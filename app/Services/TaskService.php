<?php

namespace App\Services;

use App\Events\BoardTaskCreated;
use App\Models\Board;
use App\Models\BoardUser;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    protected $taskModel;
    protected $boardModel;
    protected $boardUserModel;

    public function __construct(Task $taskModel, Board $boardModel, BoardUser $boardUserModel)
    {
        $this->taskModel = $taskModel;
        $this->boardModel = $boardModel;
        $this->boardUserModel = $boardUserModel;
    }

    public function getAllTags($board)
    {
        return $this->taskModel->where('board_id', $board->id)
                               ->distinct()             
                               ->pluck('tag')            
                               ->filter();            
    }
    
    public function getUserTasks($board)
    {
        if ($board->user_id === Auth::id()) {
            return $board->tasks;
        }

        if ($board->collaborators->contains(Auth::id())) {
            return $board->tasks;
        }

        return collect();
    }

    public function getTaskByProgress($tasks, $progress)
    {
        return $tasks->where('progress', $progress)
                     ->groupBy(function($task) {
                         return Carbon::parse($task->due)->format('Y-m-d');
                     })->sortKeys();
    }

    public function getTaskCounts($tasks)
    {
        return [
            'to_do' => $tasks->where('progress', 'to_do')->count(),
            'in_progress' => $tasks->where('progress', 'in_progress')->count(),
            'done' => $tasks->where('progress', 'done')->count(),
        ];
    }
    
    public function createTask(Board $board, array $data, string $idempotencyKey)
    {
        // Idempotency check (to prevent duplicate actions)
        if ($this->isIdempotencyKeyUsed($idempotencyKey)) {
            return ['warning' => 'Task has already been created.'];
        }
    
        // Check if a task with the same name already exists
        $existingTask = $this->taskModel->where('name', $data['name'])
                                         ->where('board_id', $board->id)
                                         ->first();
        if ($existingTask) {
            return ['warning' => 'A task with this name already exists on this board.'];
        }
    
        // Retrieve the board_user_id associated with the authenticated user and the board
        $boardUser = $this->boardUserModel->where('board_id', $board->id)
                                           ->where('user_id', auth()->id())
                                           ->first();
    
        if (!$boardUser) {
            return ['error' => 'You are not authorized to add tasks to this board.'];
        }
    
        // Create the task using mass assignment
        $task = $this->taskModel->create([
            'name' => $data['name'],
            'description' => $data['description'],
            'due' => $data['due'],
            'priority' => $data['priority'],
            'progress' => $data['progress'],
            'tag' => $data['tag'],
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
        ]);
    
        broadcast(new BoardTaskCreated($task));
    
        // Store the idempotency key in the cache to prevent duplicate processing
        $this->cacheIdempotencyKey($idempotencyKey);
    
        return ['success' => 'Task created successfully.', 'task' => $task];
    }
    

    public function isIdempotencyKeyUsed($idempotencyKey)
    {
        return Cache::has('idempotency_' . $idempotencyKey);
    }

    public function cacheIdempotencyKey($idempotencyKey)
    {
        Cache::put('idempotency_' . $idempotencyKey, true, 86400);
    }
}
