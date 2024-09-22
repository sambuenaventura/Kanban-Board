<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    
    protected $fillable = [
        'board_id',
        'board_user_id',
        'name',
        'description',
        'due',
        'priority',
        'progress',
        'tag',
        'attachment',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function boardUser()
    {
        return $this->belongsTo(BoardUser::class);
    }



    public function scopeForUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public static function getUserTasks() 
    {
        return self::forUser()
        ->latest()
        ->paginate();
    }
    
    public static function getAllTags() 
    {
        return self::forUser()
        ->distinct()
        ->pluck('tag')
        ->filter();
    }

    public static function countTaskByStatus($tasks, $progress)
    {
        return $tasks->where('progress', $progress)
        ->count();
    }
    
    public function scopeFilterByTags($query, $tags)
    {
        if ($tags) {
            $selectedTags = explode(',', $tags);
            $query->whereIn('tag', $selectedTags);
        }
        return $query;
    }

    public static function getTaskByProgress($tasks, $progress)
    {
        return $tasks->where('progress', $progress)
                        ->groupBy(function($task) 
                        {
                            return \Carbon\Carbon::parse($task->due)->format('Y-m-d');
                        })->sortKeys();
    }

    
    public function getFormattedPriorityAttribute()
    {
        switch ($this->priority) {
            case 'low':
                return 'Low';
            case 'medium':
                return 'Medium';
            case 'high':
                return 'High';
            default:
                return '';

        }
    }
    
    public static function getDues($tasks)
    {
        $today = (new DateTime())->format('Y-m-d');
        $yesterday = (new DateTime())->modify('-1 day')->format('Y-m-d');
        $tomorrow = (new DateTime())->modify('+1 day')->format('Y-m-d');
    
        foreach ($tasks as $task) {
            if ($task->due === $today) {
                $task->due_day = 'Due Today';
            } elseif ($task->due === $yesterday) {
                $task->due_day = 'Due Yesterday';
            } elseif ($task->due === $tomorrow) {
                $task->due_day = 'Due Tomorrow';
            } else {
                $task->due_day = '';
            }
        }
    
        return $tasks;
    }
    
}
