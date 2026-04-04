<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

     protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_banned',
        'ban_reason',
        'referral_code', 
        'referred_by',
        'streak_days',
        'last_login_date',   
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
            'streak_days'     => 'integer',
            'last_login_date' => 'date',
        ];
    }

    // ── Relations ──

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function receivedLikes()
    {
        return $this->hasMany(Like::class, 'liked_user_id');
    }

    public function passes()
    {
        return $this->hasMany(Pass::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

     public function matchesAsUser1()
    {
        return $this->hasMany(\App\Models\Matche::class, 'user1_id');
    }
 
    public function matchesAsUser2()
    {
        return $this->hasMany(\App\Models\Matche::class, 'user2_id');
    }
 
    public function matches()
    {
        return \App\Models\Matche::where('user1_id', $this->id)
            ->orWhere('user2_id', $this->id);
    }
 

    // ── Helpers ──

    public function hasProfile(): bool
    {
        return $this->profile !== null;
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function isBanned(): bool
    {
        return $this->is_banned === true;
    }

    /**
     * Vérifie si l'utilisateur a un abonnement actif (essai ou payé).
     */
    public function hasActiveSubscription(): bool
    {
        $sub = $this->subscription;
        return $sub && $sub->isActive();
    }

    /**
     * Récupère ou crée l'abonnement d'essai.
     */
    public function getOrCreateSubscription(): Subscription
    {
        $sub = $this->subscription;

        if (!$sub) {
            $sub = Subscription::createTrial($this->id);
            $this->load('subscription');
        }

        return $sub;
    }

    public function referrals()
    {
        return $this->hasMany(\App\Models\Referral::class, 'referrer_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function getStreakBadgeAttribute(): string
    {
        $streak = $this->streak_days ?? 0;
        if ($streak >= 100) return '🏆';
        if ($streak >= 30)  return '⚡';
        if ($streak >= 7)   return '🔥';
        if ($streak >= 3)   return '✨';
        return '';
    }
}
