<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardUser;
use App\Models\User;
use Illuminate\Http\Request;

class BoardUserController extends Controller
{
    public function addUserToBoard(Request $request, $boardId)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:collaborator', // Prevent assigning owner role here
        ]);
    
        // Check if the user is already a member of the board
        $existingBoardUser = BoardUser::where('board_id', $boardId)
            ->where('user_id', $request->user_id)
            ->first();
    
        if ($existingBoardUser) {
            return redirect()->back()->withErrors(['user' => 'This user is already a member of the board.']);
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
