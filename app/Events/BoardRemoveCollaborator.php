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

class BoardRemoveCollaborator implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $boardId;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $boardId)
    {
        $this->userId = $userId;
        $this->boardId = $boardId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('boards'),
        ];
    }
    

    public function broadcastAs()
    {
        return 'user.removed';    
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'board_id' => $this->boardId,
        ];
    }
}