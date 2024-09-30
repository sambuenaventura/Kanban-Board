<?php

namespace App\Services;

use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\BoardUser;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BoardService
{
    protected $boardModel;
    protected $taskModel;
    protected $boardUserModel;
    protected $userModel;
    protected $boardInvitationModel;

    // Inject the models through the constructor
    public function __construct(Board $boardModel, Task $taskModel, BoardUser $boardUserModel, User $userModel, BoardInvitation $boardInvitationModel)
    {
        $this->boardModel = $boardModel;
        $this->taskModel = $taskModel;
        $this->boardUserModel = $boardUserModel;
        $this->userModel = $userModel;
        $this->boardInvitationModel = $boardInvitationModel;
    }

    public function getOwnedBoards($userId)
    {
        return $this->boardModel->with(['user', 'tasks', 'boardUsers'])
                                ->withCount(['tasks', 'boardUsers'])
                                ->where('user_id', $userId)
                                ->get();
    }


}