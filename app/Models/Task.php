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
    
    public function scopeFilterByTags($query, $tags)
    {
        if ($tags) {
            $selectedTags = explode(',', $tags);
            $query->whereIn('tag', $selectedTags);
        }
        return $query;
    }



}
