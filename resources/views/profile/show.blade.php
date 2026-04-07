<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Campus Crush - Mon Profil</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Sora', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
            min-height: 100vh;
        }

        .cc-surface {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(24px);
        }

        .cc-surface-raised {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(40px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .cc-gradient-text {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .cc-mono {
            font-family: 'Space Mono', monospace;
        }

        /* ═══ PHOTO ═══ */
        .photo-ring {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            padding: 3px;
            background: conic-gradient(from 0deg, #ff5e6c, #ffc145, #a855f7, #ff5e6c);
        }

        .photo-ring::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            background: conic-gradient(from 90deg, rgba(255, 94, 108, 0.15), rgba(168, 85, 247, 0.15), rgba(255, 193, 69, 0.15), rgba(255, 94, 108, 0.15));
            z-index: -1;
            filter: blur(12px);
        }

        /* ═══ ORBS ═══ */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            opacity: 0.1;
        }

        /* ═══ ANIMATIONS ═══ */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .fade-up {
            animation: fadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .d1 {
            animation-delay: .08s;
        }

        .d2 {
            animation-delay: .16s;
        }

        .d3 {
            animation-delay: .24s;
        }

        .d4 {
            animation-delay: .32s;
        }

        .d5 {
            animation-delay: .40s;
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: scale(0.5);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .count-up {
            animation: countUp 0.4s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        /* ═══ STAT CARD HOVER ═══ */
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:active {
            transform: scale(0.97);
        }

        /* Safe area */
        .safe-top {
            padding-top: max(env(safe-area-inset-top, 12px), 12px);
        }
    </style>
</head>

<body class="text-white">

    <div class="orb" style="width:250px;height:250px;background:#ff5e6c;top:-60px;right:-60px;"></div>
    <div class="orb" style="width:200px;height:200px;background:#a855f7;bottom:100px;left:-60px;"></div>

    <div class="min-h-screen w-full overflow-auto pb-28">
        <div class="relative z-10 max-w-md mx-auto px-5">

            {{-- ═══════════════════════════════ --}}
            {{-- HEADER --}}
            {{-- ═══════════════════════════════ --}}
            <div class="flex items-center justify-between safe-top pb-4 fade-up">
                <a href="{{ route('swipe') }}" class="p-2 -ml-2 rounded-xl hover:bg-white/5 active:scale-95 transition">
                    <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="flex items-center gap-1.5">
                    <span class="text-base">🔥</span>
                    <span class="text-sm font-bold cc-gradient-text">Mon Profil</span>
                </div>
                <a href="{{ route('settings') }}" class="p-2 -mr-2 rounded-xl hover:bg-white/5 active:scale-95 transition">
                    <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </a>
            </div>

            {{-- ═══════════════════════════════ --}}
            {{-- PHOTO + NAME --}}
            {{-- ═══════════════════════════════ --}}
            <div class="flex flex-col items-center mb-7 fade-up d1">
                <div class="photo-ring mb-4">
                    <div class="w-full h-full rounded-full overflow-hidden">
                        <img src="{{ $profile->photo_url }}" class="w-full h-full object-cover" alt="{{ e($user->name) }}">
                    </div>
                </div>
                <h1 class="text-2xl font-bold leading-tight">
                    {{ e($user->name) }}<span class="text-white/40 font-normal ml-1.5 text-lg">{{ $profile->age }}</span>
                </h1>
                <p class="text-white/35 text-xs mt-1 flex items-center gap-1.5">
                    <span>🎓</span>
                    {{ $profile->university_name ?? $profile->university ?? 'UGB' }}
                    <span class="text-white/15">·</span>
                    {{ $profile->gender === 'homme' ? '♂ Homme' : '♀ Femme' }}
                </p>
            </div>

            {{-- ═══════════════════════════════ --}}
            {{-- BADGES --}}
            {{-- ═══════════════════════════════ --}}
            <div class="flex flex-wrap justify-center gap-2 mb-6 fade-up d2">
                <span class="px-3.5 py-1.5 rounded-full text-[11px] font-medium" style="background: rgba(255,94,108,0.08); border: 1px solid rgba(255,94,108,0.15); color: #ff8a8a;">
                    📚 {{ $profile->ufr ?? 'N/A' }}
                </span>
                <span class="px-3.5 py-1.5 rounded-full text-[11px] font-medium" style="background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.15); color: #c084fc;">
                    🎓 {{ $profile->level }}
                </span>
                @if($profile->promotion)
                <span class="px-3.5 py-1.5 rounded-full text-[11px] font-medium" style="background: rgba(255,193,69,0.08); border: 1px solid rgba(255,193,69,0.15); color: #ffc145;">
                    📋 {{ $profile->promotion }}
                </span>
                @endif
            </div>

            {{-- ═══════════════════════════════ --}}
            {{-- STATS --}}
            {{-- ═══════════════════════════════ --}}
            <div class="grid grid-cols-3 gap-2.5 mb-6 fade-up d2">
                <div class="stat-card cc-surface-raised rounded-2xl p-3.5 text-center">
                    <p class="text-xl font-bold cc-mono cc-gradient-text count-up" style="animation-delay:0.3s">{{ $matchesCount }}</p>
                    <p class="text-[9px] text-white/25 mt-1 uppercase tracking-widest">Matchs</p>
                </div>
                <div class="stat-card cc-surface-raised rounded-2xl p-3.5 text-center">
                    <p class="text-xl font-bold cc-mono count-up" style="background: linear-gradient(135deg, #a855f7, #6c5ce7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; animation-delay:0.4s">{{ $likesCount }}</p>
                    <p class="text-[9px] text-white/25 mt-1 uppercase tracking-widest">Likes reçus</p>
                </div>
                <div class="stat-card cc-surface-raised rounded-2xl p-3.5 text-center">
                    <p class="text-xl count-up" style="animation-delay:0.5s">{{ $profile->gender === 'homme' ? '♂️' : '♀️' }}</p>
                    <p class="text-[9px] text-white/25 mt-1 uppercase tracking-widest">{{ ucfirst($profile->gender) }}</p>
                </div>
            </div>

            {{-- ═══════════════════════════════ --}}
            {{-- BIO --}}
            {{-- ═══════════════════════════════ --}}
            <div class="cc-surface rounded-2xl p-5 mb-3 fade-up d3">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-[10px] text-white/25 uppercase tracking-widest font-medium">À propos</h2>
                    <a href="{{ route('profile.edit') }}" class="text-[10px] text-[#ff5e6c]/60 hover:text-[#ff5e6c] transition">Modifier</a>
                </div>
                <p class="text-[13px] text-white/50 leading-relaxed">{{ e($profile->bio ?? 'Aucune bio pour le moment. Ajoute une bio pour te démarquer !') }}</p>
            </div>

            {{-- ═══════════════════════════════ --}}
            {{-- INTERESTS --}}
            {{-- ═══════════════════════════════ --}}
            @if($profile->interests)
            <div class="cc-surface rounded-2xl p-5 mb-6 fade-up d3">
                <h2 class="text-[10px] text-white/25 uppercase tracking-widest font-medium mb-3">Centres d'intérêt</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($profile->interests_array as $interest)
                    <span class="px-3 py-1.5 rounded-full text-[11px] text-white/45 border border-white/8 transition hover:border-white/15 hover:text-white/60" style="background: rgba(255,255,255,0.03);">
                        {{ e($interest) }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ═══════════════════════════════ --}}
            {{-- QUICK LINKS --}}
            {{-- ═══════════════════════════════ --}}
            <div class="space-y-2 mb-6 fade-up d4">
                <a href="{{ route('matches') }}" class="cc-surface rounded-2xl p-4 flex items-center gap-3.5 hover:bg-white/[0.06] active:scale-[0.98] transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,94,108,0.08); border: 1px solid rgba(255,94,108,0.1);">
                        <span class="text-lg">💕</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium">Mes matchs</h3>
                        <p class="text-[11px] text-white/25">{{ $matchesCount }} conversation{{ $matchesCount > 1 ? 's' : '' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('ai.index') }}" class="cc-surface rounded-2xl p-4 flex items-center gap-3.5 hover:bg-white/[0.06] active:scale-[0.98] transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.1);">
                        <span class="text-lg">🤖</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium">IA Campus Crush</h3>
                        <p class="text-[11px] text-white/25">Coach, match IA, entraînement</p>
                    </div>
                    <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('settings') }}" class="cc-surface rounded-2xl p-4 flex items-center gap-3.5 hover:bg-white/[0.06] active:scale-[0.98] transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.1);">
                        <span class="text-lg">⚙️</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium">Paramètres</h3>
                        <p class="text-[11px] text-white/25">Notifications, confidentialité</p>
                    </div>
                    <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="/safety" class="cc-surface rounded-2xl p-4 flex items-center gap-3.5 hover:bg-white/[0.06] active:scale-[0.98] transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.1);">
                        <span class="text-lg">🛡️</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium">Conseils de sécurité</h3>
                        <p class="text-[11px] text-white/25">Protège-toi lors des rencontres</p>
                    </div>
                    <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('crush.index') }}" class="cc-surface rounded-2xl p-4 flex items-center gap-3.5 hover:bg-white/[0.06] active:scale-[0.98] transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.1);">
                        <span class="text-lg">👀</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium">Crush anonyme</h3>
                        <p class="text-[11px] text-white/25">Envoie un crush sans te dévoiler</p>
                    </div>
                    <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- ═══════════════════════════════ --}}
            {{-- STREAK --}}
            {{-- ═══════════════════════════════ --}}
            @if(($user->streak_days ?? 0) > 0)
            <div class="fade-up d4 mb-3">
                <div class="flex items-center justify-between px-4 py-3 rounded-2xl" style="background:rgba(255,193,69,0.08); border:1px solid rgba(255,193,69,0.18);">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xl">{{ $user->streak_badge ?: '🔥' }}</span>
                        <div>
                            <p class="text-sm font-bold text-white">{{ $user->streak_days }} jour{{ $user->streak_days > 1 ? 's' : '' }} de suite</p>
                            <p class="text-[10px]" style="color:rgba(255,255,255,0.35);">
                                @if($user->streak_days >= 100) Légendaire 🏆
                                @elseif($user->streak_days >= 30) Habitué ⚡
                                @elseif($user->streak_days >= 7) En feu 🔥
                                @else Continue comme ça !
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold" style="color:#ffc145;">Streak actif</p>
                        @if($user->streak_days == 6)
                        <p class="text-[10px]" style="color:rgba(255,255,255,0.30);">+3j demain 🎁</p>
                        @elseif($user->streak_days == 29)
                        <p class="text-[10px]" style="color:rgba(255,255,255,0.30);">+7j demain 🎁</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- ═══════════════════════════════ --}}
            {{-- PARTAGE VIRAL --}}
            {{-- ═══════════════════════════════ --}}
            <div class="fade-up d4 mb-3">
                <p class="text-xs font-semibold mb-2" style="color:rgba(255,255,255,0.30); letter-spacing:0.06em;">PARTAGER MON PROFIL</p>
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="shareWhatsApp()" class="flex items-center justify-center gap-2 py-3 rounded-xl font-semibold text-sm transition active:scale-95" style="background:rgba(37,211,102,0.10);border:1px solid rgba(37,211,102,0.22);color:#25d366;">
                        <span>💬</span> WhatsApp
                    </button>
                    <button onclick="shareNative()" class="flex items-center justify-center gap-2 py-3 rounded-xl font-semibold text-sm transition active:scale-95" style="background:rgba(168,85,247,0.08);border:1px solid rgba(168,85,247,0.18);color:#a855f7;">
                        <span>📤</span> Partager
                    </button>
                </div>
            </div>

            @include('components.review-form')

            {{-- ═══════════════════════════════ --}}
            {{-- ACTIONS --}}
            {{-- ═══════════════════════════════ --}}
            <div class="space-y-3 fade-up d5">
                <a href="{{ route('profile.edit') }}" class="block w-full py-3.5 rounded-2xl text-center font-semibold text-white text-sm active:scale-[0.98] transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.2);">
                    ✏️ Modifier mon profil
                </a>

                {{-- Boost --}}
                <a href="{{ route('boost.index') }}" class="block w-full py-3.5 rounded-2xl text-center font-semibold text-sm active:scale-[0.98] transition mt-3"
                    style="{{ $profile->isBoosted()
            ? 'background:rgba(255,193,69,0.10); border:1px solid rgba(255,193,69,0.25); color:#ffc145;'
            : 'background:rgba(255,193,69,0.08); border:1px solid rgba(255,193,69,0.15); color:#ffc145;' }}">
                    @if($profile->isBoosted())
                    🚀 Boosté — actif jusqu'à {{ $profile->boosted_until->format('H:i') }}
                    @else
                    🚀 Booster mon profil — 500 FCFA / 24h
                    @endif
                </a>

                {{-- Parrainage --}}
                <a href="{{ route('referral.index') }}" class="block w-full py-3.5 rounded-2xl text-center font-semibold text-sm active:scale-[0.98] transition"
                    style="background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.18); color:#a855f7;">
                    🎁 Parrainer un(e) ami(e) — gagne 7 jours
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-3.5 rounded-2xl text-center text-sm font-medium text-white/25 cc-surface hover:bg-white/5 hover:text-red-400/60 active:scale-[0.98] transition">
                        Déconnexion
                    </button>
                </form>
            </div>

        </div>
    </div>
    <script>
        const publicUrl = '{{ route("public.profile", auth()->user()->slug ?? "profil") }}';
        const profileName = '{{ e($user->name) }}';
        const appName = 'Campus Crush';

        function shareWhatsApp() {
            const msg = encodeURIComponent(
                '👋 Salut ! Voici mon profil sur Campus Crush, l\'appli de rencontres pour étudiants 🎓💘\n' +
                'Rejoins-moi : ' + publicUrl
            );
            window.open('https://wa.me/?text=' + msg, '_blank');
        }

        function shareNative() {
            if (navigator.share) {
                navigator.share({
                    title: profileName + ' sur Campus Crush',
                    text: '👋 ' + profileName + ' est sur Campus Crush ! Rejoins l\'appli de rencontres étudiantes 🎓💘',
                    url: publicUrl,
                });
            } else {
                navigator.clipboard?.writeText(publicUrl)
                    .then(() => alert('Lien copié !'))
                    .catch(() => {});
            }
        }

        // 🎉 Afficher une notification si streak reward débloqué
        @if(session('streak_reward_7'))
        showStreakToast('🔥 7 jours de suite ! +3 jours de premium offerts 🎁');
        @elseif(session('streak_reward_30'))
        showStreakToast('⚡ 30 jours de suite ! +7 jours de premium offerts 🎁');
        @elseif(session('streak_reward_100'))
        showStreakToast('🏆 100 jours de suite ! +30 jours de premium offerts 🎁');
        @endif

        function showStreakToast(msg) {
            const t = document.createElement('div');
            t.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:99999;padding:14px 24px;border-radius:16px;font-size:13px;font-weight:600;color:#fff;text-align:center;max-width:320px;background:linear-gradient(135deg,rgba(255,193,69,0.95),rgba(255,138,92,0.95));border:1px solid rgba(255,255,255,0.15);backdrop-filter:blur(20px);font-family:Sora,sans-serif;box-shadow:0 8px 30px rgba(255,193,69,0.3);';
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => {
                t.style.transition = 'opacity 0.5s';
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 500);
            }, 4000);
        }
    </script>
    @include('components.feature-reminders')
    @include('components.bottom-nav')
     @auth
    @include('components.ai-chat-fab')
    @endauth
</body>

</html>