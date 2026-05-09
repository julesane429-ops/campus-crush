<?php

namespace App\Notifications;

use App\Channels\ExpoPushChannel;
use App\Models\Matche;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewMatchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Matche $match,
        private User $otherUser
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', ExpoPushChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_match',
            'match_id' => $this->match->id,
            'user_id' => $this->otherUser->id,
            'user_name' => $this->otherUser->name,
            'user_photo' => $this->otherUser->profile?->photo_url,
            'message' => $this->otherUser->name . ' et toi avez matché ! 🎉',
        ];
    }

    public function toExpoPush(object $notifiable): array
    {
        return [
            '❤️ Nouveau match !',
            "Tu as matché avec {$this->otherUser->name} !",
            ['type' => 'new_match', 'match_id' => $this->match->id],
        ];
    }
}
