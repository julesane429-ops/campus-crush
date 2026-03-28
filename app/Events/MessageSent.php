<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public int $matchId;
    public int $senderId;
    public string $senderName;
    public string $senderPhoto;
    public ?string $message;
    public string $time;
    public int $messageId;
    public array $attachments;

    public function __construct(Message $message)
    {
        $sender = $message->sender;
        $this->matchId = $message->match_id;
        $this->senderId = $message->sender_id;
        $this->senderName = $sender->name;
        $this->senderPhoto = $sender->profile?->photo_url ?? asset('storage/profiles/default-avatar.png');
        $this->message = $message->message;
        $this->time = $message->created_at->format('H:i');
        $this->messageId = $message->id;
        $this->attachments = $message->attachments->map(fn($a) => [
            'url' => asset('storage/' . $a->file_path),
        ])->toArray();
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.' . $this->matchId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
