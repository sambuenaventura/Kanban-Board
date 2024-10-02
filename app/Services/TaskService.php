<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    protected $taskModel;

    public function __construct(Task $taskModel)
    {
        $this->taskModel = $taskModel;
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
    


}
