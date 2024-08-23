<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = ['user_id', 'name', 'description', 'due', 'priority', 'progress', 'tag'];

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
        return $tasks->where('progress', $progress)->groupBy(function($task) {
            return \Carbon\Carbon::parse($task->due)->format('Y-m-d');
        })->sortKeys();
    }

    
    public function getFormattedPriorityAttribute()
    {
        switch ($this->priority) {
            case 'low':
                return 'L';
            case 'medium':
                return 'M';
            case 'high':
                return 'H';
            default:
                return '';

        }
    }

    // public function getPriorityCssClassAttribute()
    // {
    //     switch ($this->priority) {
    //         case 'low':
    //             return 'bg-yellow-200';
    //         case 'medium':
    //             return 'bg-green-200';
    //         case 'high':
    //             return 'bg-red-200';
    //         default:
    //             return 'text-gray-500 bg-gray-200';
    //     }
    // }
    
    

}
