<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnonymousCrush extends Model
{
    protected $fillable = [
        'sender_id',
        'target_identifier',
        'target_type',
        'target_user_id',
        'message',
        'sender_university',
        'is_revealed',
        'revealed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_revealed' => 'boolean',
            'revealed_at' => 'datetime',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
