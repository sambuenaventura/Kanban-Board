<?php

namespace App\Services;

use App\Events\BoardInvitationCount;
use App\Events\BoardInvitationDetailsSent;
use App\Events\BoardRemoveCollaborator;
use App\Models\BoardInvitation;
use App\Models\BoardUser;
use Illuminate\Support\Facades\Cache;

class BoardInvitationService
{
    protected $boardInvitationModel;
    protected $boardUserModel;

    // Inject the models through the constructor
    public function __construct(BoardInvitation $boardInvitationModel, BoardUser $boardUserModel)
    {
        $this->boardInvitationModel = $boardInvitationModel;
        $this->boardUserModel = $boardUserModel;
    }

    public function removeUserFromBoard($board, $user, $idempotencyKey)
    {
        // Idempotency check (to prevent duplicate actions)
        if ($this->isIdempotencyKeyUsed($idempotencyKey)) {
            return ['warning' => 'This action has already been processed.'];
        }

        // Detach the user from the board
        $board->users()->detach($user->id);

        broadcast(new BoardRemoveCollaborator($user->id, $board->id));  
        
        // Store the idempotency key in the cache
        $this->cacheIdempotencyKey($idempotencyKey);

        return ['success' => 'User removed from the board successfully.'];
    }

    public function inviteUser($boardId, $userId, $idempotencyKey)
    {
        // Check if the idempotency key is already used
        if ($this->isIdempotencyKeyUsed($idempotencyKey)) {
            return ['warning' => 'An invitation has already been sent to this user.'];
        }

        // Check if the user is already a collaborator on the board
        $isCollaborator = BoardUser::where('board_id', $boardId)
            ->where('user_id', $userId)
            ->exists();

        if ($isCollaborator) {
            return ['error' => 'This user is already a collaborator on the board.'];
        }

        // Check if the user already has a pending invitation
        $existingInvite = BoardInvitation::where('board_id', $boardId)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            return ['warning' => 'An invitation has already been sent to this user.'];
        }

        // Send the invitation
        $invitation = BoardInvitation::create([
            'board_id' => $boardId,
            'user_id' => $userId,
            'invited_by' => auth()->id(),
            'status' => 'pending',
        ]);

        // Fetch the updated invitation count for the invitee
        $invitationCount = User::find($invitation->user_id)->invitationCount();

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

        // Store the idempotency key in the cache
        $this->cacheIdempotencyKey($idempotencyKey);

        // Return success response
        return ['success' => 'Invitation sent successfully.'];
    }

    public function isIdempotencyKeyUsed($idempotencyKey)
    {
        return Cache::has('idempotency_' . $idempotencyKey);
    }

    public function cacheIdempotencyKey($idempotencyKey)
    {
        Cache::put('idempotency_' . $idempotencyKey, true, 86400);
    }

    
}