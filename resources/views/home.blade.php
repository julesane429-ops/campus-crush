<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @include('components.pwa-meta')
    <title>Campus Crush — Rencontres Universitaires au Sénégal</title>
    <meta name="description" content="L'appli de rencontres exclusivement conçue pour les étudiants sénégalais. Swipe, match et discute avec des étudiants de ton campus.">
    <meta name="theme-color" content="#070512">
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@200;300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-0: #050310;
            --bg-1: #0c0a1a;
            --bg-2: #1a1145;
            --bg-3: #0f1a3a;
            --accent-1: #ff5e6c;
            --accent-2: #ff8a5c;
            --accent-3: #ffc145;
            --accent-purple: #a855f7;
            --ease-apple: cubic-bezier(0.22, 1, 0.36, 1);
            --ease-soft: cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { font-family: 'Sora', -apple-system, BlinkMacSystemFont, 'SF Pro Display', sans-serif; box-sizing: border-box; -webkit-font-smoothing: antialiased; }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg-0);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Apple-style typography ── */
        .display {
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 0.98;
        }
        .display-sm { letter-spacing: -0.03em; line-height: 1.05; }
        .eyebrow {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            color: rgba(255,255,255,0.35);
        }

        /* ── Gradient text ── */
        .cc-gradient-text {
            background: linear-gradient(135deg, #ff5e6c 0%, #ff8a5c 45%, #ffc145 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            background-size: 200% 200%;
            animation: shimmer 6s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%,100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* ── Buttons ── */
        .cc-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 16px 30px; border-radius: 999px; font-weight: 600;
            font-size: 15px; letter-spacing: -0.01em;
            transition: all 0.4s var(--ease-apple);
            cursor: pointer; text-decoration: none; will-change: transform;
        }
        .cc-btn-primary {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c);
            color: white;
            box-shadow: 0 10px 40px -8px rgba(255,94,108,0.55), inset 0 1px 0 rgba(255,255,255,0.18);
        }
        .cc-btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 18px 50px -8px rgba(255,94,108,0.7), inset 0 1px 0 rgba(255,255,255,0.25);
        }
        .cc-btn-ghost {
            background: rgba(255,255,255,0.04); color: #f0eef5;
            border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .cc-btn-ghost:hover { background: rgba(255,255,255,0.08); transform: translateY(-2px); }

        /* ── Glass cards ── */
        .glass-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.015));
            border: 1px solid rgba(255,255,255,0.07);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            transition: all 0.6s var(--ease-apple);
            will-change: transform;
        }
        .glass-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255,94,108,0.25);
            box-shadow: 0 30px 80px -20px rgba(255,94,108,0.25), 0 8px 32px rgba(0,0,0,0.4);
        }

        /* ── Reveal-on-scroll ── */
        .reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 0.9s var(--ease-apple), transform 0.9s var(--ease-apple);
            will-change: opacity, transform;
        }
        .reveal.is-visible { opacity: 1; transform: translateY(0); }
        .reveal-stagger > * { transition-delay: calc(var(--i, 0) * 80ms); }

        .scale-in {
            opacity: 0;
            transform: scale(0.92);
            transition: opacity 1s var(--ease-apple), transform 1s var(--ease-apple);
        }
        .scale-in.is-visible { opacity: 1; transform: scale(1); }

        /* ── Float / hover idle ── */
        @keyframes float {
            0%,100% { transform: translateY(0) rotate(0deg); }
            33% { transform: translateY(-14px) rotate(1.5deg); }
            66% { transform: translateY(-6px) rotate(-1deg); }
        }
        @keyframes cardFloat {
            0%,100% { transform: translateY(0px) rotateZ(-2deg) rotateY(-3deg); }
            50% { transform: translateY(-12px) rotateZ(-2deg) rotateY(-3deg); }
        }
        .float { animation: float 7s ease-in-out infinite; }

        /* ── Orbs (parallax targets) ── */
        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(110px); opacity: 0.18; pointer-events: none;
            will-change: transform;
            transition: transform 0.6s var(--ease-soft);
        }

        /* ── Hero gradient bg overlay ── */
        .hero-bg {
            background:
                radial-gradient(ellipse at top right, rgba(255,94,108,0.25) 0%, transparent 50%),
                radial-gradient(ellipse at bottom left, rgba(168,85,247,0.18) 0%, transparent 50%),
                linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
        }

        /* ── Phone mockup wrapper for parallax ── */
        .phone-wrap {
            filter: drop-shadow(0 50px 90px rgba(255,94,108,0.28))
                    drop-shadow(0 25px 50px rgba(0,0,0,0.55));
            will-change: transform;
        }

        /* ── Sticky scroll feature section ── */
        .sticky-scroll {
            position: relative;
        }
        .sticky-card {
            opacity: 0.25;
            transform: scale(0.95);
            transition: opacity 0.7s var(--ease-apple), transform 0.7s var(--ease-apple);
        }
        .sticky-card.is-active {
            opacity: 1;
            transform: scale(1);
        }

        /* ── Stat counter ── */
        .stat-number {
            font-family: 'Space Mono', monospace;
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 700;
            background: linear-gradient(135deg, #ff5e6c, #ffc145);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            letter-spacing: -0.02em;
        }

        /* ── University pill ── */
        .univ-pill {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 100px;
            padding: 9px 18px;
            font-size: 12px;
            font-weight: 500;
            color: rgba(255,255,255,0.55);
            transition: all 0.3s var(--ease-apple);
            white-space: nowrap;
            backdrop-filter: blur(10px);
        }
        .univ-pill:hover {
            background: rgba(255,94,108,0.08);
            border-color: rgba(255,94,108,0.3);
            color: rgba(255,255,255,0.95);
            transform: translateY(-2px);
        }

        /* ── Scroll universities ── */
        .univs-scroll {
            display: flex; gap: 10px; overflow-x: auto; padding: 4px 0 12px;
            scrollbar-width: none; -ms-overflow-style: none;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }
        .univs-scroll::-webkit-scrollbar { display: none; }
        .univs-scroll > * { scroll-snap-align: start; }

        /* ── Marquee for universities (desktop) ── */
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .marquee { display: flex; gap: 10px; width: max-content; animation: marquee 40s linear infinite; }
        @media (prefers-reduced-motion: reduce) {
            .marquee { animation: none; }
            .reveal, .scale-in { opacity: 1; transform: none; transition: none; }
            .float, .cc-gradient-text { animation: none; }
        }

        .cc-mono { font-family: 'Space Mono', monospace; }

        /* ── Noise texture (Apple-style depth) ── */
        .noise::after {
            content: '';
            position: absolute; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.04;
            mix-blend-mode: overlay;
            pointer-events: none;
        }

        /* ── Hero scroll-zoom effect ── */
        .hero-content { transition: transform 0.1s linear; }

        /* ── Mobile-first refinements ── */
        @media (max-width: 640px) {
            .cc-btn { padding: 14px 24px; font-size: 14px; }
            .display { letter-spacing: -0.035em; }
        }

        /* ── Touch device polish ── */
        @media (hover: none) {
            .glass-card:hover { transform: none; }
            .cc-btn-primary:hover { transform: none; }
        }

        /* ── Subtle grain on cards ── */
        .grain { position: relative; isolation: isolate; }

        /* ── Marquee gradient mask ── */
        .marquee-mask {
            -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
            mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
            overflow: hidden;
        }

        /* ── Magic CTA glow ── */
        .cta-glow {
            position: relative;
            overflow: hidden;
        }
        .cta-glow::before {
            content: '';
            position: absolute;
            inset: -50%;
            background: conic-gradient(from 0deg, transparent, rgba(255,94,108,0.5), transparent 30%);
            animation: rotate-glow 6s linear infinite;
            z-index: 0;
        }
        @keyframes rotate-glow { to { transform: rotate(360deg); } }
        .cta-glow > * { position: relative; z-index: 1; }

        /* ── Cursor spotlight (Apple/Linear-style) ── */
        .spotlight {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(600px circle at var(--mx, 50%) var(--my, 50%),
                rgba(255,94,108,0.12) 0%,
                rgba(168,85,247,0.06) 30%,
                transparent 60%);
            transition: opacity 0.4s var(--ease-apple);
            opacity: 0;
            mix-blend-mode: screen;
        }
        .spotlight-host:hover .spotlight,
        .spotlight-host.is-touched .spotlight { opacity: 1; }

        /* ── Card spotlight (Linear-style hover) ── */
        .card-spotlight {
            position: absolute; inset: 0;
            background: radial-gradient(220px circle at var(--cx, 50%) var(--cy, 50%),
                rgba(255,255,255,0.07), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
            border-radius: inherit;
        }
        .glass-card:hover .card-spotlight { opacity: 1; }

        /* ── Marquee word reveal (Apple keynote-style) ── */
        .word-reveal {
            display: inline-block;
            overflow: hidden;
            vertical-align: top;
        }
        .word-reveal > span {
            display: inline-block;
            transform: translateY(110%);
            will-change: transform;
        }

        /* ── Magnetic button helper ── */
        .magnetic { will-change: transform; }

        /* ── Pinned section ── */
        .pin-section {
            position: relative;
            min-height: 100vh;
        }

        /* ── Number tickers ── */
        .ticker {
            display: inline-block;
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>

<body class="text-white">

    {{-- Orbes décoratifs avec parallax --}}
    <div class="orb" data-parallax="0.3" style="width:520px;height:520px;background:#ff5e6c;top:-160px;right:-160px;"></div>
    <div class="orb" data-parallax="0.5" style="width:420px;height:420px;background:#a855f7;top:50%;left:-180px;"></div>
    <div class="orb" data-parallax="0.2" style="width:380px;height:380px;background:#ffc145;bottom:200px;right:-100px;opacity:0.1"></div>

    <div class="relative z-10 w-full">

        {{-- ════════════════════════════════════════════
             NAVBAR — Apple style sticky glass
        ════════════════════════════════════════════ --}}
        <nav id="navbar" class="sticky top-0 z-50 transition-all duration-500" style="background: rgba(7,5,18,0); border-bottom: 1px solid transparent;">
            <div class="max-w-6xl mx-auto px-5 py-4 flex justify-between items-center">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg,#ff5e6c,#ffc145); box-shadow: 0 8px 22px -8px rgba(255,94,108,0.6);">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-lg tracking-tight">Campus Crush</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('login') }}" class="text-sm text-white/55 hover:text-white transition hidden sm:block px-3 py-2">Connexion</a>
                    <a href="{{ route('register') }}" class="cc-btn cc-btn-primary !text-sm !py-2.5 !px-5">S'inscrire</a>
                    <a href="/install" class="hidden md:inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-medium text-white/55 border border-white/10 hover:bg-white/5 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                        Installer
                    </a>
                </div>
            </div>
        </nav>

        {{-- ════════════════════════════════════════════
             HERO — Apple-style massive typography
        ════════════════════════════════════════════ --}}
        <section class="hero-bg relative overflow-hidden spotlight-host" id="hero">
            <div class="spotlight"></div>
            <div class="max-w-6xl mx-auto px-5 pt-12 pb-20 md:pt-24 md:pb-32 relative">
                <div class="hero-content flex flex-col lg:flex-row items-center gap-12 lg:gap-20">

                    {{-- Texte --}}
                    <div class="flex-1 text-center lg:text-left">
                        <div class="inline-flex mb-7 reveal" style="--i: 0">
                            <span class="eyebrow px-4 py-2 rounded-full border border-white/10 bg-white/[0.03] backdrop-blur">
                                🇸🇳 Made for Sénégal
                            </span>
                        </div>

                        <h1 class="display text-[44px] sm:text-6xl md:text-7xl lg:text-[88px] mb-7">
                            <span class="word-reveal block"><span>Trouve ton</span></span>
                            <span class="word-reveal block"><span class="cc-gradient-text">crush</span><span>.</span></span>
                            <span class="word-reveal block"><span class="text-white/95">Sur le campus.</span></span>
                        </h1>

                        <p class="text-lg md:text-xl text-white/45 max-w-xl mx-auto lg:mx-0 mb-10 reveal leading-relaxed" style="--i: 2">
                            L'appli de rencontres pensée pour les étudiants sénégalais.
                            Swipe, match, discute — tout simplement.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start reveal mb-10" style="--i: 3">
                            <a href="{{ route('register') }}" class="cc-btn cc-btn-primary text-base">
                                Commencer gratuitement
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                            <a href="{{ route('login') }}" class="cc-btn cc-btn-ghost text-base">
                                Se connecter
                            </a>
                        </div>

                        {{-- Social proof --}}
                        <div class="flex flex-col sm:flex-row items-center gap-5 justify-center lg:justify-start reveal" style="--i: 4">
                            <div class="flex items-center gap-3">
                                <div class="flex">
                                    @foreach(['AB','KD','FL','MS','AD'] as $i => $initials)
                                    <img class="w-8 h-8 rounded-full border-2 border-[#1a1145] object-cover {{ $i > 0 ? '-ml-2' : '' }}"
                                         src="https://ui-avatars.com/api/?background={{ ['ff5e6c','a855f7','ffc145','ff8a5c','1a1145'][$i] }}&color=fff&bold=true&name={{ $initials }}&size=64"
                                         alt="{{ $initials }}">
                                    @endforeach
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-white/85">
                                        {{ $stats['users'] }} étudiants inscrits
                                    </p>
                                    <div class="flex items-center gap-0.5 mt-0.5">
                                        @for($i = 0; $i < 5; $i++)<span class="text-xs text-[#ffc145]">★</span>@endfor
                                        <span class="text-[11px] text-white/35 ml-1.5">par les étudiants</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Phone mockup avec parallax --}}
                    <div class="flex-shrink-0 scale-in" data-parallax-tilt>
                        <div class="phone-wrap" style="animation: cardFloat 6s ease-in-out infinite;">
                            <svg width="280" height="560" viewBox="0 0 260 520" fill="none" xmlns="http://www.w3.org/2000/svg" class="max-w-full h-auto">
                                <rect x="2" y="2" width="256" height="516" rx="42" fill="#0c0a1a" stroke="rgba(255,255,255,0.14)" stroke-width="2"/>
                                <rect x="90" y="12" width="80" height="22" rx="11" fill="#1a1145"/>
                                <circle cx="102" cy="23" r="4" fill="rgba(255,255,255,0.18)"/>
                                <circle cx="158" cy="23" r="3" fill="rgba(255,255,255,0.12)"/>

                                <rect x="2" y="42" width="256" height="48" fill="rgba(255,255,255,0.03)"/>
                                <circle cx="28" cy="66" r="14" fill="url(#avatarGrad)"/>
                                <text x="22" y="71" font-size="11" fill="white" font-family="sans-serif" font-weight="bold">AB</text>
                                <text x="50" y="63" font-size="11" fill="white" font-family="sans-serif" font-weight="700">🔥 Campus Crush</text>
                                <rect x="210" y="54" width="28" height="24" rx="8" fill="rgba(255,94,108,0.22)"/>
                                <text x="219" y="71" font-size="13" fill="white" font-family="sans-serif">♡</text>

                                <rect x="20" y="102" width="220" height="280" rx="26" fill="url(#cardGrad)"/>
                                <rect x="20" y="102" width="220" height="200" rx="26" fill="url(#photoGrad)"/>
                                <rect x="20" y="258" width="220" height="124" rx="26" fill="url(#infoGrad)"/>
                                <text x="36" y="294" font-size="18" fill="white" font-family="sans-serif" font-weight="700">Aïssatou, 21</text>
                                <text x="36" y="314" font-size="11" fill="rgba(255,255,255,0.6)" font-family="sans-serif">📍 UCAD · Lettres</text>
                                <rect x="36" y="326" width="140" height="8" rx="4" fill="rgba(255,255,255,0.1)"/>
                                <rect x="36" y="344" width="52" height="18" rx="9" fill="rgba(255,94,108,0.22)"/>
                                <text x="46" y="357" font-size="9" fill="rgba(255,94,108,0.95)" font-family="sans-serif">Musique</text>
                                <rect x="96" y="344" width="48" height="18" rx="9" fill="rgba(168,85,247,0.22)"/>
                                <text x="105" y="357" font-size="9" fill="rgba(168,85,247,0.95)" font-family="sans-serif">Voyage</text>

                                <g transform="rotate(-15, 185, 145)">
                                    <rect x="155" y="120" width="72" height="34" rx="8" fill="transparent" stroke="#4ade80" stroke-width="3"/>
                                    <text x="163" y="143" font-size="16" fill="#4ade80" font-family="sans-serif" font-weight="800">LIKE</text>
                                </g>

                                <text x="82" y="418" font-size="11" fill="rgba(255,255,255,0.22)" font-family="sans-serif">← passe · like →</text>

                                <circle cx="90" cy="454" r="26" fill="rgba(239,68,68,0.14)" stroke="rgba(239,68,68,0.25)" stroke-width="1.5"/>
                                <text x="80" y="462" font-size="20" font-family="sans-serif">✕</text>

                                <circle cx="170" cy="454" r="30" fill="url(#likeBtn)"/>
                                <text x="158" y="464" font-size="22" font-family="sans-serif">♡</text>

                                <rect x="20" y="494" width="220" height="18" rx="9" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.07)" stroke-width="1"/>
                                <rect x="28" y="498" width="48" height="10" rx="5" fill="url(#activeNav)"/>
                                <rect x="84" y="498" width="36" height="10" rx="5" fill="rgba(255,255,255,0.06)"/>
                                <rect x="128" y="498" width="36" height="10" rx="5" fill="rgba(255,255,255,0.06)"/>
                                <rect x="172" y="498" width="36" height="10" rx="5" fill="rgba(255,255,255,0.06)"/>

                                <defs>
                                    <linearGradient id="cardGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#1e1060"/>
                                        <stop offset="100%" stop-color="#0f1a3a"/>
                                    </linearGradient>
                                    <linearGradient id="photoGrad" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#2d1b69"/>
                                        <stop offset="50%" stop-color="#1a0e4a"/>
                                        <stop offset="100%" stop-color="#3b1a3a"/>
                                    </linearGradient>
                                    <linearGradient id="infoGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="rgba(12,10,26,0)"/>
                                        <stop offset="40%" stop-color="rgba(12,10,26,0.85)"/>
                                        <stop offset="100%" stop-color="rgba(12,10,26,0.98)"/>
                                    </linearGradient>
                                    <linearGradient id="likeBtn" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#ff5e6c"/>
                                        <stop offset="100%" stop-color="#ff8a5c"/>
                                    </linearGradient>
                                    <linearGradient id="activeNav" x1="0" y1="0" x2="1" y2="0">
                                        <stop offset="0%" stop-color="#ff5e6c"/>
                                        <stop offset="100%" stop-color="#ff8a5c"/>
                                    </linearGradient>
                                    <linearGradient id="avatarGrad" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#ff5e6c"/>
                                        <stop offset="100%" stop-color="#ffc145"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Scroll cue --}}
                <div class="hidden md:flex justify-center mt-16 reveal" style="--i: 6">
                    <div class="flex flex-col items-center gap-2 text-white/30">
                        <span class="eyebrow !text-[10px]">Découvrir</span>
                        <div class="w-[1px] h-10 bg-gradient-to-b from-white/30 to-transparent animate-pulse"></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             STATS — Apple-style numbers
        ════════════════════════════════════════════ --}}
        <section class="px-5 py-16 md:py-24">
            <div class="max-w-4xl mx-auto">
                <p class="eyebrow text-center mb-8 reveal">La communauté Campus Crush</p>
                <div class="grid grid-cols-3 gap-2 sm:gap-4 rounded-3xl p-6 sm:p-10 reveal scale-in"
                     style="background: linear-gradient(135deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01)); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(20px);">
                    <div class="text-center">
                        <div class="stat-number ticker">{{ $stats['users'] }}</div>
                        <p class="text-[11px] sm:text-xs text-white/40 mt-2 font-medium uppercase tracking-wider">Étudiants</p>
                    </div>
                    <div class="text-center border-x" style="border-color: rgba(255,255,255,0.07);">
                        <div class="stat-number ticker">{{ $stats['matches'] }}</div>
                        <p class="text-[11px] sm:text-xs text-white/40 mt-2 font-medium uppercase tracking-wider">Matchs</p>
                    </div>
                    <div class="text-center">
                        <div class="stat-number ticker">{{ $stats['univs'] }}</div>
                        <p class="text-[11px] sm:text-xs text-white/40 mt-2 font-medium uppercase tracking-wider">Universités</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             COMMENT ÇA MARCHE — Apple-style sticky-scroll
        ════════════════════════════════════════════ --}}
        <section class="px-5 py-16 md:py-28">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-16 md:mb-24">
                    <p class="eyebrow mb-4 reveal">Comment ça marche</p>
                    <h2 class="display display-sm text-4xl md:text-6xl reveal" style="--i: 1">
                        Trois étapes,<br>
                        <span class="cc-gradient-text">et c'est parti.</span>
                    </h2>
                </div>

                <div class="space-y-5 md:space-y-7">
                    @foreach([
                        ['01', '📸', 'Crée ton profil', 'Photo, filière, bio et tes passions. Deux minutes chrono pour montrer qui tu es vraiment.', '#ff5e6c', 'rgba(255,94,108,0.12)'],
                        ['02', '🔥', 'Swipe & like', 'Découvre des étudiants de ton campus. Like ceux qui te plaisent — un geste, c\'est tout.', '#a855f7', 'rgba(168,85,247,0.12)'],
                        ['03', '💬', 'Match & discute', 'Si c\'est mutuel, c\'est un match ! Brise la glace avec des icebreakers ou un message.', '#ffc145', 'rgba(255,193,69,0.12)'],
                    ] as $idx => $s)
                    <div class="glass-card sticky-card reveal grain rounded-3xl p-7 md:p-10 flex flex-col md:flex-row md:items-center gap-6 md:gap-10" style="--i: {{ $idx }}">
                        <div class="flex-shrink-0 flex items-center gap-5 md:flex-col md:gap-3 md:w-32">
                            <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl flex items-center justify-center text-3xl md:text-4xl"
                                 style="background: {{ $s[5] }}; border: 1px solid {{ $s[4] }}30;">
                                {{ $s[1] }}
                            </div>
                            <span class="cc-mono text-xs tracking-[0.2em] font-bold" style="color: {{ $s[4] }};">{{ $s[0] }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl md:text-3xl font-bold mb-2 tracking-tight">{{ $s[2] }}</h3>
                            <p class="text-base md:text-lg text-white/45 leading-relaxed max-w-2xl">{{ $s[3] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             FEATURES GRID — Bento style
        ════════════════════════════════════════════ --}}
        <section class="px-5 py-16 md:py-24">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-14">
                    <p class="eyebrow mb-4 reveal">Ce qui rend ça spécial</p>
                    <h2 class="display display-sm text-3xl md:text-5xl reveal" style="--i: 1">
                        Pensé pour <span class="cc-gradient-text">vous</span>.
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-5">
                    {{-- Big card --}}
                    <div class="glass-card grain reveal rounded-3xl p-7 md:col-span-2 md:row-span-2 relative overflow-hidden min-h-[260px]" style="--i: 0">
                        <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full blur-3xl" style="background: rgba(255,94,108,0.25);"></div>
                        <div class="relative">
                            <div class="text-5xl mb-5">🛡️</div>
                            <h3 class="text-2xl md:text-3xl font-bold mb-3 tracking-tight">100% étudiants vérifiés</h3>
                            <p class="text-white/45 leading-relaxed text-base md:text-lg max-w-md">
                                Inscription par université, profils modérés. Pas de bots, pas de faux comptes — juste des étudiants comme toi.
                            </p>
                        </div>
                    </div>

                    <div class="glass-card grain reveal rounded-3xl p-6 relative overflow-hidden" style="--i: 1">
                        <div class="text-4xl mb-3">⚡</div>
                        <h3 class="text-lg font-bold mb-1.5 tracking-tight">Boost</h3>
                        <p class="text-white/40 text-sm leading-relaxed">Sois en tête de la liste pendant 30min.</p>
                    </div>

                    <div class="glass-card grain reveal rounded-3xl p-6 relative overflow-hidden" style="--i: 2">
                        <div class="text-4xl mb-3">🤖</div>
                        <h3 class="text-lg font-bold mb-1.5 tracking-tight">AI Coach</h3>
                        <p class="text-white/40 text-sm leading-relaxed">Conseils IA pour briser la glace.</p>
                    </div>

                    <div class="glass-card grain reveal rounded-3xl p-6 relative overflow-hidden md:col-span-2" style="--i: 3">
                        <div class="flex items-center gap-5">
                            <div class="text-4xl">💳</div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold mb-1 tracking-tight">Paiement local</h3>
                                <p class="text-white/40 text-sm leading-relaxed">Wave, Orange Money, Free Money — directement dans l'app.</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card grain reveal rounded-3xl p-6 relative overflow-hidden" style="--i: 4">
                        <div class="text-4xl mb-3">🎁</div>
                        <h3 class="text-lg font-bold mb-1.5 tracking-tight">1er mois</h3>
                        <p class="text-white/40 text-sm leading-relaxed">Gratuit. Aucune carte requise.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             UNIVERSITÉS — Marquee scroll
        ════════════════════════════════════════════ --}}
        @if($universities->count() > 0)
        <section class="px-5 py-16 md:py-20">
            <div class="max-w-5xl mx-auto">
                <p class="eyebrow text-center mb-8 reveal">Étudiants de toutes les universités du Sénégal</p>
                {{-- Mobile: scroll horizontal. Desktop: marquee infini --}}
                <div class="md:hidden univs-scroll">
                    @foreach($universities as $univ)
                    <span class="univ-pill flex-shrink-0">
                        🎓 {{ $univ->short_name }}
                        @if($univ->city)<span class="text-white/30"> · {{ $univ->city }}</span>@endif
                    </span>
                    @endforeach
                </div>
                <div class="hidden md:block marquee-mask">
                    <div class="marquee">
                        @foreach($universities as $univ)
                        <span class="univ-pill flex-shrink-0">
                            🎓 {{ $univ->short_name }}
                            @if($univ->city)<span class="text-white/30"> · {{ $univ->city }}</span>@endif
                        </span>
                        @endforeach
                        @foreach($universities as $univ)
                        <span class="univ-pill flex-shrink-0">
                            🎓 {{ $univ->short_name }}
                            @if($univ->city)<span class="text-white/30"> · {{ $univ->city }}</span>@endif
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- ════════════════════════════════════════════
             AVIS UTILISATEURS
        ════════════════════════════════════════════ --}}
        @if($featuredReviews->count() > 0)
        <section class="px-5 py-16 md:py-24">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-14">
                    <p class="eyebrow mb-4 reveal">Avis</p>
                    <h2 class="display display-sm text-3xl md:text-5xl reveal" style="--i: 1">
                        Ce qu'ils en <span class="cc-gradient-text">pensent</span>.
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5">
                    @foreach($featuredReviews as $idx => $rev)
                    <div class="glass-card reveal grain rounded-2xl p-6" style="--i: {{ $idx }}">
                        <div class="flex items-center gap-0.5 mb-4">
                            @for($i = 1; $i <= 5; $i++)
                            <span class="text-sm {{ $i <= $rev->rating ? 'text-[#ffc145]' : 'text-white/10' }}">★</span>
                            @endfor
                        </div>
                        <p class="text-base text-white/70 leading-relaxed mb-5">
                            « {{ Str::limit($rev->comment, 150) }} »
                        </p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden ring-1 ring-white/10">
                                <img src="{{ $rev->user->profile?->photo_url ?? 'https://ui-avatars.com/api/?background=1a1145&color=ff5e6c&bold=true&name=' . urlencode(substr($rev->user->name, 0, 2)) }}"
                                     class="w-full h-full object-cover" alt="{{ $rev->user->name }}">
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-white/85">{{ $rev->user->name }}</p>
                                <p class="text-[11px] text-white/30">{{ $rev->user->profile?->university_name ?? 'Étudiant·e' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- ════════════════════════════════════════════
             CTA FINAL — Apple-style end card
        ════════════════════════════════════════════ --}}
        <section class="px-5 py-16 md:py-28">
            <div class="max-w-3xl mx-auto">
                <div class="cta-glow rounded-[32px] p-1 reveal">
                    <div class="rounded-[28px] p-10 md:p-16 text-center relative overflow-hidden"
                         style="background: linear-gradient(160deg, rgba(255,94,108,0.08), rgba(168,85,247,0.05) 50%, rgba(7,5,18,0.95)); border: 1px solid rgba(255,94,108,0.18); backdrop-filter: blur(30px);">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-[#ff5e6c] rounded-full blur-[100px] opacity-25 pointer-events-none"></div>
                        <div class="absolute bottom-0 left-0 w-56 h-56 bg-[#a855f7] rounded-full blur-[90px] opacity-15 pointer-events-none"></div>

                        <div class="text-6xl md:text-7xl mb-6 float">💘</div>
                        <h2 class="display text-4xl md:text-6xl mb-4 relative z-10">
                            Prêt(e) ?
                        </h2>
                        <p class="text-white/55 mb-2 relative z-10 text-lg">
                            Rejoins {{ $stats['users'] }} étudiants du Sénégal
                        </p>
                        <p class="text-white/30 mb-9 relative z-10 text-sm">
                            Premier mois gratuit · Aucune carte bancaire requise
                        </p>
                        <a href="{{ route('register') }}" class="cc-btn cc-btn-primary text-base md:text-lg relative z-10">
                            Créer mon compte
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             FOOTER
        ════════════════════════════════════════════ --}}
        <footer class="px-5 py-10 border-t" style="border-color: rgba(255,255,255,0.06);">
            <div class="max-w-5xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg,#ff5e6c,#ffc145);">
                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    </div>
                    <span class="text-sm font-bold">Campus Crush</span>
                </div>
                <div class="flex items-center gap-6 text-xs text-white/30">
                    <a href="{{ route('login') }}" class="hover:text-white/70 transition">Connexion</a>
                    <a href="{{ route('register') }}" class="hover:text-white/70 transition">S'inscrire</a>
                    <a href="/install" class="hover:text-white/70 transition">Installer</a>
                </div>
                <p class="text-xs text-white/25">© {{ date('Y') }} · Fait avec ❤️ au Sénégal</p>
            </div>
        </footer>
    </div>

    @include('components.pwa-install-banner')
    @include('components.promo-popup')

    <script>
    (function() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const supportsHover = window.matchMedia('(hover: hover)').matches;

        if (prefersReducedMotion) {
            document.querySelectorAll('.reveal, .scale-in').forEach(el => el.classList.add('is-visible'));
            return;
        }

        if (window.gsap && window.ScrollTrigger) {
            gsap.registerPlugin(ScrollTrigger);
            gsap.config({ nullTargetWarn: false });

            // ── Reveal — replaces IntersectionObserver, scrubbed easing ──
            gsap.utils.toArray('.reveal').forEach((el) => {
                const i = parseFloat(el.style.getPropertyValue('--i')) || 0;
                gsap.to(el, {
                    opacity: 1,
                    y: 0,
                    duration: 0.9,
                    delay: Math.min(i * 0.06, 0.4),
                    ease: 'expo.out',
                    scrollTrigger: { trigger: el, start: 'top 88%', toggleActions: 'play none none none' },
                });
            });

            gsap.utils.toArray('.scale-in').forEach((el) => {
                gsap.to(el, {
                    opacity: 1,
                    scale: 1,
                    duration: 1.1,
                    ease: 'expo.out',
                    scrollTrigger: { trigger: el, start: 'top 85%' },
                });
            });

            // ── Hero parallax: orbs scrub with scroll ──
            gsap.utils.toArray('[data-parallax]').forEach((orb) => {
                const speed = parseFloat(orb.dataset.parallax) || 0.3;
                gsap.to(orb, {
                    y: () => -window.innerHeight * speed,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: '#hero', start: 'top top', end: 'bottom top', scrub: 0.6,
                    },
                });
            });

            // ── Phone: lifts + scales subtly during hero scroll ──
            const phoneTarget = document.querySelector('[data-parallax-tilt]');
            if (phoneTarget) {
                gsap.to(phoneTarget, {
                    y: -60, scale: 0.94, ease: 'none',
                    scrollTrigger: {
                        trigger: '#hero', start: 'top top', end: 'bottom top', scrub: 0.5,
                    },
                });
            }

            // ── Sticky cards: scale + opacity drive ──
            gsap.utils.toArray('.sticky-card').forEach((card) => {
                gsap.fromTo(card,
                    { opacity: 0.3, scale: 0.94 },
                    {
                        opacity: 1, scale: 1, ease: 'power2.out',
                        scrollTrigger: { trigger: card, start: 'top 80%', end: 'top 40%', scrub: true },
                    },
                );
            });

            // ── Number tickers (stats) ──
            gsap.utils.toArray('.ticker').forEach((el) => {
                const target = el.textContent.trim();
                const num = parseInt(target.replace(/[^\d]/g, ''), 10);
                if (!Number.isFinite(num)) return;
                const suffix = target.replace(/[\d\s]/g, '');
                const obj = { v: 0 };
                gsap.to(obj, {
                    v: num,
                    duration: 1.6,
                    ease: 'power3.out',
                    onUpdate: () => { el.textContent = Math.round(obj.v).toLocaleString('fr-FR') + suffix; },
                    scrollTrigger: { trigger: el, start: 'top 90%', once: true },
                });
            });

            // ── Word-reveal hero (keynote-style line drop-in) ──
            gsap.utils.toArray('.word-reveal > span').forEach((span, i) => {
                gsap.to(span, {
                    y: 0,
                    duration: 1,
                    delay: 0.1 + i * 0.08,
                    ease: 'expo.out',
                });
            });

            // ── Phone tilt on cursor (3D) ──
            if (phoneTarget && supportsHover) {
                const inner = phoneTarget.querySelector('.phone-wrap');
                const qx = gsap.quickTo(inner, 'rotationY', { duration: 0.6, ease: 'expo.out' });
                const qy = gsap.quickTo(inner, 'rotationX', { duration: 0.6, ease: 'expo.out' });
                phoneTarget.addEventListener('mousemove', (e) => {
                    const rect = phoneTarget.getBoundingClientRect();
                    const cx = rect.left + rect.width / 2;
                    const cy = rect.top + rect.height / 2;
                    qx(((e.clientX - cx) / rect.width) * 12);
                    qy(((e.clientY - cy) / rect.height) * -10);
                });
                phoneTarget.addEventListener('mouseleave', () => { qx(0); qy(0); });
                gsap.set(inner, { transformPerspective: 1000, transformStyle: 'preserve-3d' });
            }

            // ── Magnetic CTA buttons ──
            if (supportsHover) {
                document.querySelectorAll('.cc-btn-primary').forEach((btn) => {
                    btn.classList.add('magnetic');
                    const qx = gsap.quickTo(btn, 'x', { duration: 0.5, ease: 'expo.out' });
                    const qy = gsap.quickTo(btn, 'y', { duration: 0.5, ease: 'expo.out' });
                    btn.addEventListener('mousemove', (e) => {
                        const r = btn.getBoundingClientRect();
                        qx((e.clientX - r.left - r.width / 2) * 0.25);
                        qy((e.clientY - r.top - r.height / 2) * 0.35);
                    });
                    btn.addEventListener('mouseleave', () => { qx(0); qy(0); });
                });
            }
        } else {
            // Fallback if GSAP fails to load
            document.querySelectorAll('.reveal, .scale-in').forEach(el => el.classList.add('is-visible'));
        }

        // ── Cursor spotlight (vanilla, doesn't need GSAP) ──
        if (supportsHover) {
            document.querySelectorAll('.spotlight-host').forEach((host) => {
                host.addEventListener('mousemove', (e) => {
                    const r = host.getBoundingClientRect();
                    host.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                    host.style.setProperty('--my', (e.clientY - r.top) + 'px');
                });
            });
        }

        // ── Navbar scroll-state (frosted glass when scrolled) ────
        const navbar = document.getElementById('navbar');
        if (navbar) {
            const onScroll = () => {
                if (window.scrollY > 20) {
                    navbar.style.background = 'rgba(7,5,18,0.78)';
                    navbar.style.backdropFilter = 'blur(24px)';
                    navbar.style.webkitBackdropFilter = 'blur(24px)';
                    navbar.style.borderBottomColor = 'rgba(255,255,255,0.06)';
                } else {
                    navbar.style.background = 'rgba(7,5,18,0)';
                    navbar.style.backdropFilter = 'none';
                    navbar.style.webkitBackdropFilter = 'none';
                    navbar.style.borderBottomColor = 'transparent';
                }
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }
    })();
    </script>
</body>
</html>
