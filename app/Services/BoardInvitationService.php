<?php

namespace App\Services;
use App\Events\BoardRemoveCollaborator;
use App\Models\BoardInvitation;
use App\Models\BoardUser;

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

    
}