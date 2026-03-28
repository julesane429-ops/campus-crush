<?php

namespace App\Events;

use App\Models\Matche;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NewMatch implements ShouldBroadcastNow
{
    use Dispatchable;

    public int $matchId;
    public int $otherUserId;
    public string $otherUserName;
    public string $otherUserPhoto;

    public function __construct(Matche $match, User $forUser)
    {
        $other = $match->getOtherUser($forUser->id);
        $this->matchId = $match->id;
        $this->otherUserId = $other->id;
        $this->otherUserName = $other->name;
        $this->otherUserPhoto = $other->profile?->photo_url ?? asset('storage/profiles/default-avatar.png');
    }

    /**
     * On envoie sur le channel privé de l'utilisateur qui reçoit la notification.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->otherUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewMatch';
    }
}
