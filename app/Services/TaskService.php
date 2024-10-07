<?php

namespace App\Services;

use App\Events\BoardTaskCreated;
use App\Events\BoardTaskDeleted;
use App\Events\BoardTaskUpdated;
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
        return $board->tasks;
    }
    
    public function getTaskByProgress($tasks, $progress)
    {
        return $tasks->where('progress', $progress)
                     ->map(function($task) {
                         // Add a custom flag for overdue, due today, or due soon
                         if ($task->due < Carbon::today()) {
                             $task->is_overdue = true;
                         } elseif ($task->due->isToday()) {
                             $task->is_due_today = true;
                         } elseif ($task->due->isBetween(Carbon::tomorrow(), Carbon::today()->addDays(3))) {
                             $task->is_due_soon = true;
                         }
                         return $task;
                     })
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
    

    public function getTaskDetails($boardId, $taskId)
    {
        // Fetch the task with media (attachments)
        $task = $this->taskModel->with('media')->findOrFail($taskId);

        $this->authorizeUserForTask($task, auth()->user());

        // Fetch the board
        $board = $this->boardModel->findOrFail($boardId);
    
        return [
            'task' => $task,
            'board' => $board,
            'attachments' => $task->media,
            'boardId' => $boardId,
        ];
    }

    public function getEditableTask($taskId)
    {
        // Fetch the task
        $task = $this->taskModel->findOrFail($taskId);
    
        $this->authorizeUserForTask($task, auth()->user());
    
        return $task;
    }

    public function updateTask($taskId, array $data)
    {
        // Find the task by ID
        $task = Task::find($taskId);
    
        if (!$task) {
            return [
                'error' => 'Task not found.',
            ];
        }
    
        $this->authorizeUserForTask($task, auth()->user());
    
        // Check if the progress is being updated
        $progressChanged = $task->progress !== ($data['progress'] ?? null);
        
        // Update the task
        $task->update($data);
    
        // Determine the message based on the progress change
        $message = 'Task updated successfully.';
        if ($progressChanged) {
            switch ($data['progress']) {
                case 'to_do':
                    $message = 'Task reopened successfully.';
                    break;
                case 'in_progress':
                    $message = 'Task started successfully.';
                    break;
                case 'done':
                    $message = 'Task completed successfully.';
                    break;
            }
        }
    
        return [
            'success' => $message,
            'task' => $task,
        ];
    }
    

    public function addAttachmentToTask(Task $task, $file)
    {
        $this->authorizeUserForTask($task, auth()->user());
    
        // Add media to task
        $task->addMedia($file)->toMediaCollection('attachments');
    
        return [
            'success' => 'Successfully added an attachment to ' . $task->name . '.',
        ];
    }

    public function deleteTask($taskId)
    {
        $task = $this->taskModel->findOrFail($taskId);

        $this->authorizeUserForTask($task, auth()->user());

        $task->delete();

        return [
            'success' => 'Task deleted successfully.',
            'taskId' => $taskId,
        ];
    }
    
    public function deleteTaskAjax($id)
    {
        // Find the task by ID
        $task = $this->taskModel->findOrFail($id);
    
        $this->authorizeUserForTask($task, auth()->user());
    
        // Get the board ID before deleting the task
        $boardId = $task->board_id;
    
        // Delete the task
        $task->delete();
    
        // Dispatch the TaskDeleted event
        broadcast(new BoardTaskDeleted($task->id, $boardId));
    
        return [
            'success' => 'Task deleted successfully.',
            'taskId' => $task->id,
            'boardId' => $boardId,
        ];
    }

    public function updateTaskStatus($id, $progress)
    {
        $task = $this->taskModel->findOrFail($id);

        $this->authorizeUserForTask($task, auth()->user());

        // Update the task progress
        $task->progress = $progress;
        $task->save();

        // Dispatch the BoardTaskUpdated event
        broadcast(new BoardTaskUpdated($task->id, $task->board_id, auth()->id()));

        return [
            'success' => true,
            'message' => 'Task status updated',
        ];
    
    }
    
    public function deleteAttachment(Task $task, $attachmentId)
    {
        $this->authorizeUserForTask($task, auth()->user());
    
        // Find the media by attachment ID
        $media = $task->getMedia('attachments')->find($attachmentId);
    
        // Check if media exists
        if (!$media) {
            return ['error' => 'Attachment not found.'];
        }
    
        // Delete the media
        $media->delete();
    
        return [
            'success' => true,
            'message' => 'Attachment deleted successfully.',
        ];
    }
    
    
    
    public function authorizeUserForTask(Task $task, $user)
    {
        if (!$user->can('isOwnerOrCollaborator', $task)) {
            abort(403, 'Unauthorized action.');
        }
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
