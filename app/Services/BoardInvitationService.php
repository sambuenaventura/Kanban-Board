<?php

namespace App\Services;
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

    
}