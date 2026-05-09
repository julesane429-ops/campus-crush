<?php

namespace App\Console\Commands;

use App\Models\Like;
use App\Models\Matche;
use App\Models\Pass;
use App\Models\User;
use App\Services\ExpoPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendDailyMatch extends Command
{
    protected $signature = 'crush:daily-match';
    protected $description = 'Envoie une notification "Match du jour" à chaque utilisateur actif';

    public function handle()
    {
        $expoPush = app(ExpoPushService::class);

        $users = User::whereHas('profile', function ($q) {
            $q->where('last_seen_at', '>=', now()->subDays(7));
        })->with('profile')->get();

        $sent = 0;

        foreach ($users as $user) {
            try {
                $match = $this->findDailyMatch($user);
                if (!$match) continue;

                $ttl = now()->endOfDay()->diffInSeconds(now());
                Cache::put("daily_match_{$user->id}", $match->id, $ttl);

                $matchProfile = $match->profile;
                $matchName = $match->name;
                $matchUniv = $matchProfile?->university_name ?? '';

                $expoPush->send(
                    $user,
                    '✨ Match du jour !',
                    "Découvre {$matchName}" . ($matchUniv ? " de {$matchUniv}" : '') . ". On vous trouve compatibles 💘",
                    ['type' => 'daily_match', 'user_id' => $match->id]
                );

                $sent++;

            } catch (\Exception $e) {
                $this->warn("Erreur pour user #{$user->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ {$sent} notifications 'Match du jour' envoyées sur {$users->count()} utilisateurs actifs.");
    }

    private function findDailyMatch(User $user): ?User
    {
        if (!$user->profile || !$user->profile->gender) return null;

        $targetGender = $user->profile->gender === 'homme' ? 'femme' : 'homme';

        $liked   = Like::where('user_id', $user->id)->pluck('liked_user_id');
        $passed  = Pass::where('user_id', $user->id)->pluck('passed_user_id');
        $matched = Matche::forUser($user->id)->get()
            ->map(fn($m) => $m->user1_id === $user->id ? $m->user2_id : $m->user1_id);
        $excluded = $liked->merge($passed)->merge($matched)->unique();

        return User::where('id', '!=', $user->id)
            ->whereNotIn('id', $excluded)
            ->where('is_banned', false)
            ->whereHas('profile', function ($q) use ($targetGender) {
                $q->where('gender', $targetGender);
            })
            ->with('profile')
            ->get()
            ->sortByDesc(function ($c) use ($user) {
                $score = 0;
                if ($c->profile->university_id === $user->profile->university_id) $score += 3;
                if ($c->profile->isBoosted()) $score += 2;
                if ($c->profile->last_seen_at?->gt(now()->subDays(2))) $score += 1;
                $myInterests    = $user->profile->interests_array;
                $theirInterests = $c->profile->interests_array;
                if (count(array_intersect($myInterests, $theirInterests)) > 0) $score += 2;
                return $score;
            })
            ->first();
    }
}
