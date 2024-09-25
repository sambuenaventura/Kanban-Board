<?php

namespace App\Models;

use Carbon\Carbon;
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
                            return Carbon::parse($task->due)->format('Y-m-d');
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
    
    public function scopeNotDone($query)
    {
        return $query->where('progress', '!=', 'done');
    }

    public function scopeOverdue($query)
    {
        return $query->notDone()->where('due', '<', Carbon::today());
    }

    public function scopeDueToday($query)
    {
        return $query->notDone()->whereDate('due', Carbon::today());
    }

    public function scopeDueSoon($query)
    {
        $tomorrow = Carbon::tomorrow();
        $threeDaysFromNow = Carbon::today()->addDays(3);
        return $query->notDone()->whereBetween('due', [$tomorrow, $threeDaysFromNow]);
    }

    public static function getTaskCounts($boardId)
    {
        return [
            'overdue' => self::where('board_id', $boardId)->overdue()->count(),
            'dueToday' => self::where('board_id', $boardId)->dueToday()->count(),
            'dueSoon' => self::where('board_id', $boardId)->dueSoon()->count(),
        ];
    }
    
    
    
    
    
    
}
