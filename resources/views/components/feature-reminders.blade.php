{{--
    Smart Feature Reminders
    Affiche UN toast contextuel de temps en temps pour rappeler les fonctionnalités.
    Chaque rappel a un cooldown de 3 jours via localStorage.
    Inclure dans les pages principales : swipe, matches, profile, likes.
--}}

@auth
@php
$user = auth()->user();
$profile = $user->profile;
if (!$profile) return;

$sub = $user->subscription;
$isBoosted = $profile->boosted_until && $profile->boosted_until->isFuture();
$streak = $user->streak_days ?? 0;
$referralCount = \App\Models\Referral::where('referrer_id', $user->id)->count();
$crushCount = \App\Models\AnonymousCrush::where('sender_id', $user->id)->count();
$likesReceived = \App\Models\Like::where('liked_user_id', $user->id)->count();
$matchesCount = \App\Models\Matche::forUser($user->id)->count();
$hasReview = \App\Models\Review::where('user_id', $user->id)->exists();
$daysRemaining = $sub ? $sub->daysRemaining() : 0;

// Construire la liste des rappels possibles (contextuel)
$reminders = [];

// Crush anonyme — si jamais utilisé
if ($crushCount === 0) {
$reminders[] = [
'key' => 'crush_anon',
'icon' => '👀',
'text' => 'Tu savais que tu peux envoyer un crush anonyme ? La personne ne saura pas que c\'est toi 💘',
'cta' => 'Essayer',
'url' => route('crush.index'),
'bg' => 'linear-gradient(135deg, rgba(255,94,108,0.95), rgba(255,138,92,0.95))',
];
}

// Boost — si pas boosté et a des likes
if (!$isBoosted && $likesReceived > 0) {
$reminders[] = [
'key' => 'boost',
'icon' => '🚀',
'text' => 'Booste ton profil pour apparaître en premier dans le swipe — 500 FCFA / 24h',
'cta' => 'Booster',
'url' => route('boost.index'),
'bg' => 'linear-gradient(135deg, rgba(255,193,69,0.95), rgba(255,138,92,0.95))',
];
}

// Parrainage — si moins de 3 filleuls
if ($referralCount < 3) {
    $reminders[]=[ 'key'=> 'referral',
    'text' => 'Invite tes potes sur Campus Crush et gagne 7 jours de premium par ami inscrit 🎁',
    'icon' => '🎁',
    'cta' => 'Parrainer',
    'url' => route('referral.index'),
    'bg' => 'linear-gradient(135deg, rgba(168,85,247,0.95), rgba(108,92,231,0.95))',
    ];
    }

    // Avis — si pas encore laissé d'avis et inscrit depuis > 3 jours
    if (!$hasReview && $user->created_at->lt(now()->subDays(3))) {
    $reminders[] = [
    'key' => 'review',
    'icon' => '⭐',
    'text' => 'Tu aimes Campus Crush ? Laisse un avis pour aider d\'autres étudiants à nous découvrir !',
    'cta' => 'Laisser un avis',
    'url' => route('profile.show') . '#review',
    'bg' => 'linear-gradient(135deg, rgba(255,193,69,0.95), rgba(255,94,108,0.95))',
    ];
    }

    // Rappel avis mensuel — même si déjà donné, rappeler de mettre à jour
    if ($hasReview && $user->created_at->lt(now()->subMonth())) {
    $reminders[] = [
    'key' => 'review_monthly',
    'icon' => '💬',
    'text' => 'Ton expérience a évolué ? Mets à jour ton avis sur Campus Crush, ça nous aide beaucoup !',
    'cta' => 'Modifier mon avis',
    'url' => route('profile.show') . '#review',
    'bg' => 'linear-gradient(135deg, rgba(255,193,69,0.95), rgba(255,138,92,0.95))',
    ];
    }

    // Streak — si streak > 0 mais < 7
        if ($streak> 0 && $streak < 7) {
            $daysTo7=7 - $streak;
            $reminders[]=[ 'key'=> 'streak',
            'icon' => '🔥',
            'text' => "Encore {$daysTo7} jour" . ($daysTo7 > 1 ? 's' : '') . " de suite et tu gagnes 3 jours de premium gratuit !",
            'cta' => 'Continuer',
            'url' => route('profile.show'),
            'bg' => 'linear-gradient(135deg, rgba(255,138,92,0.95), rgba(255,94,108,0.95))',
            ];
            }

            // Abonnement expire bientôt
            if ($daysRemaining > 0 && $daysRemaining <= 5) {
                $reminders[]=[ 'key'=> 'sub_expiring',
                'icon' => '⏰',
                'text' => "Ton abonnement expire dans {$daysRemaining} jour" . ($daysRemaining > 1 ? 's' : '') . ". Renouvelle pour ne pas perdre tes matchs !",
                'cta' => 'Renouveler',
                'url' => route('subscription.index'),
                'bg' => 'linear-gradient(135deg, rgba(239,68,68,0.95), rgba(255,94,108,0.95))',
                ];
                }

                // Qui t'a liké — si des likes non vus
                if ($likesReceived > $matchesCount && $likesReceived > 0) {
                $reminders[] = [
                'key' => 'likes_pending',
                'icon' => '💕',
                'text' => 'Des personnes t\'ont liké ! Va voir qui et match avec elles 👀',
                'cta' => 'Voir',
                'url' => route('likes.index'),
                'bg' => 'linear-gradient(135deg, rgba(236,72,153,0.95), rgba(168,85,247,0.95))',
                ];
                }

                // Partage profil — si jamais partagé (on check juste de temps en temps)
                $reminders[] = [
                'key' => 'share_profile',
                'icon' => '📤',
                'text' => 'Partage ton profil sur WhatsApp pour que plus de monde te découvre et te like !',
                'cta' => 'Partager',
                'url' => route('profile.show'),
                'bg' => 'linear-gradient(135deg, rgba(37,211,102,0.95), rgba(34,197,94,0.95))',
                ];
                @endphp

                <script>
                    (function() {
                        const COOLDOWN_DAYS = 3;
                        const MONTHLY_KEYS = ['review_monthly']; // Cooldown spécial de 30 jours
                        const SHOW_DELAY_MS = 5000; // Afficher 5s après le chargement

                        const reminders = @json($reminders);
                        if (!reminders.length) return;

                        // Filtrer les rappels déjà affichés récemment
                        const now = Date.now();
                        const available = reminders.filter(r => {
                            const lastShown = localStorage.getItem('cc_remind_' + r.key);
                            if (!lastShown) return true;
                            const elapsed = now - parseInt(lastShown, 10);
                            const cooldown = MONTHLY_KEYS.includes(r.key) ? 30 : COOLDOWN_DAYS;
                            return elapsed > cooldown * 24 * 60 * 60 * 1000;
                        });

                        if (!available.length) return;

                        // Choisir un rappel aléatoire parmi les disponibles
                        const reminder = available[Math.floor(Math.random() * available.length)];

                        setTimeout(() => {
                            showFeatureToast(reminder);
                            localStorage.setItem('cc_remind_' + reminder.key, now.toString());
                        }, SHOW_DELAY_MS);

                        function showFeatureToast(r) {
                            const toast = document.createElement('div');
                            toast.id = 'feature-toast';
                            toast.style.cssText = `
            position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(20px);
            z-index: 99990; width: calc(100% - 32px); max-width: 380px;
            border-radius: 20px; padding: 16px 18px;
            background: ${r.bg};
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
            font-family: 'Sora', sans-serif;
            opacity: 0;
            transition: opacity 0.4s ease, transform 0.4s cubic-bezier(0.22,1,0.36,1);
        `;

                            toast.innerHTML = `
            <div style="display:flex; align-items:flex-start; gap:12px;">
                <span style="font-size:24px; flex-shrink:0; margin-top:2px;">${r.icon}</span>
                <div style="flex:1; min-width:0;">
                    <p style="font-size:12px; color:rgba(255,255,255,0.92); line-height:1.5; margin-bottom:10px; font-weight:500;">${r.text}</p>
                    <div style="display:flex; gap:8px;">
                        <a href="${r.url}" style="
                            display:inline-flex; padding:7px 16px; border-radius:10px;
                            background:rgba(255,255,255,0.2); color:#fff;
                            font-size:11px; font-weight:700; text-decoration:none;
                            border: 1px solid rgba(255,255,255,0.15);
                        ">${r.cta}</a>
                        <button onclick="dismissToast()" style="
                            padding:7px 12px; border-radius:10px; border:none; cursor:pointer;
                            background:rgba(0,0,0,0.15); color:rgba(255,255,255,0.5);
                            font-size:11px; font-weight:600; font-family:inherit;
                        ">Plus tard</button>
                    </div>
                </div>
                <button onclick="dismissToast()" style="
                    flex-shrink:0; width:24px; height:24px; border-radius:50%; border:none; cursor:pointer;
                    background:rgba(0,0,0,0.15); color:rgba(255,255,255,0.4); font-size:12px;
                    display:flex; align-items:center; justify-content:center;
                ">✕</button>
            </div>
        `;

                            document.body.appendChild(toast);

                            // Animate in
                            requestAnimationFrame(() => {
                                toast.style.opacity = '1';
                                toast.style.transform = 'translateX(-50%) translateY(0)';
                            });

                            // Auto dismiss après 12 secondes
                            setTimeout(() => dismissToast(), 12000);
                        }

                        window.dismissToast = function() {
                            const t = document.getElementById('feature-toast');
                            if (!t) return;
                            t.style.opacity = '0';
                            t.style.transform = 'translateX(-50%) translateY(20px)';
                            setTimeout(() => t.remove(), 400);
                        };
                    })();
                </script>
                @endauth