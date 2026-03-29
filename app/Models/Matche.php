<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Matche extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'user1_id',
        'user2_id',
        'blocked_by_user1',
        'blocked_by_user2',
    ];

    protected function casts(): array
    {
        return [
            'blocked_by_user1' => 'boolean',
            'blocked_by_user2' => 'boolean',
        ];
    }

    // ── Relations ──

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'match_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'match_id')->latestOfMany();
    }

    // ── Scopes ──

    /**
     * Matchs d'un utilisateur donné (corrige le bug orWhere sans groupement).
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user1_id', $userId)
                ->orWhere('user2_id', $userId);
        });
    }

    /**
     * Matchs non bloqués pour un utilisateur.
     */
    public function scopeNotBlockedFor(Builder $query, int $userId): Builder
    {
        return $query->forUser($userId)->where(function ($q) use ($userId) {
            $q->where(function ($sub) use ($userId) {
                $sub->where('user1_id', $userId)->where('blocked_by_user1', false);
            })->orWhere(function ($sub) use ($userId) {
                $sub->where('user2_id', $userId)->where('blocked_by_user2', false);
            });
        });
    }

    // ── Helpers ──

    /**
     * Retourne l'autre utilisateur du match.
     */
    public function getOtherUser(int $userId): User
    {
        return $this->user1_id === $userId ? $this->user2 : $this->user1;
    }

    /**
     * Vérifie si le match est bloqué par un utilisateur donné.
     */
    public function isBlockedBy(int $userId): bool
    {
        if ($this->user1_id === $userId) {
            return $this->blocked_by_user1;
        }

        return $this->blocked_by_user2;
    }

    /**
     * Vérifie si le match est bloqué (par n'importe qui).
     */
    public function isBlocked(): bool
    {
        return $this->blocked_by_user1 || $this->blocked_by_user2;
    }
}
