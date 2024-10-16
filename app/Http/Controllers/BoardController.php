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
use App\Services\SubscriptionService;
use App\Services\TaskService;
use App\Traits\IdempotentRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    use AuthorizesRequests, IdempotentRequest;

    protected $boardService;
    protected $taskService;
    protected $subscriptionService;

    public function __construct(BoardService $boardService, TaskService $taskService, SubscriptionService $subscriptionService)
    {
        $this->boardService = $boardService;
        $this->taskService = $taskService;
        $this->subscriptionService = $subscriptionService;
    }

    public function index()
    {
        $userId = Auth::id();
        $boardsOwned = $this->boardService->getOwnedBoards($userId);
        $boardsCollaborated = $this->boardService->getCollaboratedBoards($userId);
       
        $this->boardService->addTaskCountsToBoards($boardsOwned);
        $this->boardService->addTaskCountsToBoards($boardsCollaborated);

        $this->sortCollaboratorsForBoards($boardsOwned, $userId);
        $this->sortCollaboratorsForBoards($boardsCollaborated, $userId);
    
        return view('boards.index', compact('boardsOwned', 'boardsCollaborated', 'userId'));
    }

    private function sortCollaboratorsForBoards($boards, $userId)
    {
        foreach ($boards as $board) {
            $board->sortedCollaborators = $this->boardService->sortCollaborators($board->boardUsers, $userId);
        }
    }
    
    public function create()
    {
        return view('boards.create');
    }

    public function store(StoreBoardRequest $request)
    {
        $user = auth()->user();
        
        $maxBoards = $this->subscriptionService->getMaxBoards($user);
    
        $currentBoardCount = $user->boards()->count();
    
        if ($currentBoardCount >= $maxBoards) {
            return redirect()->route('boards.index')
                            ->withErrors(['error' => 'You have reached the maximum number of boards allowed for your subscription plan.']);
        }
    
        $board = $this->boardService->createBoard($request->validated());
    
        broadcast(new BoardCreated($board));

        return redirect()->route('boards.index')->with('success', 'Board created successfully.');
    }

    public function show($id, Request $request)
    {
        // Fetch the board and its tasks and collaborators using the board service
        $board = $this->boardService->getBoardWithTasksAndCollaborators($id);

        $this->authorize('view', $board);
    
        $tasks = $this->taskService->getUserTasks($board);
        
        // Get collaborators
        $collaborators = $this->boardService->getCollaborators($board);

        // Fetch pending invitations
        $pendingInvitations = $this->boardService->getPendingInvitations($board);
        
        // Fetch users who are not collaborators or the authenticated user
        $nonCollaborators = $this->boardService->getNonCollaboratorsExcludingInvited($board, $pendingInvitations);    

        // Filter by tags
        $selectedTags = $this->getSelectedTags($request);
        if (!empty($selectedTags)) {
            $tasks = $this->filterTasksByTags($tasks, $selectedTags);
        }

        // Filter by priority (if selected)
        $selectedPriority = $request->input('priority');

        if (!empty($selectedPriority)) {
            $tasks = $this->filterTasksByPriority($tasks, $selectedPriority);
        }

        // Filter by due date
        $selectedDue = $request->input('due');
        if (!empty($selectedDue)) {
            $tasks = $this->filterTasksByDue($tasks, $selectedDue);
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
            'pendingInvitations',
            'selectedPriority'
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

    // Method to filter tasks by priority
    protected function filterTasksByPriority($tasks, $selectedPriority)
    {
        return  $tasks->whereIn('priority', $selectedPriority);
    }
    
    // Method to filter tasks by due
    protected function filterTasksByDue($tasks, $selectedDue)
    {
        $today = Carbon::today();

        return $tasks->filter(function ($task) use ($selectedDue, $today) {
            switch ($selectedDue) {
                case 'overdue':
                    return $task->due < $today;
                case 'today':
                    return $task->due->isToday();
                case 'soon':
                    return $task->due->isBetween($today->addDay(), $today->copy()->addDays(3));
                default:
                    return true;
            }
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

        $board->update($request->only(['name', 'description']));

        return redirect()->route('boards.index')->with('success', 'Board updated successfully.');
    }
    
    public function destroy(Request $request, $id)
    {
        // Get the idempotency key from the request
        $idempotencyKey = $request->input('idempotency_key');
    
        // Check if the idempotency key is present
        if (empty($idempotencyKey)) {
            return redirect()->route('boards.index')->withErrors(['idempotency_key' => 'Idempotency key is required.']);
        }
    
        $board = $this->boardService->getBoardById($id);
    
        if ($board) {
            $this->authorize('owner', $board);
        }
    
        $result = $this->boardService->deleteBoard($id, $idempotencyKey);
    
        if ($result['status'] === 'warning') {
            return redirect()->route('boards.index')->with('warning', $result['message']);
        }
    
        return redirect()->route('boards.index')->with('success', $result['message']);
    }
    
}
