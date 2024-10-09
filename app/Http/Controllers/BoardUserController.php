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
            return redirect()->route('boards.index')->withErrors(['user' => $response['error']]);
        }

        if (isset($response['warning'])) {
            return redirect()->route('boards.index')->with('warning', $response['warning']);
        }

        return redirect()->route('boards.index')->with('success', $response['success']);
    }
    
    public function cancelInvitation(ProcessInvitationRequest $request, Board $board, $invitation)
    {
        // If $invitation is already a model instance, use it directly
        if (!($invitation instanceof BoardInvitation)) {
            $invitation = BoardInvitation::find($invitation);
        }
    
        // Check if the invitation was found
        if (!$invitation) {
            // Treat as a successful idempotent operation
            return redirect()->route('boards.show', $board->id)
                ->with('warning', 'The invitation has already been canceled.');
        }
    
        // Check if the invitation belongs to the correct board
        if ($invitation->board_id !== $board->id) {
            return redirect()->route('boards.show', $board->id)->withErrors(['invitation' => 'Invitation not found for this board.']);
        }
    
        $response = $this->boardInvitationService->cancelInvitation($board, $invitation, $request->idempotency_key);
        
        if (isset($response['error'])) {
            return redirect()->route('boards.show', $board->id)->withErrors(['user' => $response['error']]);
        }
    
        if (isset($response['warning'])) {
            return redirect()->route('boards.show', $board->id)->with('warning', $response['warning']);
        }
    
        return redirect()->route('boards.show', $board->id)->with('success', $response['success']);
    }
    
}
