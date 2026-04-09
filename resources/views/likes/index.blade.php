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
            <div class="flex flex-col items-center justify-center py-12 text-center fade-up" style="animation-delay:0.15s">
                <div class="w-24 h-24 rounded-3xl flex items-center justify-center mb-6 empty-float"
                     style="background: linear-gradient(135deg, rgba(255,94,108,0.10), rgba(168,85,247,0.10)); border: 1px solid rgba(255,94,108,0.12);">
                    <span class="text-5xl">💫</span>
                </div>
                <h3 class="text-xl font-bold text-white/80 mb-2">Plus de likes en attente !</h3>
                <p class="text-sm text-white/30 max-w-[260px] leading-relaxed mb-8">
                    Tous les profils ont été traités.<br>Continue à swiper pour décrocher ton crush !
                </p>
                <a href="{{ route('swipe') }}"
                   class="px-8 py-3.5 rounded-2xl text-sm font-semibold text-white active:scale-95 transition"
                   style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.25);">
                    🔥 Découvrir des profils
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Match popup — style swipe ──────────────────────────────── --}}
    <div id="match-popup" class="hidden fixed inset-0 z-50 flex flex-col items-center justify-center"
         style="background: rgba(10,8,22,0.96); backdrop-filter: blur(24px); animation: matchReveal 0.3s ease both;">

        {{-- Confettis --}}
        <div id="confetti-container" class="fixed inset-0 pointer-events-none overflow-hidden z-[9998]"></div>

        <div class="flex flex-col items-center px-8 w-full max-w-sm">
            {{-- Photos côte à côte --}}
            <div class="relative flex items-center justify-center mb-8" style="height:160px; width:100%;">
                {{-- Ma photo --}}
                <div class="match-photo-left absolute w-28 h-28 rounded-3xl overflow-hidden"
                     style="left:50%; margin-left:-90px; transform:rotate(-4deg);
                            box-shadow:-8px 8px 30px rgba(0,0,0,0.5), 0 0 0 3px rgba(255,94,108,0.4);">
                    <img src="{{ auth()->user()->profile->photo_url }}" class="w-full h-full object-cover" alt="Moi">
                </div>
                {{-- Cœur --}}
                <div class="match-heart absolute w-12 h-12 rounded-2xl flex items-center justify-center z-10"
                     style="left:50%; top:50%; transform:translate(-50%,-50%);
                            background:linear-gradient(135deg,#ff5e6c,#ff8a5c);
                            box-shadow:0 4px 20px rgba(255,94,108,0.6);">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                {{-- Photo du match --}}
                <div class="match-photo-right absolute w-28 h-28 rounded-3xl overflow-hidden"
                     style="left:50%; margin-left:18px; transform:rotate(4deg);
                            box-shadow:8px 8px 30px rgba(0,0,0,0.5), 0 0 0 3px rgba(255,193,69,0.4);">
                    <img id="match-photo" src="" class="w-full h-full object-cover" alt="Match">
                </div>
            </div>

            {{-- Texte --}}
            <div class="match-title text-center mb-8">
                <div class="text-xs font-semibold tracking-widest text-white/30 uppercase mb-2">Nouveau match 🎉</div>
                <h2 class="text-4xl font-extrabold mb-2"
                    style="background:linear-gradient(135deg,#ff5e6c,#ffc145);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                    C'est un Match !
                </h2>
                <p class="text-white/40 text-sm">
                    Toi et <span id="match-name" class="text-white/80 font-semibold">...</span> vous êtes likés
                </p>
            </div>

            {{-- Boutons --}}
            <div class="match-btns flex flex-col gap-3 w-full">
                <button id="send-msg-btn"
                    class="w-full py-4 rounded-2xl font-semibold text-white text-[15px] transition active:scale-95"
                    style="background:linear-gradient(135deg,#ff5e6c,#ff8a5c); box-shadow:0 8px 30px rgba(255,94,108,0.35);">
                    Envoyer un message 💬
                </button>
                <button onclick="document.getElementById('match-popup').classList.add('hidden')"
                    class="w-full py-3.5 rounded-2xl font-medium text-white/35 text-sm border transition active:scale-95 hover:bg-white/5"
                    style="border-color:rgba(255,255,255,0.08);">
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
                // Photo du match depuis la carte
                const card = btn.closest('.liker-card');
                const img  = card ? card.querySelector('img') : null;
                if (img) document.getElementById('match-photo').src = img.src;

                document.getElementById('send-msg-btn').onclick = () => {
                    window.location.href = '/messages/' + data.match_id;
                };
                if (navigator.vibrate) navigator.vibrate([30, 40, 80]);
                setTimeout(() => {
                    document.getElementById('match-popup').classList.remove('hidden');
                    launchConfetti();
                }, 300);
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

    function launchConfetti() {
        const container = document.getElementById('confetti-container');
        if (!container) return;
        container.innerHTML = '';
        const colors = ['#ff5e6c', '#ffc145', '#a855f7', '#3b82f6', '#10b981', '#fff'];
        for (let i = 0; i < 55; i++) {
            const el = document.createElement('div');
            el.className = 'confetti-piece';
            const size  = Math.random() * 8 + 5;
            const color = colors[Math.floor(Math.random() * colors.length)];
            const left  = Math.random() * 100;
            const delay = Math.random() * 0.8;
            const dur   = Math.random() * 1.5 + 1.8;
            const isCirc = Math.random() > 0.5;
            el.style.cssText = `width:${size}px;height:${size}px;background:${color};left:${left}vw;border-radius:${isCirc?'50%':'2px'};animation-duration:${dur}s;animation-delay:${delay}s;opacity:0.9;`;
            container.appendChild(el);
        }
        setTimeout(() => { container.innerHTML = ''; }, 4000);
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

    /* Empty float */
    .empty-float { animation: emptyFloat 4s ease-in-out infinite; }
    @keyframes emptyFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-12px); }
    }

    /* Match popup animations */
    @keyframes matchReveal { from { opacity:0; } to { opacity:1; } }

    @keyframes photoSlideLeft {
        0%   { opacity:0; transform:translateX(-40px) scale(0.85) rotate(-4deg); }
        100% { opacity:1; transform:translateX(0) scale(1) rotate(-4deg); }
    }
    @keyframes photoSlideRight {
        0%   { opacity:0; transform:translateX(40px) scale(0.85) rotate(4deg); }
        100% { opacity:1; transform:translateX(0) scale(1) rotate(4deg); }
    }
    @keyframes heartBounce {
        0%   { opacity:0; transform:translate(-50%,-50%) scale(0); }
        60%  { transform:translate(-50%,-50%) scale(1.3); }
        80%  { transform:translate(-50%,-50%) scale(0.9); }
        100% { opacity:1; transform:translate(-50%,-50%) scale(1); }
    }
    @keyframes titleSlide {
        0%   { opacity:0; transform:translateY(20px); }
        100% { opacity:1; transform:translateY(0); }
    }
    @keyframes confettiDrop {
        0%   { transform:translateY(-20px) rotate(0deg); opacity:1; }
        100% { transform:translateY(100vh) rotate(720deg); opacity:0; }
    }
    .match-photo-left  { animation: photoSlideLeft  0.55s cubic-bezier(0.22,1,0.36,1) 0.2s  both; }
    .match-photo-right { animation: photoSlideRight 0.55s cubic-bezier(0.22,1,0.36,1) 0.2s  both; }
    .match-heart       { animation: heartBounce     0.6s  cubic-bezier(0.22,1,0.36,1) 0.5s  both; }
    .match-title       { animation: titleSlide      0.5s  cubic-bezier(0.22,1,0.36,1) 0.65s both; }
    .match-btns        { animation: titleSlide      0.5s  cubic-bezier(0.22,1,0.36,1) 0.8s  both; }
    .confetti-piece    {
        position:fixed; top:-10px; width:8px; height:8px; border-radius:2px;
        pointer-events:none; z-index:9999; animation:confettiDrop linear both;
    }
    </style>

    <script>
    // ── Pull-to-refresh ───────────────────────────────────────────────
    // Sur cette page, window est le scroll container (body min-h-screen)
    (function () {
        let startY = 0, pulling = false, pullDist = 0, releasing = false;
        const THRESHOLD = 52;
        const getScrollTop = () => window.pageYOffset || document.documentElement.scrollTop || 0;

        const style = document.createElement('style');
        style.textContent = `
            @keyframes _ptr_spin { to { transform: rotate(360deg); } }
            ._ptr_spinning { animation: _ptr_spin 0.7s linear infinite !important; }
        `;
        document.head.appendChild(style);

        const wrap = document.createElement('div');
        wrap.style.cssText = [
            'position:fixed', 'top:0', 'left:0', 'right:0', 'z-index:9999',
            'display:flex', 'align-items:center', 'justify-content:center',
            'height:0', 'overflow:hidden', 'pointer-events:none',
            'background:rgba(255,94,108,0.09)',
            'backdrop-filter:blur(14px)',
            'border-bottom:1px solid rgba(255,94,108,0.15)',
        ].join(';');
        wrap.innerHTML = `
            <div id="_ptr_inner" style="display:flex;align-items:center;gap:10px;opacity:0;">
                <span id="_ptr_icon" style="font-size:18px;display:inline-block;transition:transform 0.15s ease;">↓</span>
                <span id="_ptr_txt" style="font-size:12px;color:rgba(255,255,255,0.55);font-family:'Sora',sans-serif;font-weight:600;letter-spacing:0.01em;">Tirer pour rafraîchir</span>
            </div>`;
        document.body.appendChild(wrap);

        const inner = wrap.querySelector('#_ptr_inner');
        const icon  = wrap.querySelector('#_ptr_icon');
        const txt   = wrap.querySelector('#_ptr_txt');

        function setHeight(h, animated) {
            wrap.style.transition = animated ? 'height 0.22s cubic-bezier(0.22,1,0.36,1)' : 'none';
            wrap.style.height = h + 'px';
        }

        document.addEventListener('touchstart', e => {
            if (releasing || getScrollTop() > 0) return;
            startY   = e.touches[0].clientY;
            pulling  = true;
            pullDist = 0;
        }, { passive: true });

        document.addEventListener('touchmove', e => {
            if (!pulling || releasing) return;
            const dist = e.touches[0].clientY - startY;
            if (dist <= 0) { pullDist = 0; setHeight(0, false); inner.style.opacity = '0'; return; }
            pullDist = dist;
            e.preventDefault();

            const h = Math.min(Math.sqrt(pullDist) * 6.5, 70);
            setHeight(h, false);
            inner.style.opacity = Math.min(pullDist / (THRESHOLD * 0.6), 1);

            const ready = pullDist >= THRESHOLD;
            icon.style.transform = ready ? 'rotate(180deg)' : 'rotate(0deg)';
            icon.textContent = ready ? '↑' : '↓';
            txt.textContent  = ready ? 'Relâcher pour rafraîchir' : 'Tirer pour rafraîchir';
            txt.style.color  = ready ? 'rgba(255,94,108,0.9)' : 'rgba(255,255,255,0.55)';
        }, { passive: false });

        document.addEventListener('touchend', () => {
            if (!pulling) return;
            pulling   = false;
            releasing = true;

            if (pullDist >= THRESHOLD) {
                icon.textContent = '↻';
                icon.classList.add('_ptr_spinning');
                txt.textContent  = 'Chargement...';
                txt.style.color  = 'rgba(255,255,255,0.6)';
                inner.style.opacity = '1';
                setHeight(52, true);
                setTimeout(() => window.location.reload(), 600);
            } else {
                inner.style.opacity = '0';
                setHeight(0, true);
                setTimeout(() => { releasing = false; }, 250);
            }
            pullDist = 0;
        }, { passive: true });
    })();
    </script>
    @include('components.feature-reminders')
     @auth
    @include('components.ai-chat-fab')
    @endauth
</body>
</html>