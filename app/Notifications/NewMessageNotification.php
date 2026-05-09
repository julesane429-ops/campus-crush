<?php

namespace App\Notifications;

use App\Channels\ExpoPushChannel;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Message $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', ExpoPushChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->message->sender;
        $preview = mb_substr($this->message->message ?? '📷 Photo', 0, 50);

        return [
            'type' => 'new_message',
            'match_id' => $this->message->match_id,
            'message_id' => $this->message->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'sender_photo' => $sender->profile?->photo_url,
            'preview' => $preview,
            'message' => $sender->name . ': ' . $preview,
        ];
    }

    public function toExpoPush(object $notifiable): array
    {
        $sender = $this->message->sender;
        $preview = mb_substr($this->message->message ?? '📷 Photo', 0, 50);

        return [
            "💬 {$sender->name}",
            $preview,
            ['type' => 'new_message', 'match_id' => $this->message->match_id],
        ];
    }
}
