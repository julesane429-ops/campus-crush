<?php

namespace App\Services;

use App\Models\User;
use App\Models\Like;
use App\Models\Profile;
use Illuminate\Support\Facades\Cache;

class CompatibilityService
{
    public function calculate(User $user, Profile $candidateProfile): int
    {
        if (!$user->profile) {
            return 0;
        }

        $myProfile = $user->profile;
        $score     = 0;

        if ($myProfile->ufr && $myProfile->ufr === $candidateProfile->ufr) {
            $score += 40;
        }

        if ($myProfile->promotion && $myProfile->promotion === $candidateProfile->promotion) {
            $score += 30;
        }

        if ($myProfile->age && $candidateProfile->age) {
            $ageDiff = abs($myProfile->age - $candidateProfile->age);
            if ($ageDiff <= 2) {
                $score += 20;
            } elseif ($ageDiff <= 5) {
                $score += 10;
            }
        }

        if ($myProfile->university && $myProfile->university === $candidateProfile->university) {
            $score += 10;
        }

        // ✅ Fix perf : getFavoriteUfr mis en cache 30 minutes
        $favoriteUfr = $this->getFavoriteUfr($user->id);
        if ($favoriteUfr && $candidateProfile->ufr === $favoriteUfr) {
            $score += 10;
        }

        if ($myProfile->interests && $candidateProfile->interests) {
            $myInterests   = array_map('trim', explode(',', strtolower($myProfile->interests)));
            $theirInterests = array_map('trim', explode(',', strtolower($candidateProfile->interests)));
            $common        = array_intersect($myInterests, $theirInterests);
            $score        += count($common) * 3;
        }

        return min($score, 100);
    }

    /**
     * ✅ Fix perf : cache 30 minutes pour éviter de recharger
     * tous les likes + profils à chaque calcul de compatibilité.
     * Le cache est invalidé automatiquement quand l'utilisateur like.
     */
    private function getFavoriteUfr(int $userId): ?string
    {
        return Cache::remember("favorite_ufr_{$userId}", now()->addMinutes(30), function () use ($userId) {
            $likedProfiles = Like::where('user_id', $userId)
                ->with('likedUser.profile')
                ->get();

            if ($likedProfiles->isEmpty()) {
                return null;
            }

            return $likedProfiles
                ->pluck('likedUser.profile.ufr')
                ->filter()
                ->countBy()
                ->sortDesc()
                ->keys()
                ->first();
        });
    }

    /**
     * Invalider le cache quand l'utilisateur like quelqu'un.
     * À appeler dans SwipeController::like()
     */
    public function invalidateFavoriteUfrCache(int $userId): void
    {
        Cache::forget("favorite_ufr_{$userId}");
    }
}