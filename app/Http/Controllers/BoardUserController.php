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
use App\Traits\IdempotentRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BoardUserController extends Controller
{
    use AuthorizesRequests, IdempotentRequest;

    protected $boardInvitationService;

    public function __construct(BoardInvitationService $boardInvitationService)
    {
        $this->boardInvitationService = $boardInvitationService;
    }

    public function removeUserFromBoard(Request $request, Board $board, User $user)
    {
        // Get the idempotency key from the request
        $idempotencyKey = $request->input('idempotency_key');
    
        // Check if the idempotency key is present
        if (empty($idempotencyKey)) {
            return redirect()->route('boards.index')->withErrors(['idempotency_key' => 'Idempotency key is required.']);
        }
    
        $this->authorize('ownerOrCollaborator', $board);
    
        $response = $this->boardInvitationService->removeUserFromBoard($board, $user, $idempotencyKey);
    
        if ($response['status'] === 'error') {
            return redirect()->route('boards.show', $board->id)->withErrors(['user' => $response['message']]);
        }
    
        if ($response['status'] === 'warning') {
            return redirect()->route('boards.show', $board->id)->with('warning', $response['message']);
        }
    
        return redirect()->route('boards.show', $board->id)->with('success', $response['message']);
    }
    
    public function inviteUserToBoard(InviteUserRequest $request, Board $board)
    {
        // Get the idempotency key from the request
        $idempotencyKey = $request->input('idempotency_key');
    
        // Check if the idempotency key is present
        if (empty($idempotencyKey)) {
            return redirect()->route('boards.show', $board->id)
                             ->withErrors(['idempotency_key' => 'Idempotency key is required.']);
        }
    
        $response = $this->boardInvitationService->inviteUser($board, $request->user_id, $idempotencyKey);
    
        if (isset($response['status']) && $response['status'] === 'error') {
            return redirect()->route('boards.show', $board->id)->withErrors(['user' => $response['message']]);
        }
    
        if (isset($response['status']) && $response['status'] === 'warning') {
            return redirect()->route('boards.show', $board->id)->with('warning', $response['message']);
        }
    
        return redirect()->route('boards.show', $board->id)->with('success', $response['message']);
    }

    public function acceptInvitation(ProcessInvitationRequest $request, BoardInvitation $invitation)
    {
        // Get the idempotency key from the request
        $idempotencyKey = $request->input('idempotency_key');
    
        // Check if the idempotency key is present
        if (empty($idempotencyKey)) {
            return redirect()->route('boards.show', $invitation->board_id)
                             ->withErrors(['idempotency_key' => 'Idempotency key is required.']);
        }
    
        // Ensure the authenticated user is the invitee
        if ($invitation->user_id !== auth()->id()) {
            return redirect()->route('boards.show', $invitation->board_id)
                             ->withErrors(['user' => 'Unauthorized action.']);
        }
    
        $response = $this->boardInvitationService->acceptInvitation($invitation, $idempotencyKey);
    
        if ($response['status'] === 'error') {
            return redirect()->route('boards.show', $invitation->board_id)->withErrors(['user' => $response['message']]);
        }
    
        if ($response['status'] === 'warning') {
            return redirect()->route('boards.show', $invitation->board_id)->with('warning', $response['message']);
        }
    
        return redirect()->route('boards.show', $invitation->board_id)->with('success', $response['message']);
    }
    
    public function declineInvitation(ProcessInvitationRequest $request, BoardInvitation $invitation)
    {
        // Get the idempotency key from the request
        $idempotencyKey = $request->input('idempotency_key');
    
        // Check if the idempotency key is present
        if (empty($idempotencyKey)) {
            return redirect()->route('boards.index')
                             ->withErrors(['idempotency_key' => 'Idempotency key is required.']);
        }
    
        $response = $this->boardInvitationService->declineInvitation($invitation, $idempotencyKey);
    
        if ($response['status'] === 'error') {
            return redirect()->route('boards.index')->withErrors(['user' => $response['message']]);
        }
    
        if ($response['status'] === 'warning') {
            return redirect()->route('boards.index')->with('warning', $response['message']);
        }
    
        return redirect()->route('boards.index')->with('success', $response['message']);
    }
    
    public function manageInvitations()
    {
        $pendingInvitations = $this->boardInvitationService->getPendingInvitationsForUser(auth()->id());
    
        return view('boards.manage-invitations', compact('pendingInvitations'));
    }
    
    public function cancelInvitation(ProcessInvitationRequest $request, Board $board, $invitationId)
    {
        // Get the idempotency key from the request
        $idempotencyKey = $request->input('idempotency_key');
    
        // Check if the idempotency key is present
        if (empty($idempotencyKey)) {
            return redirect()->route('boards.show', $board->id)
                             ->withErrors(['idempotency_key' => 'Idempotency key is required.']);
        }
    
        $response = $this->boardInvitationService->cancelInvitation($board, $invitationId, $idempotencyKey);
    
        if ($response['status'] === 'error') {
            return redirect()->route('boards.show', $board->id)->withErrors(['user' => $response['message']]);
        }
    
        if ($response['status'] === 'warning') {
            return redirect()->route('boards.show', $board->id)->with('warning', $response['message']);
        }
    
        return redirect()->route('boards.show', $board->id)->with('success', $response['message']);
    }

}
