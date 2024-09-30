<?php

namespace App\Http\Controllers;

use App\Events\BoardCreated;
use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\BoardUser;
use App\Models\Task;
use App\Models\User;
use App\Services\BoardService;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BoardController extends Controller
{
    use AuthorizesRequests;

    protected $boardService;
    protected $taskService;

    public function __construct(BoardService $boardService, TaskService $taskService)
    {
        $this->boardService = $boardService;
        $this->taskService = $taskService;
    }

    public function index()
    {
        $userId = Auth::id();
       
        $boardsOwned = Board::with(['user', 'tasks'])
            ->withCount(['tasks', 'boardUsers'])
            ->where('user_id', $userId)
            ->get();

        $boardsCollaborated = Board::with(['user', 'tasks', 'collaborators'])
            ->withCount(['tasks', 'boardUsers'])
            ->whereHas('collaborators', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get();

        // Add task counts to each board
        $boardsOwned->each(function ($board) {
            $board->taskCounts = Task::getTaskCounts($board->id);
        });

        $boardsCollaborated->each(function ($board) {
            $board->taskCounts = Task::getTaskCounts($board->id);
        });

        return view('boards.index', compact('boardsOwned', 'boardsCollaborated', 'userId'));
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

        // Dispatch boardcreated event
        broadcast(new BoardCreated($board));
            
        // Redirect to boards index or other relevant route
        return redirect()->route('boards.index')->with('success', 'Board created successfully.');
    }
    

    public function show($id, Request $request, Board $board)
    {
        $board = Board::with('tasks', 'collaborators')->findOrFail($id);
    
        $this->authorize('view', $board);
    
        $tasks = $board->getUserTasks();
        
        // Get collaborators
        $collaborators = $board->collaborators ?? collect();
    
        // Fetch users who are not collaborators or the authenticated user
        $nonCollaborators = User::whereDoesntHave('boards', function ($query) use ($board) {
            $query->where('boards.id', $board->id);
        })
            ->where('id', '!=', auth()->id())
            ->whereNotIn('id', $collaborators->pluck('id')) // Exclude collaborators
            ->get();
    
        // Fetch pending invitations
        $pendingInvitations = BoardInvitation::where('board_id', $board->id)
            ->where('status', 'pending')
            ->with('invitedUser')
            ->get();
    
        // Exclude users with pending invitations from nonCollaborators
        $invitedUserIds = $pendingInvitations->pluck('user_id');
        $nonCollaborators = $nonCollaborators->whereNotIn('id', $invitedUserIds);
    
        // Filter by tags
        $tags = $request->query('tags', null);
    
        if ($tags) {
            $selectedTags = array_unique(is_array($tags) ? $tags : explode(',', $tags));
            $tasks = $tasks->filter(function($task) use ($selectedTags) {
                return in_array($task->tag, $selectedTags);
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
    
        $allTags = $board->getAllTags();

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
            'nonCollaborators',
            'pendingInvitations'
        ));
    }

    public function edit($id)
    {
        $board = Board::findOrFail($id);
        return view('boards.edit', compact('board'));
    }


    public function update(UpdateBoardRequest $request, $id)
    {
        $board = Board::findOrFail($id);

        $this->authorize('update', $board);

        $board->update($request->only(['name']));

        return redirect()->route('boards.index')->with('success', 'Board updated successfully.');
    }
    
    
    public function destroy(Request $request, $id)
    {
        $board = Board::find($id); // Use find instead of findOrFail

        // Consolidated check for board existence or idempotency key
        if (!$board || Cache::has('idempotency_' . $request->idempotency_key)) {
            return redirect()->route('boards.index')->with('warning', 'The board has already been deleted.');
        }
        
        $this->authorize('delete', $board);
    
        $board->delete();
        Cache::put('idempotency_' . $request->idempotency_key, true, 86400);
        
        return redirect()->route('boards.index')->with('success', 'Board deleted successfully.');
    }
    
    
}
