<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'match_id',
        'sender_id',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    // ── Relations ──

    public function match()
    {
        return $this->belongsTo(Matche::class, 'match_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    // ── Helpers ──

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isMine(int $userId): bool
    {
        return $this->sender_id === $userId;
    }
}
