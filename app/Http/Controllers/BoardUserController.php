<?php

namespace App\Http\Controllers;

use App\Events\BoardInvitationCount;
use App\Events\BoardInvitationDetailsSent;
use App\Events\BoardInvitationDetailsCanceled;
use App\Events\BoardRemoveCollaborator;
use App\Http\Requests\InviteUserRequest;
use App\Http\Requests\ProcessInvitationRequest;
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
    
    public function inviteUserToBoard(InviteUserRequest $request, $boardId)
    {
        $response = $this->boardInvitationService->inviteUser($boardId, $request->user_id, $request->idempotency_key);
    
        if (isset($response['error'])) {
            return redirect()->back()->withErrors(['user' => $response['error']]);
        }
    
        if (isset($response['warning'])) {
            return redirect()->route('boards.show', $boardId)->with('warning', $response['warning']);
        }
    
        return redirect()->route('boards.show', $boardId)->with('success', $response['success']);
    }
    

    public function acceptInvitation(ProcessInvitationRequest $request, BoardInvitation $invitation)
    {
        $response = $this->boardInvitationService->acceptInvitation($invitation, $request->idempotency_key);
    
        if (isset($response['error'])) {
            return redirect()->route('boards.show', $invitation->board_id)->withErrors(['user' => $response['error']]);
        }
    
        if (isset($response['warning'])) {
            return redirect()->route('boards.show', $invitation->board_id)->with('warning', $response['warning']);
        }
    
        return redirect()->route('boards.show', $invitation->board_id)->with('success', $response['success']);
    }
    
    
    
    
    public function declineInvitation(ProcessInvitationRequest $request, BoardInvitation $invitation)
    {
        $response = $this->boardInvitationService->declineInvitation($invitation, $request->idempotency_key);
    
        if (isset($response['error'])) {
            return redirect()->route('dashboard')->withErrors(['user' => $response['error']]);
        }

        if (isset($response['warning'])) {
            return redirect()->route('dashboard')->with('warning', $response['warning']);
        }

        return redirect()->route('boards.index')->with('success', $response['success']);
    }
    
    
    public function manageInvitations()
    {
        $pendingInvitations = $this->boardInvitationService->getPendingInvitationsForUser(auth()->id());
    
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
