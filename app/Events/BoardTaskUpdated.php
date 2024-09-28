<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BoardTaskUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $taskId;
    public $boardId;
    public $userId;

    public function __construct($taskId, $boardId, $userId)
    {
        $this->taskId = $taskId;
        $this->boardId = $boardId;
        $this->userId = $userId;
    }


    public function broadcastOn(): array
    {
        return [
            new Channel("boards.{$this->boardId}")
        ];
    }

    public function broadcastAs()
    {
        return 'task.updated';
    }

    public function broadcastWith()
    {
        return [
            'taskId' => $this->taskId,
            'userId' => $this->userId,
        ];
    }
}