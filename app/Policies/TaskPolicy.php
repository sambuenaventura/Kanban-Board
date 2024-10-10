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

    /**
     * Check if the user is the owner or a collaborator of the board associated with the task.
     */
    public function ownerOrCollaborator(User $user, Task $task)
    {
        // Check if the user is the owner of the board that owns the task
        if ($task->board->user_id === $user->id) {
            return true;
        }
    
        // Check if the user is a collaborator on the board that owns the task
        if ($task->board->collaborators()->where('user_id', $user->id)->exists()) {
            return true;
        }
    
        // If neither, deny access
        return false;
    }

}
