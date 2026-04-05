<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Like;
use App\Models\Pass;
use App\Models\Matche;
use App\Services\WebPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendDailyMatch extends Command
{
    protected $signature = 'crush:daily-match';
    protected $description = 'Envoie une notification "Match du jour" à chaque utilisateur actif';

    public function handle()
    {
        $webPush = app(WebPushService::class);

        // Utilisateurs actifs ces 7 derniers jours avec un profil
        $users = User::whereHas('profile', function ($q) {
            $q->where('last_seen_at', '>=', now()->subDays(7));
        })->with('profile')->get();

        $sent = 0;

        foreach ($users as $user) {
            try {
                $match = $this->findDailyMatch($user);

                if (!$match) continue;

                // Stocker en cache pour affichage (expire à minuit)
                $ttl = now()->endOfDay()->diffInSeconds(now());
                Cache::put("daily_match_{$user->id}", $match->id, $ttl);

                // Push notification
                $matchProfile = $match->profile;
                $matchName = $match->name;
                $matchUniv = $matchProfile?->university_name ?? '';

                $webPush->sendToUser(
                    $user,
                    '✨ Match du jour !',
                    "Découvre {$matchName}" . ($matchUniv ? " de {$matchUniv}" : '') . ". On vous trouve compatibles 💘",
                    '/swipe'
                );

                $sent++;

            } catch (\Exception $e) {
                $this->warn("Erreur pour user #{$user->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ {$sent} notifications 'Match du jour' envoyées sur {$users->count()} utilisateurs actifs.");
    }

    /**
     * Trouver le meilleur profil recommandé pour cet utilisateur.
     */
    private function findDailyMatch(User $user): ?User
    {
        if (!$user->profile || !$user->profile->gender) return null;

        $targetGender = $user->profile->gender === 'homme' ? 'femme' : 'homme';

        // Exclure : déjà liké, passé, matché
        $liked = Like::where('user_id', $user->id)->pluck('liked_user_id');
        $passed = Pass::where('user_id', $user->id)->pluck('passed_user_id');
        $matched = Matche::forUser($user->id)->get()
            ->map(fn($m) => $m->user1_id === $user->id ? $m->user2_id : $m->user1_id);
        $excluded = $liked->merge($passed)->merge($matched)->unique();

        // Préférer : même université, profil boosté, actif récemment
        $candidate = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $excluded)
            ->where('is_banned', false)
            ->whereHas('profile', function ($q) use ($targetGender, $user) {
                $q->where('gender', $targetGender);
            })
            ->with('profile')
            ->get()
            ->sortByDesc(function ($c) use ($user) {
                $score = 0;

                // Même université +3
                if ($c->profile->university_id === $user->profile->university_id) {
                    $score += 3;
                }

                // Profil boosté +2
                if ($c->profile->isBoosted()) {
                    $score += 2;
                }

                // Actif récemment +1
                if ($c->profile->last_seen_at && $c->profile->last_seen_at->gt(now()->subDays(2))) {
                    $score += 1;
                }

                // A des intérêts communs +2
                $myInterests = $user->profile->interests_array;
                $theirInterests = $c->profile->interests_array;
                if (count(array_intersect($myInterests, $theirInterests)) > 0) {
                    $score += 2;
                }

                return $score;
            })
            ->first();

        return $candidate;
    }
}
