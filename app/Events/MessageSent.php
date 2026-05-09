<?php

namespace App\Events;

use App\Models\Message;
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
        $this->senderPhoto = $sender->profile?->photo_url ?? 'https://ui-avatars.com/api/?background=1a1145&color=ff5e6c&bold=true&name=' . urlencode(substr($sender->name, 0, 2));
        $this->message = $message->message;
        $this->time = $message->created_at->format('H:i');
        $this->messageId = $message->id;

        // FIX: utiliser $a->url qui gère S3/Supabase au lieu de asset('storage/...')
        $this->attachments = $message->attachments->map(fn($a) => [
            'url' => $a->url,
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

    public function broadcastWith(): array
    {
        return [
            'matchId' => $this->matchId,
            'match_id' => $this->matchId,
            'senderId' => $this->senderId,
            'sender_id' => $this->senderId,
            'senderName' => $this->senderName,
            'sender_name' => $this->senderName,
            'senderPhoto' => $this->senderPhoto,
            'sender_photo' => $this->senderPhoto,
            'message' => $this->message,
            'time' => $this->time,
            'messageId' => $this->messageId,
            'message_id' => $this->messageId,
            'attachments' => $this->attachments,
        ];
    }
}
