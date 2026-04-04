<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateStreak
{
    /**
     * Met à jour le streak de connexion de l'utilisateur.
     * Throttlé à 1 fois par jour via cache pour éviter les écritures inutiles.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user     = Auth::user();
            $cacheKey = "streak_updated_{$user->id}";

            if (!Cache::has($cacheKey)) {
                $this->updateStreak($user);
                // Ne recalculer qu'une fois par heure max
                Cache::put($cacheKey, true, now()->addHour());
            }
        }

        return $next($request);
    }

    private function updateStreak($user): void
    {
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $lastLogin = $user->last_login_date?->toDateString();

        if ($lastLogin === $today) {
            // Déjà connecté aujourd'hui → rien à faire
            return;
        }

        if ($lastLogin === $yesterday) {
            // Connecté hier → on prolonge le streak
            $user->update([
                'streak_days'     => $user->streak_days + 1,
                'last_login_date' => $today,
            ]);
        } else {
            // Streak cassé (ou première connexion) → on repart à 1
            $user->update([
                'streak_days'     => 1,
                'last_login_date' => $today,
            ]);
        }

        // 🎁 Récompense automatique à certains paliers
        $this->checkStreakRewards($user->fresh());
    }

    private function checkStreakRewards($user): void
    {
        $streak = $user->streak_days;

        // Paliers de récompense : 7j, 30j, 100j → +jours de premium
        $rewards = [7 => 3, 30 => 7, 100 => 30];

        if (isset($rewards[$streak])) {
            $days = $rewards[$streak];
            $sub  = \App\Models\Subscription::where('user_id', $user->id)->latest()->first();

            if ($sub) {
                $endDate = $sub->ends_at && $sub->ends_at->isFuture()
                    ? $sub->ends_at : now();
                $sub->update(['ends_at' => $endDate->addDays($days)]);
            }

            // Stocker la notif streak en session pour l'afficher côté client
            session()->flash("streak_reward_{$streak}", $days);
        }
    }
}