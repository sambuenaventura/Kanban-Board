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
    


}
