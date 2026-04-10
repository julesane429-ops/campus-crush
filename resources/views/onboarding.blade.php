<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0c0a1a">
    <title>Campus Crush - Bienvenue</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); }
        .cc-gradient-text {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .float-anim { animation: float 3s ease-in-out infinite; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:none; } }
        .fade-up { animation: fadeUp 0.6s ease both; }
        .slide { display: none; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; }
        .slide.active { display: flex; }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.15); transition: all 0.3s; }
        .dot.active { width: 24px; border-radius: 4px; background: linear-gradient(135deg, #ff5e6c, #ff8a5c); }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; opacity: 0.15; }
        .orb-1 { width: 300px; height: 300px; background: #ff5e6c; top: -100px; right: -100px; }
        .orb-2 { width: 250px; height: 250px; background: #a855f7; bottom: -80px; left: -80px; }
    </style>
</head>
<body class="min-h-screen text-white overflow-hidden">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="relative z-10 w-full max-w-md mx-auto min-h-screen flex flex-col" style="min-height: 100dvh;">

        {{-- Top bar : Home + Skip --}}
        <div class="flex items-center justify-between px-4 pt-4 pb-2" style="padding-top: max(env(safe-area-inset-top, 12px), 12px);">
            <a href="{{ route('home') }}" class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-[11px] text-white/30 hover:text-white/50 hover:bg-white/5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Accueil
            </a>
            <a href="{{ route('register') }}" class="text-[11px] text-white/30 hover:text-white/60 transition px-3 py-2">Passer →</a>
        </div>

        {{-- Slides --}}
        <div id="slides-container" class="flex-1 flex items-center">

            {{-- Slide 1 --}}
            <div class="slide active fade-up" data-slide="0">
                <div class="text-6xl sm:text-7xl mb-5 float-anim">🔥</div>
                <h1 class="text-2xl sm:text-3xl font-extrabold cc-gradient-text mb-2">Campus Crush</h1>
                <p class="text-white/40 text-sm leading-relaxed max-w-[280px]">
                    L'app de rencontres faite pour les étudiants sénégalais. Trouve ton crush sur le campus !
                </p>
            </div>

            {{-- Slide 2 --}}
            <div class="slide fade-up" data-slide="1">
                <div class="text-6xl sm:text-7xl mb-5 float-anim" style="animation-delay: 0.3s">💕</div>
                <h2 class="text-xl sm:text-2xl font-bold mb-2">Swipe & Match</h2>
                <p class="text-white/40 text-sm leading-relaxed max-w-[280px]">
                    Swipe à droite si tu kiffes, à gauche si c'est pas ton style. Quand c'est réciproque, c'est un match !
                </p>
            </div>

            {{-- Slide 3 --}}
            <div class="slide fade-up" data-slide="2">
                <div class="text-6xl sm:text-7xl mb-5 float-anim" style="animation-delay: 0.6s">🏫</div>
                <h2 class="text-xl sm:text-2xl font-bold mb-2">11 Universités</h2>
                <p class="text-white/40 text-sm leading-relaxed max-w-[280px]">
                    UGB, UCAD, UADB, UASZ et plus encore. Filtre par université, UFR ou promotion.
                </p>
            </div>

            {{-- Slide 4 --}}
            <div class="slide fade-up" data-slide="3">
                <div class="text-6xl sm:text-7xl mb-5 float-anim" style="animation-delay: 0.9s">🎁</div>
                <h2 class="text-xl sm:text-2xl font-bold mb-2">1er mois gratuit</h2>
                <p class="text-white/40 text-sm leading-relaxed max-w-[280px]">
                    Profite de 30 jours d'essai gratuit. Ensuite c'est seulement 1 000 FCFA/mois.
                </p>
            </div>
        </div>

        {{-- Bottom --}}
        <div class="px-5 pb-6" style="padding-bottom: max(env(safe-area-inset-bottom, 24px), 24px);">
            {{-- Dots --}}
            <div class="flex items-center justify-center gap-2 mb-6">
                <span class="dot active" data-dot="0"></span>
                <span class="dot" data-dot="1"></span>
                <span class="dot" data-dot="2"></span>
                <span class="dot" data-dot="3"></span>
            </div>

            {{-- Next --}}
            <div id="btn-next-wrap">
                <button id="btn-next" class="w-full py-3.5 rounded-2xl font-semibold text-white text-base transition active:scale-[0.98]"
                        style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                    Suivant
                </button>
            </div>

            {{-- Final buttons --}}
            <div id="btn-start-wrap" class="hidden space-y-2.5">
                <a href="{{ route('register') }}" class="block w-full py-3.5 rounded-2xl font-semibold text-white text-base text-center transition active:scale-[0.98]"
                   style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                    Créer mon compte 🚀
                </a>
                <a href="{{ route('login') }}" class="block w-full py-3 rounded-2xl font-medium text-white/50 text-sm text-center border border-white/10 hover:bg-white/5 transition">
                    J'ai déjà un compte
                </a>
                <a href="{{ route('home') }}" class="block w-full py-2.5 text-center text-xs text-white/25 hover:text-white/40 transition">
                    ← Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

    <script>
    (function() {
        let current = 0;
        const total = 4;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const btnNext = document.getElementById('btn-next');
        const btnNextWrap = document.getElementById('btn-next-wrap');
        const btnStartWrap = document.getElementById('btn-start-wrap');

        function goTo(index) {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            current = index;

            if (current === total - 1) {
                btnNextWrap.classList.add('hidden');
                btnStartWrap.classList.remove('hidden');
            } else {
                btnNextWrap.classList.remove('hidden');
                btnStartWrap.classList.add('hidden');
            }
        }

        btnNext.addEventListener('click', () => { if (current < total - 1) goTo(current + 1); });

        let startX = 0;
        const container = document.getElementById('slides-container');
        container.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
        container.addEventListener('touchend', (e) => {
            const diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                if (diff > 0 && current < total - 1) goTo(current + 1);
                if (diff < 0 && current > 0) goTo(current - 1);
            }
        }, { passive: true });

        dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));
        localStorage.setItem('cc_onboarding_seen', '1');
    })();
    </script>
</body>
</html>
