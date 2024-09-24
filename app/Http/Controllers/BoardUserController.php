<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardUser;
use App\Models\User;
use Illuminate\Http\Request;

class BoardUserController extends Controller
{

    public function removeUserFromBoard(Board $board, User $user)
    {

        $this->authorize('delete', $board);

        $board->users()->detach($user->id);
        return redirect()->route('boards.show', $board->id)->with('success', 'User removed from the board successfully.');
    }

    public function inviteUserToBoard(Request $request, $boardId)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
    
        // Check if the user is already a member of the board
        $isCollaborator = BoardUser::where('board_id', $boardId)
            ->where('user_id', $request->user_id)
            ->exists();
    
        if ($isCollaborator) {
            return redirect()->back()->withErrors(['user' => 'This user is already a collaborator on the board.']);
        }
    
        // Check if the user already has an invitation
        $existingInvite = BoardInvitation::where('board_id', $boardId)
            ->where('user_id', $request->user_id)
            ->where('status', 'pending')
            ->first();
    
        if ($existingInvite) {
            return redirect()->back()->withErrors(['user' => 'An invitation has already been sent to this user.']);
        }
    
        // Send an invitation
        BoardInvitation::create([
            'board_id' => $boardId,
            'user_id' => $request->user_id,
            'invited_by' => auth()->id(),
            'status' => 'pending',
        ]);
    
        return redirect()->route('boards.show', $boardId)->with('success', 'Invitation sent successfully.');
    }
    

        }
    
        // Add the user to the board
        BoardUser::create([
            'board_id' => $boardId,
            'user_id' => $request->user_id,
            'role' => $request->role,
        ]);
    
        return redirect()->route('boards.show', $boardId)->with('success', 'User added to the board successfully.');
    }
    public function removeUserFromBoard(Board $board, User $user)
    {
        $board->users()->detach($user->id);
        return redirect()->route('boards.show', $board->id)->with('success', 'User removed from the board successfully.');
    }


}
