<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardInvitation extends Model
{
    use HasFactory;

    protected $fillable = ['board_id', 'user_id', 'invited_by', 'status'];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function invitedUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
