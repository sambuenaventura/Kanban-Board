<?php

namespace App\Http\Controllers;

use App\Events\BoardInvitationCount;
use App\Events\BoardInvitationDetailsSent;
use App\Events\BoardInvitationDetailsCanceled;
use App\Events\BoardRemoveCollaborator;
use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\BoardUser;
use App\Models\User;
use App\Services\BoardInvitationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BoardUserController extends Controller
{
    use AuthorizesRequests;

    protected $boardInvitationService;

    public function __construct(BoardInvitationService $boardInvitationService)
    {
        $this->boardInvitationService = $boardInvitationService;
    }

    // public function addUserToBoard(Request $request, $boardId)
    // {

    //     $board = Board::findOrFail($boardId);

    //     // Authorize the action
    //     $this->authorize('update', $board);

    //     // Validate the request data
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'role' => 'required|string|in:collaborator', // Prevent assigning owner role here
    //     ]);
    
    //     // Check if the user is already a member of the board
    //     $existingBoardUser = BoardUser::where('board_id', $boardId)
    //         ->where('user_id', $request->user_id)
    //         ->first();

    
    //     if ($existingBoardUser) {
    //         return redirect()->back()->withErrors(['user' => 'This user is already a member of the board.']);
    //     }
    
    //     // Add the user to the board
    //     BoardUser::create([
    //         'board_id' => $boardId,
    //         'user_id' => $request->user_id,
    //         'role' => $request->role,
    //     ]);
    
    //     return redirect()->route('boards.show', $boardId)->with('success', 'User added to the board successfully.');
    // }

    public function removeUserFromBoard(Request $request, Board $board, User $user)
    {
        $this->authorize('owner', $board);

        $response = $this->boardInvitationService->removeUserFromBoard($board, $user, $request->idempotency_key);
        
        if (isset($response['error'])) {
            return redirect()->route('boards.show', $board->id)->withErrors(['user' => $response['error']]);
        }
    
        if (isset($response['warning'])) {
            return redirect()->route('boards.show', $board->id)->with('warning', $response['warning']);
        }
    
        return redirect()->route('boards.show', $board->id)->with('success', $response['success']);
    }
    
        if ($existingInvite) {
            // return redirect()->back()->withErrors(['user' => 'An invitation has already been sent to this user.']);
            return redirect()->back()->with('warning', 'An invitation has already been sent to this user.');
        }
    
        // Send an invitation
        $invitation = BoardInvitation::create([
            'board_id' => $boardId,
            'user_id' => $request->user_id,
            'invited_by' => auth()->id(),
            'status' => 'pending',
        ]);

        // Fetch the updated invitation count for the invitee
        $invitationCount = User::find($invitation->user_id)->invitationCount();
        
        // Broadcast the updated invitation count
        broadcast(new BoardInvitationCount($invitation->user_id, $invitationCount));

        // Prepare the invitation details for broadcasting
        $invitationDetails = [
            'id' => $invitation->id,
            'board' => [
                'name' => $invitation->board->name,
            ],
            'inviter' => [
                'name' => auth()->user()->name,
            ],
            'created_at' => $invitation->created_at
        ];

        broadcast(new BoardInvitationDetailsSent($request->user_id, $invitationDetails));

        Cache::put('idempotency_' . $request->idempotency_key, true, 86400);
    
        return redirect()->route('boards.show', $boardId)->with('success', 'Invitation sent successfully.');
    }
    

    public function acceptInvitation(BoardInvitation $invitation)
    {
        // Ensure the authenticated user is the invitee
        if ($invitation->user_id !== auth()->id()) {
            return redirect()->route('boards.show', $invitation->board_id)->withErrors('Unauthorized action.');
        }
    
        // Add the user to the board
        BoardUser::create([
            'board_id' => $invitation->board_id,
            'user_id' => $invitation->user_id,
            'role' => 'collaborator',
        ]);
    
        // Update invitation status
        $invitation->update(['status' => 'accepted']);

        // Fetch the updated invitation count for the invitee
        $invitationCount = User::find($invitation->user_id)->invitationCount();

        // Broadcast the updated invitation count
        broadcast(new BoardInvitationCount($invitation->user_id, $invitationCount));
        
    
        return redirect()->route('boards.show', $invitation->board_id)->with('success', 'You have joined the board.');
    }
    
    public function declineInvitation(BoardInvitation $invitation)
    {
        // Ensure the authenticated user is the invitee
        if ($invitation->user_id !== auth()->id()) {
            return redirect()->route('boards.show', $invitation->board_id)->withErrors('Unauthorized action.');
        }
    
        // Update invitation status to declined
        $invitation->update(['status' => 'declined']);

        // Fetch the updated invitation count for the invitee
        $invitationCount = User::find($invitation->user_id)->invitationCount();

        // Broadcast the updated invitation count
        broadcast(new BoardInvitationCount($invitation->user_id, $invitationCount));
                
        return redirect()->route('dashboard')->with('success', 'You declined the invitation.');
    }
    
    public function manageInvitations()
    {
        $pendingInvitations = BoardInvitation::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->with(['board', 'inviter'])
            ->get();
    
        return view('boards.manage-invitations', compact('pendingInvitations'));
    }
    
    public function cancelInvitation(Board $board, BoardInvitation $invitation)
    {
        // Authorize the action, checking if the authenticated user is the inviter
        // $this->authorize('cancel', $invitation);

        // Check if the invitation belongs to the correct board
        if ($invitation->board_id !== $board->id) {
            return redirect()->route('boards.show', $board->id)->withErrors('Invitation not found for this board.');
        }

        $userId = $invitation->user_id;

        // Check if the user has already joined the board
        $isMember = $board->users()->where('users.id', $userId)->exists();

        if ($isMember) {
            // User has already joined the board, so do not cancel the invitation
            return redirect()->route('boards.show', $board->id)->with('warning', 'User has already joined the board. Invitation cannot be canceled.');
        }

        // Check if the invitation has been declined
        if ($invitation->status === 'declined') {
            return redirect()->route('boards.show', $board->id)->with('warning', 'User has already declined the invitation. Invitation cannot be canceled.');
        }

        // Delete the invitation
        $invitation->delete();

        // Fetch the updated invitation count for the invitee
        $invitationCount = User::find($invitation->user_id)->invitationCount();

        // Broadcast the updated invitation count
        broadcast(new BoardInvitationCount($invitation->user_id, $invitationCount));

        // Broadcast the canceled invitation details
        broadcast(new BoardInvitationDetailsCanceled($userId, $invitation->id));
       
        // Redirect back with a success message
        return redirect()->route('boards.show', $board->id)->with('success', 'Invitation canceled successfully.');
    }


}
