<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ExpoPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendEngagementPush extends Command
{
    protected $signature = 'crush:engagement-push';
    protected $description = 'Envoie des push notifications d\'engagement : quotidiennes, week-end, ré-engagement';

    private array $dailyMessages = [
        ['title' => '💘 Ton crush t\'attend !', 'body' => 'De nouveaux profils t\'attendent. Viens swiper et trouve ton match !'],
        ['title' => '🔥 Qui t\'a liké aujourd\'hui ?', 'body' => 'Quelqu\'un t\'a peut-être liké. Connecte-toi pour voir !'],
        ['title' => '✨ Nouvelle journée, nouveaux matchs', 'body' => 'Des profils compatibles viennent de s\'inscrire. Viens découvrir !'],
        ['title' => '👀 Tu manques à tes matchs', 'body' => 'Tes matchs attendent de tes nouvelles. Envoie un message !'],
        ['title' => '💬 Un message t\'attend peut-être', 'body' => 'Connecte-toi pour vérifier tes conversations !'],
        ['title' => '🎯 Profils compatibles trouvés', 'body' => 'On a trouvé des profils qui pourraient te plaire. Viens voir !'],
        ['title' => '🤖 Teste l\'IA Campus Crush !', 'body' => 'Discute avec une IA, améliore ton profil et entraîne-toi à draguer 💬'],
    ];

    private array $weekendMessages = [
        ['title' => '🎉 C\'est le week-end !', 'body' => 'Le meilleur moment pour trouver ton crush. Viens swiper 🔥'],
        ['title' => '💃 Samedi soir, on match ?', 'body' => 'Les étudiants sont plus actifs le week-end. Profites-en pour matcher !'],
        ['title' => '🥳 Week-end = plus de monde en ligne', 'body' => 'C\'est le moment idéal pour trouver ton crush sur le campus !'],
        ['title' => '💘 Ton samedi sera plus beau avec un match', 'body' => 'Des centaines d\'étudiants sont en ligne. Rejoins-les !'],
    ];

    private array $reengagementMessages = [
        3 => ['title' => '😢 Tu nous manques !', 'body' => 'Ça fait 3 jours qu\'on ne t\'a pas vu. De nouveaux profils t\'attendent !'],
        5 => ['title' => '👀 Pendant ton absence...', 'body' => 'Plusieurs personnes ont visité le swipe. Reviens voir qui t\'a liké !'],
        7 => ['title' => '💔 Ton crush t\'attend depuis 1 semaine', 'body' => 'Reviens sur Campus Crush, il y a plein de nouveautés !'],
        14 => ['title' => '🔥 14 jours sans toi !', 'body' => 'Des dizaines de nouveaux profils se sont inscrits. Reviens découvrir !'],
        30 => ['title' => '💘 On ne t\'oublie pas !', 'body' => 'Ça fait un mois ! Plein de nouveaux étudiants t\'attendent sur Campus Crush.'],
    ];

    public function handle()
    {
        $expoPush = app(ExpoPushService::class);
        $isSaturday = now()->isSaturday();
        $sent = 0;

        // ════════════════════════════════════════════
        // 1. NOTIFICATION QUOTIDIENNE (utilisateurs actifs < 3 jours)
        // ════════════════════════════════════════════
        $activeUsers = User::whereHas('profile', function ($q) {
            $q->where('last_seen_at', '>=', now()->subDays(3));
        })->with('profile')->get();

        foreach ($activeUsers as $user) {
            $cacheKey = "engage_daily_{$user->id}_" . today()->toDateString();
            if (Cache::has($cacheKey)) continue;

            if ($isSaturday) {
                $msg = $this->weekendMessages[array_rand($this->weekendMessages)];
            } else {
                $pool = $user->ai_chat_unlocked
                    ? array_filter($this->dailyMessages, fn($m) => !str_contains($m['title'], 'IA'))
                    : $this->dailyMessages;
                $msg = array_values($pool)[array_rand($pool)];
            }

            $body = $this->personalize($user, $msg['body']);

            try {
                $expoPush->send($user, $msg['title'], $body);
                Cache::put($cacheKey, true, now()->endOfDay());
                $sent++;
            } catch (\Exception $e) {
                // Continuer
            }
        }

        $this->info("📱 {$sent} notifications quotidiennes envoyées.");

        // ════════════════════════════════════════════
        // 2. RÉ-ENGAGEMENT (utilisateurs inactifs)
        // ════════════════════════════════════════════
        $reengaged = 0;

        foreach ($this->reengagementMessages as $days => $msg) {
            $inactiveUsers = User::whereHas('profile', function ($q) use ($days) {
                $q->whereBetween('last_seen_at', [
                    now()->subDays($days)->subHours(12),
                    now()->subDays($days)->addHours(12),
                ]);
            })->with('profile')->get();

            foreach ($inactiveUsers as $user) {
                $cacheKey = "engage_reactivate_{$user->id}_{$days}d";
                if (Cache::has($cacheKey)) continue;

                $newLikes = \App\Models\Like::where('liked_user_id', $user->id)
                    ->where('created_at', '>=', now()->subDays($days))
                    ->count();

                $body = $msg['body'];
                if ($newLikes > 0) {
                    $body = "🔥 {$newLikes} personne" . ($newLikes > 1 ? 's' : '') . " t'" . ($newLikes > 1 ? 'ont' : 'a') . " liké pendant ton absence ! " . $body;
                }

                try {
                    $expoPush->send($user, $msg['title'], $body);
                    Cache::put($cacheKey, true, now()->addDays(7));
                    $reengaged++;
                } catch (\Exception $e) {
                    // Continuer
                }
            }
        }

        $this->info("🔄 {$reengaged} notifications de ré-engagement envoyées.");

        // ════════════════════════════════════════════
        // 3. SAMEDI SPÉCIAL — 2ème notification en soirée
        // ════════════════════════════════════════════
        if ($isSaturday && now()->hour >= 18) {
            $satEvening = 0;
            $eveningMsg = [
                'title' => '🌙 Samedi soir sur Campus Crush',
                'body' => 'C\'est le moment où tout le monde est en ligne. Viens matcher !',
            ];

            foreach ($activeUsers as $user) {
                $cacheKey = "engage_sat_evening_{$user->id}_" . today()->toDateString();
                if (Cache::has($cacheKey)) continue;

                try {
                    $expoPush->send($user, $eveningMsg['title'], $eveningMsg['body']);
                    Cache::put($cacheKey, true, now()->endOfDay());
                    $satEvening++;
                } catch (\Exception $e) {
                    // Continuer
                }
            }

            $this->info("🌙 {$satEvening} notifications samedi soir envoyées.");
        }

        return Command::SUCCESS;
    }

    private function personalize(User $user, string $body): string
    {
        $unseenLikes = \App\Models\Like::where('liked_user_id', $user->id)
            ->whereNotIn('user_id', function ($q) use ($user) {
                $q->select('user1_id')->from('matches')->where('user2_id', $user->id)
                    ->union(
                        \Illuminate\Support\Facades\DB::table('matches')
                            ->select('user2_id')
                            ->where('user1_id', $user->id)
                    );
            })
            ->count();

        if ($unseenLikes > 0) {
            return "💕 {$unseenLikes} like" . ($unseenLikes > 1 ? 's' : '') . " en attente ! " . $body;
        }

        return $body;
    }
}
