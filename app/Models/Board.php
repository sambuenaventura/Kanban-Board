<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];
    
    // Set relationhships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'board_users')->withPivot('role');
    }
    
    public function collaborators() // This method in the Board model says, "Show me all users who are collaborators on this board."
    {
        return $this->belongsToMany(User::class, 'board_users')
                    ->wherePivot('role', 'collaborator'); // This part filters the users to only include those with a specific role (in this case, "collaborator").
    }

    public function boardUsers()
    {
        return $this->hasMany(BoardUser::class)->with('user');
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
