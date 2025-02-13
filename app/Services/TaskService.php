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
    protected $idempotencyService;

    public function __construct(Task $taskModel, Board $boardModel, BoardUser $boardUserModel, IdempotencyService $idempotencyService)
    {
        $this->taskModel = $taskModel;
        $this->boardModel = $boardModel;
        $this->boardUserModel = $boardUserModel;
        $this->idempotencyService = $idempotencyService;
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
    
    public function getTaskById(string $id)
    {
        return $this->taskModel->find($id);

    }
    
    public function getTaskByProgress($tasks, $progress)
    {
        return $tasks->where('progress', $progress)
                     ->map(function ($task) {
                         if ($task->due < Carbon::today()) {
                             $task->is_overdue = true;
                         } elseif ($task->due->isToday()) {
                             $task->is_due_today = true;
                         } elseif ($task->due->isBetween(Carbon::tomorrow(), Carbon::today()->addWeek())) {
                             $task->is_due_soon = true;
                         }
                         return $task;
                     })
                     ->groupBy(function ($task) {
                         return Carbon::parse($task->due)->format('Y-m-d');
                     })->sortKeys();
    }

    public function getTaskCounts($tasks)
    {
        $taskCounts = [
            'to_do' => 0,
            'in_progress' => 0,
            'done' => 0,
        ];
    
        foreach ($tasks as $task) {
            switch ($task->progress) {
                case 'to_do':
                    $taskCounts['to_do']++;
                    break;
                case 'in_progress':
                    $taskCounts['in_progress']++;
                    break;
                case 'done':
                    $taskCounts['done']++;
                    break;
            }
        }
    
        return $taskCounts;
    }
    
    public function createTask(Board $board, array $data, string $idempotencyKey)
    {
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
    
        // Check the number of existing tasks for the board
        $taskCount = $this->taskModel->where('board_id', $board->id)->count();
        if ($taskCount >= 100) {
            return ['error' => 'You have reached the maximum limit of 100 tasks for this board.'];
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

        // Invalidate board-specific task cache
        Cache::forget("board_{$task->board_id}_tasks");

        broadcast(new BoardTaskCreated($task));
    
        return ['success' => 'Task created successfully.', 'task' => $task];
    }

    public function getTaskDetails($boardId, $taskId)
    {
        // Fetch the task with media (attachments)
        $task = $this->taskModel->with('media')->findOrFail($taskId);

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
        $task = $this->taskModel->findOrFail($taskId);
        
        return $task;
    }

    public function updateTask($taskId, array $data, $idempotencyKey)
    {
        return $this->idempotencyService->process("update_task_{$taskId}", $idempotencyKey, function () use ($taskId, $data) {
            
            $task = $this->getTaskById($taskId);
        
            if (!$task) {
                return [
                    'status' => 'error',
                    'message' => 'Task not found.',
                ];
            }

            // Check if the progress is being updated
            $progressChanged = $task->progress !== ($data['progress'] ?? null);
            
            $task->update($data);

            // Invalidate board-specific task cache
            Cache::forget("board_{$task->board_id}_tasks");

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
                'status' => 'success',
                'message' => $message,
                'task' => $task,
            ];
        });
    }

    public function addAttachmentToTask(Task $task, $file, $idempotencyKey)
    {
        return $this->idempotencyService->process("add_attachment_{$task->id}", $idempotencyKey, function () use ($task, $file) {
    
            // Add media to task
            $task->addMedia($file)->toMediaCollection('attachments');
    
            return [
                'status' => 'success',
                'message' => 'Attachment uploaded successfully.',
            ];
        });
    }

    public function deleteTask(string $id, string $idempotencyKey)
    {
        return $this->idempotencyService->process("delete_task_{$id}", $idempotencyKey, function () use ($id) 
            {
                $task = $this->taskModel->findOrFail($id);
                $task->delete();

                // Invalidate board-specific task cache
                Cache::forget("board_{$task->board_id}_tasks");

                return [
                    'status' => 'success',
                    'message' => 'Task deleted successfully.',
                ];
            }
        );
    }
    
    public function deleteTaskAjax($id)
    {
        $task = $this->taskModel->findOrFail($id);
        
        $boardId = $task->board_id;
    
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
        
        $task->progress = $progress;
        $task->save();
        
        // Invalidate board-specific task cache if necessary
        Cache::forget("board_{$task->board_id}_tasks"); 
        
        broadcast(new BoardTaskUpdated($task->id, $task->board_id, auth()->id()));
        
        return [
            'success' => true,
            'message' => 'Task status updated',
        ];
    }

    public function deleteAttachment(Task $task, $attachmentId, $idempotencyKey)
    {
        return $this->idempotencyService->process("delete_attachment_{$task->id}", $idempotencyKey, function () use ($task, $attachmentId) {
            
            // Find the media by attachment ID
            $media = $task->getMedia('attachments')->find($attachmentId);
    
            // Check if media exists
            if (!$media) {
                return [
                    'status' => 'error',
                    'message' => 'Attachment not found.',
                ];
            }
    
            // Delete the media
            $media->delete();
    
            return [
                'status' => 'success',
                'message' => 'Attachment deleted successfully.',
            ];
        });
    }

}
