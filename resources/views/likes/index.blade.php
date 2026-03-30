<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Campus Crush - Qui t'a liké</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); min-height: 100vh; }
        .cc-surface { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(24px); }
        .cc-gradient-text { background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cc-mono { font-family: 'Space Mono', monospace; }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; opacity: 0.1; }

        @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }
        .fade-up { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; }

        .liker-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .liker-card:active { transform: scale(0.97); }

        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        .heart-beat { animation: heartBeat 1.5s ease-in-out infinite; }

        .safe-top { padding-top: max(env(safe-area-inset-top, 12px), 12px); }
    </style>
</head>
<body class="text-white">

    <div class="orb" style="width:250px;height:250px;background:#ff5e6c;top:-60px;right:-60px;"></div>
    <div class="orb" style="width:200px;height:200px;background:#a855f7;bottom:100px;left:-60px;"></div>

    <div class="min-h-screen w-full pb-28">
        <div class="relative z-10 max-w-md mx-auto px-5">

            {{-- Header --}}
            <div class="flex items-center justify-between safe-top pb-4 fade-up">
                <a href="{{ route('swipe') }}" class="p-2 -ml-2 rounded-xl hover:bg-white/5 active:scale-95 transition">
                    <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex items-center gap-1.5">
                    <span class="text-base">💕</span>
                    <span class="text-sm font-bold cc-gradient-text">Qui t'a liké</span>
                </div>
                <div class="w-9"></div>
            </div>

            {{-- Stats --}}
            <div class="cc-surface rounded-2xl p-5 mb-6 text-center fade-up" style="animation-delay:0.08s">
                <div class="flex items-center justify-center gap-3 mb-2">
                    <span class="text-3xl heart-beat">💜</span>
                </div>
                <p class="text-2xl font-bold cc-mono cc-gradient-text">{{ $totalLikes }}</p>
                <p class="text-[11px] text-white/30 mt-1">personne{{ $totalLikes > 1 ? 's' : '' }} t'{{ $totalLikes > 1 ? 'ont' : 'a' }} liké au total</p>
            </div>

            @if($likers->count() > 0)
            {{-- Pending likes header --}}
            <div class="flex items-center justify-between mb-4 fade-up" style="animation-delay:0.12s">
                <p class="text-[10px] text-white/25 uppercase tracking-widest font-medium">En attente de ta réponse</p>
                <span class="text-[11px] text-white/30 cc-mono">{{ $likers->count() }}</span>
            </div>

            {{-- Grid of likers --}}
            <div class="grid grid-cols-2 gap-3">
                @foreach($likers as $i => $liker)
                <div class="liker-card cc-surface rounded-2xl overflow-hidden fade-up" style="animation-delay: {{ 0.15 + $i * 0.05 }}s">
                    {{-- Photo --}}
                    <div class="relative aspect-[3/4]">
                        <img src="{{ $liker['photo'] }}" class="w-full h-full object-cover" alt="{{ e($liker['name']) }}" loading="lazy">
                        <div class="absolute inset-0" style="background: linear-gradient(0deg, rgba(12,10,26,0.9) 0%, rgba(12,10,26,0.3) 40%, transparent 70%);"></div>

                        {{-- Info --}}
                        <div class="absolute bottom-0 left-0 right-0 p-3">
                            <h3 class="font-semibold text-sm leading-tight">{{ e($liker['name']) }}<span class="text-white/50 font-normal ml-1">{{ $liker['age'] }}</span></h3>
                            <p class="text-[10px] text-white/40 mt-0.5">🎓 {{ e($liker['university']) }}</p>
                            <p class="text-[9px] text-white/20 mt-0.5 cc-mono">{{ $liker['liked_at'] }}</p>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex gap-2 p-2.5">
                        <button onclick="passUser({{ $liker['id'] }}, this)" class="flex-1 py-2 rounded-xl text-xs font-medium text-white/40 border border-white/8 hover:bg-white/5 active:scale-95 transition">
                            ✕
                        </button>
                        <button onclick="likeUser({{ $liker['id'] }}, '{{ e($liker['name']) }}', this)" class="flex-1 py-2 rounded-xl text-xs font-semibold text-white active:scale-95 transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                            💚 Like
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center py-16 text-center fade-up" style="animation-delay:0.15s">
                <div class="w-20 h-20 rounded-3xl flex items-center justify-center mb-5" style="background: rgba(255,94,108,0.08); border: 1px solid rgba(255,94,108,0.1);">
                    <span class="text-3xl">💫</span>
                </div>
                <h3 class="font-semibold text-base mb-1.5 text-white/70">Pas de likes en attente</h3>
                <p class="text-xs text-white/30 max-w-[240px] leading-relaxed">Tous les profils qui t'ont liké ont déjà été traités. Continue à swiper pour trouver ton crush !</p>
                <a href="{{ route('swipe') }}" class="mt-6 px-6 py-3 rounded-2xl text-sm font-semibold text-white active:scale-95 transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                    Découvrir des profils
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Match popup --}}
    <div id="match-popup" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6" style="background:rgba(12,10,26,0.92); backdrop-filter:blur(20px);">
        <div class="text-center max-w-xs w-full" style="animation: matchPop 0.5s cubic-bezier(0.22,1,0.36,1) both;">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-3xl font-extrabold cc-gradient-text mb-2">C'est un Match !</h2>
            <p class="text-white/40 text-sm mb-8">Toi et <span id="match-name" class="text-white/70 font-medium">...</span> vous êtes likés</p>
            <div class="flex flex-col gap-3">
                <button id="send-msg-btn" class="w-full py-3.5 rounded-2xl font-semibold text-white text-sm" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                    Envoyer un message 💬
                </button>
                <button onclick="document.getElementById('match-popup').classList.add('hidden')" class="w-full py-3.5 rounded-2xl font-medium text-white/40 text-sm border border-white/10 hover:bg-white/5 transition">
                    Continuer
                </button>
            </div>
        </div>
    </div>

    @include('components.bottom-nav')

    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    async function likeUser(userId, name, btn) {
        const card = btn.closest('.liker-card');
        card.style.opacity = '0.5';
        card.style.pointerEvents = 'none';

        try {
            const res = await fetch('/like/' + userId, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.match) {
                document.getElementById('match-name').textContent = name;
                document.getElementById('send-msg-btn').onclick = () => {
                    window.location.href = '/messages/' + data.match_id;
                };
                document.getElementById('match-popup').classList.remove('hidden');
            }

            card.style.transition = 'all 0.3s ease';
            card.style.transform = 'scale(0.8)';
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 300);

        } catch(e) {
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
            console.error('Like error:', e);
        }
    }

    async function passUser(userId, btn) {
        const card = btn.closest('.liker-card');
        card.style.opacity = '0.5';
        card.style.pointerEvents = 'none';

        try {
            await fetch('/pass/' + userId, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });

            card.style.transition = 'all 0.3s ease';
            card.style.transform = 'scale(0.8)';
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 300);

        } catch(e) {
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
        }
    }
    </script>

    <style>
    @keyframes matchPop {
        0% { transform: scale(0.3); opacity: 0; }
        50% { transform: scale(1.08); }
        100% { transform: scale(1); opacity: 1; }
    }
    </style>
</body>
</html>
