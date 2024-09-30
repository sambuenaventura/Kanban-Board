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
        $boardsOwned = $this->boardService->getOwnedBoards($userId);
        $boardsCollaborated = $this->boardService->getCollaboratedBoards($userId);

        // Add task counts to each board
        $this->boardService->addTaskCountsToBoards($boardsOwned);
        $this->boardService->addTaskCountsToBoards($boardsCollaborated);

        return view('boards.index', compact('boardsOwned', 'boardsCollaborated', 'userId'));
    }
    

    public function create()
    {
        return view('boards.create');
    }


    public function store(StoreBoardRequest $request)
    {
        // Create a new board with the validated data
        $board = $this->boardService->createBoard($request->validated());
    
        // Dispatch boardcreated event
        broadcast(new BoardCreated($board));
            
        // Redirect to boards index or other relevant route
        return redirect()->route('boards.index')->with('success', 'Board created successfully.');
    }


    public function show($id, Request $request, Board $board)
    {
        $board = Board::with('tasks', 'collaborators')->findOrFail($id);
    
        $this->authorize('view', $board);
    
        $tasks = $this->taskService->getUserTasks($board);
        
        // Get collaborators
        $collaborators = $this->boardService->getCollaborators($board);
    
        // Fetch users who are not collaborators or the authenticated user
        $nonCollaborators = $this->boardService->getNonCollaboratorsExcludingInvited($board);
    
        // Fetch pending invitations
        $pendingInvitations = $this->boardService->getPendingInvitations($board);
    
        // Filter by tags
        $selectedTags = $this->getSelectedTags($request);

        if (!empty($selectedTags)) {
            $tasks = $this->filterTasksByTags($tasks, $selectedTags);
        } else {
            $tasks = $tasks;
        }

        $toDoTasks = $this->taskService->getTaskByProgress($tasks, 'to_do');
        $inProgressTasks = $this->taskService->getTaskByProgress($tasks, 'in_progress');
        $doneTasks = $this->taskService->getTaskByProgress($tasks, 'done');
    
        $taskCounts = $this->taskService->getTaskCounts($tasks);

        $allTags = $this->taskService->getAllTags($board);

        return view('boards.show', compact(
            'board', 
            'toDoTasks', 
            'inProgressTasks', 
            'doneTasks', 
            'taskCounts',
            'selectedTags', 
            'allTags',
            'collaborators', 
            'nonCollaborators',
            'pendingInvitations'
        ));
    }

    // Method to handle tag selection
    protected function getSelectedTags(Request $request)
    {
        $tags = $request->query('tags', null);
        return $tags ? array_unique(is_array($tags) ? $tags : explode(',', $tags)) : [];
    }

    // Method to filter tasks by tags
    protected function filterTasksByTags($tasks, $selectedTags)
    {
        return $tasks->filter(function ($task) use ($selectedTags) {
            return in_array($task->tag, $selectedTags);
        });
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
