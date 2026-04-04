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
}
