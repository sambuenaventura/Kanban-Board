<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function isOwnerOrCollaborator(User $user, Task $task)
    {
        // Check if the user is the owner of the board
        if ($task->board->user_id === $user->id) {
            return true;
        }
    
        // Check if the user is a collaborator on the board
        if ($task->board->collaborators->contains($user->id)) {
            return true;
        }
    
        // If neither, deny access
        return false;
    }

}
