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

class BoardInvitationCount implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $invitationCount;

    public function __construct($userId, $invitationCount)
    {
        $this->userId = $userId;
        $this->invitationCount = $invitationCount;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("user.{$this->userId}"),
        ];
    }

    public function broadcastAs()
    {
        return 'invitation.count.updated';    
    }

    public function broadcastWith()
    {
        return [
            'invitation_count' => $this->invitationCount,
        ];
    }
}
