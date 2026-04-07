<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatSession extends Model
{
    protected $fillable = [
        'user_id', 'bot_type', 'bot_name', 'bot_avatar', 'is_active', 'message_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'message_count' => 'integer',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function messages() { return $this->hasMany(AiChatMessage::class, 'session_id'); }
}
