<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class UserTyping implements ShouldBroadcastNow
{
    use Dispatchable;

    public int $matchId;
    public int $userId;
    public string $userName;

    public function __construct(int $matchId, int $userId, string $userName)
    {
        $this->matchId = $matchId;
        $this->userId = $userId;
        $this->userName = $userName;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.' . $this->matchId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserTyping';
    }

    public function broadcastWith(): array
    {
        return [
            'matchId' => $this->matchId,
            'match_id' => $this->matchId,
            'userId' => $this->userId,
            'user_id' => $this->userId,
            'userName' => $this->userName,
            'user_name' => $this->userName,
        ];
    }
}
