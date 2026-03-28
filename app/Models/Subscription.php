<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'amount',
        'payment_method',
        'transaction_id',
        'starts_at',
        'ends_at',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * L'abonnement est-il actif (payé ou en essai) ?
     */
    public function isActive(): bool
    {
        if ($this->status === 'cancelled') {
            return false;
        }

        // En période d'essai
        if ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture()) {
            return true;
        }

        // Abonnement payé actif
        if ($this->status === 'active' && $this->ends_at && $this->ends_at->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * Nombre de jours restants.
     */
    public function daysRemaining(): int
    {
        $endDate = $this->status === 'trial' ? $this->trial_ends_at : $this->ends_at;

        if (!$endDate || $endDate->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($endDate);
    }

    /**
     * Est-ce un essai gratuit ?
     */
    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    /**
     * Créer un essai gratuit de 30 jours pour un nouvel utilisateur.
     */
    public static function createTrial(int $userId): self
    {
        return self::create([
            'user_id' => $userId,
            'status' => 'trial',
            'amount' => 0,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'trial_ends_at' => now()->addDays(30),
        ]);
    }

    /**
     * Activer un abonnement payé (1 mois).
     */
    public function activate(string $paymentMethod, ?string $transactionId = null): self
    {
        $this->update([
            'status' => 'active',
            'amount' => 1000,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        return $this;
    }
}
