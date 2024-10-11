<?php

namespace App\Services;

use App\Events\BoardInvitationCount;
use App\Events\BoardInvitationDetailsCanceled;
use App\Events\BoardInvitationDetailsSent;
use App\Events\BoardRemoveCollaborator;
use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\BoardUser;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BoardInvitationService
{
    protected $boardInvitationModel;
    protected $boardUserModel;
    protected $userModel;
    protected $idempotencyService;

    // Inject the models through the constructor
    public function __construct(BoardInvitation $boardInvitationModel, BoardUser $boardUserModel, User $userModel, IdempotencyService $idempotencyService)
    {
        $this->boardInvitationModel = $boardInvitationModel;
        $this->boardUserModel = $boardUserModel;
        $this->userModel = $userModel;
        $this->idempotencyService = $idempotencyService;
    }

    public function getInvitationById($id)
    {
        return $this->boardInvitationModel->find($id);
    }

    public function removeUserFromBoard(Board $board, User $user, string $idempotencyKey)
    {
        return $this->idempotencyService->process("remove_user_{$user->id}_from_board_{$board->id}", $idempotencyKey, function () use ($board, $user) {
            // Check if the user is a collaborator before attempting to detach
            if (!$board->users()->where('user_id', $user->id)->exists()) {
                return [
                    'status' => 'error',
                    'message' => 'User is not a collaborator on this board.',
                ];
            }
    
            // Detach the user from the board
            $board->users()->detach($user->id);

            // Broadcast the removed collaborator
            broadcast(new BoardRemoveCollaborator($user->id, $board->id));  
    
            return [
                'status' => 'success',
                'message' => 'User removed from the board successfully.',
            ];
        });
    }
    
    public function inviteUser(Board $board, $userId, string $idempotencyKey)
    {
        return $this->idempotencyService->process("invite_user_{$userId}", $idempotencyKey, function () use ($board, $userId) {
            // Check if the user is already a collaborator on the board
            if ($board->users()->where('users.id', $userId)->exists()) {
                return [
                    'status' => 'error',
                    'message' => 'This user is already a collaborator on the board.',
                ];
            }
    
            // Check if the user already has a pending invitation
            $existingInvite = BoardInvitation::where('board_id', $board->id)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->first();
    
            if ($existingInvite) {
                return [
                    'status' => 'warning',
                    'message' => 'An invitation has already been sent to this user.',
                ];
            }
    
            // Send the invitation
            $invitation = BoardInvitation::create([
                'board_id' => $board->id,
                'user_id' => $userId,
                'invited_by' => auth()->id(),
                'status' => 'pending',
            ]);
    
            // Fetch the updated invitation count for the invitee
            $invitationCount = $this->userModel->find($invitation->user_id)->invitationCount();
    
            // Broadcast the updated invitation count
            broadcast(new BoardInvitationCount($invitation->user_id, $invitationCount));
    
            // Prepare and broadcast the invitation details
            $invitationDetails = [
                'id' => $invitation->id,
                'board' => ['name' => $invitation->board->name],
                'inviter' => ['name' => auth()->user()->name],
                'created_at' => $invitation->created_at,
            ];
    
            broadcast(new BoardInvitationDetailsSent($userId, $invitationDetails));
    
            return [
                'status' => 'success',
                'message' => 'Invitation sent successfully.',
            ];
        });
    }

    public function acceptInvitation(BoardInvitation $invitation, $idempotencyKey)
    {
        // Idempotency check (to prevent duplicate actions)
        if ($this->isIdempotencyKeyUsed($idempotencyKey)) {
            return ['warning' => 'This action has already been processed.'];
        }

        // Check if the invitation is already accepted
        if ($invitation->status === 'accepted') {
            return [
                'error' => 'This invitation has already been accepted.'
            ];
        }
    
        // Ensure the authenticated user is the invitee
        if ($invitation->user_id !== auth()->id()) {
            return ['error' => 'Unauthorized action.'];
        }
    
        // Check if the user is already a collaborator
        $isCollaborator = BoardUser::where('board_id', $invitation->board_id)
                                    ->where('user_id', $invitation->user_id)
                                    ->exists();
    
        if ($isCollaborator) {
            return ['warning' => 'You are already a collaborator on this board.'];
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

        // Store the idempotency key in the cache
        $this->cacheIdempotencyKey($idempotencyKey);
        
        return ['success' => 'You have joined the board.'];
    }

    public function declineInvitation(BoardInvitation $invitation, $idempotencyKey)
    {
        // Idempotency check (to prevent duplicate actions)
        if ($this->isIdempotencyKeyUsed($idempotencyKey)) {
            return ['warning' => 'This action has already been processed.'];
        }
        
        // Check if the invitation is already declined
        if ($invitation->status === 'declined') {
            return [
                'error' => 'This invitation has already been declined.'
            ];
        }

        // Ensure the authenticated user is the invitee
        if ($invitation->user_id !== auth()->id()) {
            return ['error' => 'Unauthorized action.'];
        }

        // Update invitation status to declined
        $invitation->update(['status' => 'declined']);

        // Fetch the updated invitation count for the invitee
        $invitationCount = User::find($invitation->user_id)->invitationCount();

        // Broadcast the updated invitation count
        broadcast(new BoardInvitationCount($invitation->user_id, $invitationCount));
        
        return ['success' => 'You declined the invitation.'];
    }

    public function getPendingInvitationsForUser($userId)
    {
        return $this->boardInvitationModel->with(['board', 'inviter'])
                                          ->where('user_id', $userId)
                                          ->where('status', 'pending')
                                          ->get();
    }

    public function cancelInvitation(Board $board, BoardInvitation $invitation, $idempotencyKey)
    {
        // Idempotency check (to prevent duplicate actions)
        if ($this->isIdempotencyKeyUsed($idempotencyKey)) {
            return ['warning' => 'This action has already been processed.'];
        }

        // Check if the invitation belongs to the correct board
        if ($invitation->board_id !== $board->id) {
            return ['error' => 'Invitation not found for this board.'];
        }

        $userId = $invitation->user_id;

        // Check if the user has already joined the board
        if ($board->users()->where('users.id', $userId)->exists()) {
            return ['warning' => 'User has already joined the board. Invitation cannot be canceled.'];
        }

        // Check if the invitation has been declined
        if ($invitation->status === 'declined') {
            return ['warning' => 'User has already declined the invitation. Invitation cannot be canceled.'];
        }

        // Delete the invitation
        $invitation->delete();

        // Fetch the updated invitation count for the invitee
        $invitationCount = User::find($invitation->user_id)->invitationCount();

        broadcast(new BoardInvitationCount($invitation->user_id, $invitationCount));

        broadcast(new BoardInvitationDetailsCanceled($userId, $invitation->id));
        
        // Store the idempotency key in the cache
        $this->cacheIdempotencyKey($idempotencyKey);

        return ['success' => 'Invitation canceled successfully.'];
    }
    
}