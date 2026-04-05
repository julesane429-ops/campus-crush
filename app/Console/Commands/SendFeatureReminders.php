<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Like;
use App\Models\Matche;
use App\Models\AnonymousCrush;
use App\Models\Referral;
use App\Models\Review;
use App\Services\WebPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendFeatureReminders extends Command
{
    protected $signature = 'crush:feature-reminders';
    protected $description = 'Envoie des push notifications périodiques pour rappeler les fonctionnalités';

    public function handle()
    {
        $webPush = app(WebPushService::class);
        $sent = 0;

        // Utilisateurs actifs ces 3 derniers jours avec un profil
        $users = User::whereHas('profile', function ($q) {
            $q->where('last_seen_at', '>=', now()->subDays(3));
        })->with('profile')->get();

        foreach ($users as $user) {
            // Max 1 notification reminder par jour par utilisateur
            $cacheKey = "feature_reminder_{$user->id}_" . today()->toDateString();
            if (Cache::has($cacheKey)) continue;

            $reminder = $this->pickReminder($user);
            if (!$reminder) continue;

            try {
                $webPush->sendToUser(
                    $user,
                    $reminder['title'],
                    $reminder['body'],
                    $reminder['url']
                );
                Cache::put($cacheKey, true, now()->endOfDay());
                $sent++;
            } catch (\Exception $e) {
                // Pas grave
            }
        }

        $this->info("✅ {$sent} rappels envoyés.");
    }

    /**
     * Choisir le rappel le plus pertinent pour cet utilisateur.
     */
    private function pickReminder(User $user): ?array
    {
        $reminders = [];
        $profile = $user->profile;

        // ── Crush anonyme (jamais utilisé) ──
        $crushCount = AnonymousCrush::where('sender_id', $user->id)->count();
        if ($crushCount === 0) {
            $reminders[] = [
                'title' => '👀 Crush anonyme',
                'body' => 'Dis à quelqu\'un que tu l\'aimes sans te dévoiler ! Essaie le crush anonyme 💘',
                'url' => '/crush',
                'weight' => 5,
            ];
        }

        // ── Crushes reçus non révélés ──
        $unrevealed = AnonymousCrush::where('target_user_id', $user->id)
            ->where('is_revealed', false)->count();
        if ($unrevealed > 0) {
            $reminders[] = [
                'title' => "💌 {$unrevealed} crush anonyme" . ($unrevealed > 1 ? 's' : '') . " !",
                'body' => "Quelqu'un a un crush sur toi ! Ouvre l'app pour découvrir qui 👀",
                'url' => '/crush',
                'weight' => 10, // Haute priorité
            ];
        }

        // ── Likes reçus non matchés ──
        $unmatchedLikes = Like::where('liked_user_id', $user->id)
            ->whereNotIn('user_id', function ($q) use ($user) {
                $q->select('user1_id')->from('matches')->where('user2_id', $user->id)
                    ->union(
                        \Illuminate\Support\Facades\DB::table('matches')
                            ->select('user2_id')
                            ->where('user1_id', $user->id)
                    );
            })->count();
        if ($unmatchedLikes > 0) {
            $reminders[] = [
                'title' => "💕 {$unmatchedLikes} personne" . ($unmatchedLikes > 1 ? 's' : '') . " t'" . ($unmatchedLikes > 1 ? 'ont' : 'a') . " liké",
                'body' => 'Va voir qui et match avec ' . ($unmatchedLikes > 1 ? 'elles' : 'elle') . ' !',
                'url' => '/likes',
                'weight' => 8,
            ];
        }

        // ── Boost (pas boosté, inscrit > 5 jours) ──
        if (!$profile->isBoosted() && $user->created_at->lt(now()->subDays(5))) {
            $reminders[] = [
                'title' => '🚀 Passe en tête du swipe !',
                'body' => 'Booste ton profil pendant 24h pour 500 FCFA — jusqu\'à 10x plus de vues',
                'url' => '/boost',
                'weight' => 3,
            ];
        }

        // ── Parrainage (< 3 filleuls) ──
        $referralCount = Referral::where('referrer_id', $user->id)->count();
        if ($referralCount < 3) {
            $reminders[] = [
                'title' => '🎁 7 jours gratuits !',
                'body' => 'Invite un(e) ami(e) sur Campus Crush et gagne 7 jours de premium',
                'url' => '/referral',
                'weight' => 4,
            ];
        }

        // ── Avis (pas encore donné, inscrit > 7 jours) ──
        if (!Review::where('user_id', $user->id)->exists() && $user->created_at->lt(now()->subDays(7))) {
            $reminders[] = [
                'title' => '⭐ Ton avis compte !',
                'body' => 'Comment trouves-tu Campus Crush ? Laisse ton avis en 30 secondes',
                'url' => '/me',
                'weight' => 2,
            ];
        }

        // ── Streak bientôt récompensé ──
        $streak = $user->streak_days ?? 0;
        if ($streak === 6) {
            $reminders[] = [
                'title' => '🔥 Plus qu\'1 jour !',
                'body' => 'Connecte-toi demain pour atteindre 7 jours de suite et gagner 3 jours premium gratuits !',
                'url' => '/me',
                'weight' => 9,
            ];
        } elseif ($streak === 29) {
            $reminders[] = [
                'title' => '⚡ Incroyable ! 29 jours !',
                'body' => 'Encore 1 jour de connexion et tu débloques 7 jours de premium gratuits !',
                'url' => '/me',
                'weight' => 9,
            ];
        }

        // ── Abonnement expire bientôt ──
        $sub = $user->subscription;
        if ($sub && $sub->isActive() && $sub->daysRemaining() <= 3 && $sub->daysRemaining() > 0) {
            $days = $sub->daysRemaining();
            $reminders[] = [
                'title' => "⏰ Plus que {$days}j !",
                'body' => "Ton abonnement expire bientôt. Renouvelle pour ne pas perdre tes matchs !",
                'url' => '/subscription',
                'weight' => 7,
            ];
        }

        if (empty($reminders)) return null;

        // Choisir avec pondération (les plus importants ont plus de chances)
        usort($reminders, fn($a, $b) => $b['weight'] <=> $a['weight']);

        // 70% chance de prendre le plus important, 30% un aléatoire
        if (rand(1, 100) <= 70) {
            return $reminders[0];
        }

        return $reminders[array_rand($reminders)];
    }
}
