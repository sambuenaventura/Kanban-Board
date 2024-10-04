<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    // Private method for checking ownership
    private function isOwner(User $user, Board $board)
    {
        return $user->id === $board->user_id;
    }

    // General method to authorize all CRUD actions based on ownership
    public function owner(User $user, Board $board)
    {
        return $this->isOwner($user, $board);
    }

    // Proxy methods for CRUD actions
    public function view(User $user, Board $board)
    {
        return $this->isOwner($user, $board) || $board->collaborators()->where('user_id', $user->id)->exists();
    }
    

    public function update(User $user, Board $board)
    {
        return $this->owner($user, $board);
    }

    public function delete(User $user, Board $board)
    {
        return $this->owner($user, $board);
    }
}
