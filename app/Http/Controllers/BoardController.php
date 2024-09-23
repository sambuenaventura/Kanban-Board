<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardRequest;
use App\Models\Board;
use App\Models\BoardUser;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BoardController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $userId = Auth::id();
        
        // Get boards where the user is either the owner or a collaborator
        $boards = Board::with(['user', 'collaborators']) // Eager load user and collaborators
                        ->withCount(['tasks', 'boardUsers'])
                        ->where(function ($query) use ($userId) {
                            $query->where('user_id', $userId) // The user is the owner $query->where('column_name', 'value'):
                                        ->orWhereHas('collaborators', function ($subQuery) use ($userId) { // ->orWhereHas('relationshipName', function ($subQuery) use ($variable) {. The ->orWhereHas('collaborators', function ($subQuery) use ($userId) line accesses the board_users table through the collaborators relationship defined in the Board model.
                                        $subQuery->where('users.id', $userId); // The user is a collaborator
                                    });
                        })
                        ->get();

        $boardsOwned = Board::with(['user'])
                            ->withCount(['tasks', 'boardUsers'])
                            ->where(function ($query) use ($userId) {
                                $query->where('user_id', $userId);
                            })
                            ->get();

        $boardsCollaborated = Board::with(['collaborators'])
                            ->withCount(['tasks', 'boardUsers'])
                            ->orWhereHas('collaborators', function ($subQuery) use ($userId) {
                                $subQuery->where('users.id', $userId); 
                            })
                            ->get();

                            
    
        return view('boards.index', compact('boards', 'userId', 'boardsOwned', 'boardsCollaborated'));
    }

    public function create()
    {
        return view('boards.create');
    }

    public function store(StoreBoardRequest $request)
    {
        // Create a new board with the validated data
        $board = Board::create([
            'name' => $request->validated()['name'],  // 'validated()' ensures only valid data is used
            'description' => $request->validated()['description'] ?? null, // Handle nullable description
            'user_id' => auth()->id(),
        ]);
    
        // Add the creator to the board_users table as the owner
        BoardUser::create([
            'board_id' => $board->id,
            'user_id' => auth()->id(),
            'role' => 'owner',
        ]);
    
        // Redirect to boards index or other relevant route
        return redirect()->route('boards.index')->with('success', 'Board created successfully.');
    }
    

    

    
    
    

    public function show($id, Request $request, Board $board)
    {
        $board = Board::with('tasks')->findOrFail($id);
        $tasks = $board->getUserTasks();
        
        $collaborators = $board->collaborators ?? collect();
        $nonCollaborators = User::whereDoesntHave('boards', function ($query) use ($board) {
            $query->where('boards.id', $board->id);
        })->where('id', '!=', auth()->id())->get() ?? collect();
        

        // Authorization check to ensure the user can view this board
        // $this->authorize('view', $board);
    
        // Retrieve the tags from the query string, or an empty array if not present
        $tags = $request->query('tags', null);
        
        // Filter tasks by the selected tags, if any
        if ($tags) {
            // If tags is an array (e.g., from checkboxes), no need to explode
            $selectedTags = array_unique(is_array($tags) ? $tags : explode(',', $tags)); // Remove duplicates
            $tasks = $tasks->filter(function($task) use ($selectedTags) {
                return in_array($task->tag, $selectedTags); // Assuming 'tag' is a field in the task model
            });
        } else {
            $selectedTags = [];
        }
    
        $toDoTasks = Board::getTaskByProgress($tasks, 'to_do');
        $inProgressTasks = Board::getTaskByProgress($tasks, 'in_progress');
        $doneTasks = Board::getTaskByProgress($tasks, 'done');
    
        $countToDo = $toDoTasks->count();
        $countInProgress = $inProgressTasks->flatten()->count();
        $countDone = $doneTasks->flatten()->count();
    
        $allTags = Board::getAllTags();
    
        return view('boards.show', compact(
            'board', 
            'toDoTasks', 
            'inProgressTasks', 
            'doneTasks', 
            'countToDo', 
            'countInProgress', 
            'countDone', 
            'selectedTags', 
            'allTags',
            'collaborators', 
            'nonCollaborators'
        ));
    }
    
    


    
    
    
    

    public function edit($id)
    {
        $board = Board::findOrFail($id);
        return view('boards.edit', compact('board'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        $board = Board::findOrFail($id);
        $this->authorize('update', $board);

        $board->update($request->only(['name']));
        return redirect()->route('boards.index')->with('success', 'Board updated successfully.');

    }
    
    

    public function destroy($id)
    {
        $board = Board::findOrFail($id);
        $this->authorize('delete', $board);
        $board->delete();
        return redirect()->route('boards.index')->with('success', 'Board deleted successfully.');
    }
}
