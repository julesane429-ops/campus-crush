<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#070512">
    <title>Campus Crush - Bienvenue</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@200;300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ease-apple: cubic-bezier(0.22, 1, 0.36, 1);
        }
        * { font-family: 'Sora', -apple-system, sans-serif; box-sizing: border-box; -webkit-font-smoothing: antialiased; }
        html, body { overscroll-behavior: none; }
        body {
            background: #050310;
            min-height: 100dvh;
            overflow: hidden;
        }
        .hero-bg {
            background:
                radial-gradient(ellipse at top right, rgba(255,94,108,0.22) 0%, transparent 60%),
                radial-gradient(ellipse at bottom left, rgba(168,85,247,0.18) 0%, transparent 60%),
                linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
        }
        .display { font-weight: 800; letter-spacing: -0.035em; line-height: 1.02; }
        .eyebrow {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            color: rgba(255,255,255,0.35);
        }
        .cc-gradient-text {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        /* ── Slides + parallax ── */
        .slides-track {
            display: flex;
            width: 400%;
            height: 100%;
            transition: transform 0.75s var(--ease-apple);
            will-change: transform;
        }
        .slide {
            width: 25%;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }

        /* ── Parallax emoji wrapper ── */
        .emoji-stage {
            position: relative;
            width: 200px; height: 200px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 2rem;
        }
        .emoji-glow {
            position: absolute;
            inset: -40px;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.5;
            transition: opacity 0.6s var(--ease-apple);
        }
        .emoji-main {
            position: relative;
            font-size: 96px;
            line-height: 1;
            animation: float 4s ease-in-out infinite;
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.5));
        }
        @keyframes float {
            0%,100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-12px) rotate(2deg); }
        }

        /* ── Slide reveal anim ── */
        .slide.is-active .emoji-stage,
        .slide.is-active .slide-title,
        .slide.is-active .slide-text {
            animation: slideUp 0.9s var(--ease-apple) both;
        }
        .slide.is-active .slide-title { animation-delay: 0.1s; }
        .slide.is-active .slide-text { animation-delay: 0.2s; }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Dots ── */
        .dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: rgba(255,255,255,0.18);
            transition: all 0.4s var(--ease-apple);
        }
        .dot.active {
            width: 28px; border-radius: 4px;
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c);
            box-shadow: 0 4px 14px rgba(255,94,108,0.5);
        }

        /* ── Orbs (parallax) ── */
        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(90px); pointer-events: none;
            transition: transform 0.6s var(--ease-apple);
            will-change: transform;
        }

        /* ── Buttons ── */
        .btn-primary {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c);
            box-shadow: 0 10px 30px -8px rgba(255,94,108,0.55), inset 0 1px 0 rgba(255,255,255,0.2);
            transition: all 0.4s var(--ease-apple);
        }
        .btn-primary:active { transform: scale(0.97); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 40px -8px rgba(255,94,108,0.7); }
        .btn-ghost {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s var(--ease-apple);
            backdrop-filter: blur(10px);
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.08); }

        @media (prefers-reduced-motion: reduce) {
            .emoji-main { animation: none; }
            .slides-track { transition: none; }
            .slide.is-active .emoji-stage,
            .slide.is-active .slide-title,
            .slide.is-active .slide-text { animation: none; opacity: 1; transform: none; }
        }
    </style>
</head>
<body class="text-white hero-bg">
    {{-- Orbes parallax --}}
    <div class="orb" data-parallax-x="0.2" data-parallax-y="0.3" style="width:340px;height:340px;background:#ff5e6c;top:-100px;right:-110px;opacity:0.18"></div>
    <div class="orb" data-parallax-x="-0.3" data-parallax-y="-0.2" style="width:300px;height:300px;background:#a855f7;bottom:-80px;left:-100px;opacity:0.18"></div>
    <div class="orb" data-parallax-x="0.15" data-parallax-y="-0.15" style="width:240px;height:240px;background:#ffc145;top:40%;left:50%;opacity:0.08;transform:translate(-50%,-50%)"></div>

    <div class="relative z-10 w-full max-w-md mx-auto h-screen flex flex-col" style="height: 100dvh;">

        {{-- Top bar --}}
        <div class="flex items-center justify-between px-5 pt-4 pb-2" style="padding-top: max(env(safe-area-inset-top, 14px), 14px);">
            <a href="{{ route('home') }}" class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-[11px] text-white/40 hover:text-white/70 hover:bg-white/5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Accueil
            </a>
            <a href="{{ route('register') }}" class="text-[11px] text-white/40 hover:text-white/80 transition px-3 py-2">Passer →</a>
        </div>

        {{-- Slides --}}
        <div id="slides-stage" class="flex-1 overflow-hidden">
            <div id="slides-track" class="slides-track">

                {{-- Slide 1 --}}
                <div class="slide is-active" data-slide="0">
                    <p class="eyebrow mb-3">Bienvenue</p>
                    <div class="emoji-stage">
                        <div class="emoji-glow" style="background: radial-gradient(circle, #ff5e6c 0%, transparent 70%);"></div>
                        <div class="emoji-main">🔥</div>
                    </div>
                    <h1 class="display text-3xl sm:text-4xl mb-3 cc-gradient-text">Campus Crush</h1>
                    <p class="slide-text text-white/55 text-base leading-relaxed max-w-[300px]">
                        L'app de rencontres faite pour les étudiants sénégalais. Trouve ton crush sur le campus.
                    </p>
                </div>

                {{-- Slide 2 --}}
                <div class="slide" data-slide="1">
                    <p class="eyebrow mb-3">02 — Match</p>
                    <div class="emoji-stage">
                        <div class="emoji-glow" style="background: radial-gradient(circle, #ff8a5c 0%, transparent 70%);"></div>
                        <div class="emoji-main">💕</div>
                    </div>
                    <h2 class="slide-title display text-2xl sm:text-3xl mb-3">Swipe & Match</h2>
                    <p class="slide-text text-white/55 text-base leading-relaxed max-w-[300px]">
                        Swipe à droite si tu kiffes, à gauche si c'est pas ton style. Quand c'est réciproque, c'est un match !
                    </p>
                </div>

                {{-- Slide 3 --}}
                <div class="slide" data-slide="2">
                    <p class="eyebrow mb-3">03 — Universités</p>
                    <div class="emoji-stage">
                        <div class="emoji-glow" style="background: radial-gradient(circle, #a855f7 0%, transparent 70%);"></div>
                        <div class="emoji-main">🏫</div>
                    </div>
                    <h2 class="slide-title display text-2xl sm:text-3xl mb-3">11 Universités</h2>
                    <p class="slide-text text-white/55 text-base leading-relaxed max-w-[300px]">
                        UGB, UCAD, UADB, UASZ et plus encore. Filtre par université, UFR ou promotion.
                    </p>
                </div>

                {{-- Slide 4 --}}
                <div class="slide" data-slide="3">
                    <p class="eyebrow mb-3">04 — Cadeau</p>
                    <div class="emoji-stage">
                        <div class="emoji-glow" style="background: radial-gradient(circle, #ffc145 0%, transparent 70%);"></div>
                        <div class="emoji-main">🎁</div>
                    </div>
                    <h2 class="slide-title display text-2xl sm:text-3xl mb-3">1<sup class="text-base">er</sup> mois <span class="cc-gradient-text">offert</span></h2>
                    <p class="slide-text text-white/55 text-base leading-relaxed max-w-[300px]">
                        30 jours d'essai gratuit. Ensuite, seulement 1 000 FCFA/mois.
                    </p>
                </div>
            </div>
        </div>

        {{-- Bottom --}}
        <div class="px-5 pb-6" style="padding-bottom: max(env(safe-area-inset-bottom, 24px), 24px);">
            <div class="flex items-center justify-center gap-2 mb-6">
                <span class="dot active" data-dot="0"></span>
                <span class="dot" data-dot="1"></span>
                <span class="dot" data-dot="2"></span>
                <span class="dot" data-dot="3"></span>
            </div>

            <div id="btn-next-wrap">
                <button id="btn-next" class="btn-primary w-full py-4 rounded-2xl font-semibold text-white text-base">
                    Suivant
                </button>
            </div>

            <div id="btn-start-wrap" class="hidden space-y-2.5">
                <a href="{{ route('register') }}" class="btn-primary block w-full py-4 rounded-2xl font-semibold text-white text-base text-center">
                    Créer mon compte 🚀
                </a>
                <a href="{{ route('login') }}" class="btn-ghost block w-full py-3.5 rounded-2xl font-medium text-white/70 text-sm text-center">
                    J'ai déjà un compte
                </a>
                <a href="{{ route('home') }}" class="block w-full py-2.5 text-center text-xs text-white/30 hover:text-white/50 transition">
                    ← Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

    <script>
    (function() {
        let current = 0;
        const total = 4;
        const track = document.getElementById('slides-track');
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const stage = document.getElementById('slides-stage');
        const btnNext = document.getElementById('btn-next');
        const btnNextWrap = document.getElementById('btn-next-wrap');
        const btnStartWrap = document.getElementById('btn-start-wrap');
        const orbs = document.querySelectorAll('[data-parallax-x]');
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function goTo(index) {
            index = Math.max(0, Math.min(total - 1, index));
            track.style.transform = `translateX(-${index * 25}%)`;
            slides.forEach((s, i) => s.classList.toggle('is-active', i === index));
            dots.forEach((d, i) => d.classList.toggle('active', i === index));
            current = index;

            // Reflow to retrigger CSS animations
            slides[index].classList.remove('is-active');
            void slides[index].offsetWidth;
            slides[index].classList.add('is-active');

            // Move orbs based on slide
            if (!reduced) {
                orbs.forEach(orb => {
                    const px = parseFloat(orb.dataset.parallaxX) || 0;
                    const py = parseFloat(orb.dataset.parallaxY) || 0;
                    orb.style.transform = `translate(${index * 60 * px}px, ${index * 60 * py}px)`;
                });
            }

            if (current === total - 1) {
                btnNextWrap.classList.add('hidden');
                btnStartWrap.classList.remove('hidden');
            } else {
                btnNextWrap.classList.remove('hidden');
                btnStartWrap.classList.add('hidden');
            }
        }

        btnNext.addEventListener('click', () => goTo(current + 1));
        dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));

        // Touch swipe
        let startX = 0, deltaX = 0, dragging = false;
        stage.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            dragging = true;
        }, { passive: true });
        stage.addEventListener('touchmove', (e) => {
            if (!dragging) return;
            deltaX = e.touches[0].clientX - startX;
            // Subtle drag preview
            const stageW = stage.offsetWidth;
            const offset = (deltaX / stageW) * 25;
            track.style.transition = 'none';
            track.style.transform = `translateX(${-(current * 25) + offset}%)`;
        }, { passive: true });
        stage.addEventListener('touchend', () => {
            dragging = false;
            track.style.transition = '';
            if (Math.abs(deltaX) > 50) {
                goTo(deltaX < 0 ? current + 1 : current - 1);
            } else {
                goTo(current);
            }
            deltaX = 0;
        });

        // Keyboard
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') goTo(current + 1);
            if (e.key === 'ArrowLeft') goTo(current - 1);
        });

        try { localStorage.setItem('cc_onboarding_seen', '1'); } catch(e) {}
    })();
    </script>
</body>
</html>
