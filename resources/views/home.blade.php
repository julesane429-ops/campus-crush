<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('components.pwa-meta')
    <title>Campus Crush — Rencontres Universitaires au Sénégal</title>
    <meta name="description" content="L'appli de rencontres exclusivement conçue pour les étudiants sénégalais. Swipe, match et discute avec des étudiants de ton campus.">
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; }

        body {
            background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
            min-height: 100vh;
        }

        /* ── Gradient text ── */
        .cc-gradient-text {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% 200%;
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%,100% { background-position: 0% 50%; }
            50%      { background-position: 100% 50%; }
        }

        /* ── Buttons ── */
        .cc-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 16px 32px; border-radius: 16px; font-weight: 600;
            font-size: 16px; transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            cursor: pointer; text-decoration: none;
        }
        .cc-btn-primary {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c);
            color: white; box-shadow: 0 8px 30px rgba(255,94,108,0.3);
        }
        .cc-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 14px 40px rgba(255,94,108,0.45); }
        .cc-btn-ghost {
            background: rgba(255,255,255,0.04); color: #f0eef5;
            border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(20px);
        }
        .cc-btn-ghost:hover { background: rgba(255,255,255,0.08); transform: translateY(-2px); }

        /* ── Cards ── */
        .glass-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            backdrop-filter: blur(20px);
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .glass-card:hover {
            transform: translateY(-6px);
            border-color: rgba(255,94,108,0.2);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        /* ── Animations ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: none; }
        }
        @keyframes float {
            0%,100% { transform: translateY(0) rotate(0deg); }
            33%     { transform: translateY(-12px) rotate(2deg); }
            66%     { transform: translateY(-6px) rotate(-1deg); }
        }
        @keyframes cardFloat {
            0%,100% { transform: translateY(0px) rotate(-2deg); }
            50%     { transform: translateY(-10px) rotate(-2deg); }
        }
        @keyframes pingOnce {
            0%   { transform: scale(1); opacity: 1; }
            70%  { transform: scale(2); opacity: 0; }
            100% { transform: scale(1); opacity: 0; }
        }
        .fade-up { animation: fadeUp 0.7s cubic-bezier(0.22,1,0.36,1) both; }
        .float   { animation: float 6s ease-in-out infinite; }
        .d1 { animation-delay: .1s; } .d2 { animation-delay: .25s; }
        .d3 { animation-delay: .4s; } .d4 { animation-delay: .55s; }
        .d5 { animation-delay: .7s; }

        /* ── Orbs ── */
        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(90px); opacity: 0.12; pointer-events: none;
        }

        /* ── Avatar stack ── */
        .avatar-stack { display: flex; }
        .avatar-stack img {
            width: 32px; height: 32px; border-radius: 50%;
            border: 2px solid #1a1145; margin-left: -8px; object-fit: cover;
        }
        .avatar-stack img:first-child { margin-left: 0; }

        /* ── Phone mockup ── */
        .phone-shadow {
            filter: drop-shadow(0 40px 80px rgba(255,94,108,0.25))
                    drop-shadow(0 20px 40px rgba(0,0,0,0.5));
        }

        /* ── Stat counter ── */
        .stat-number {
            font-family: 'Space Mono', monospace;
            font-size: 2.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff5e6c, #ffc145);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ── University pill ── */
        .univ-pill {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 100px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 500;
            color: rgba(255,255,255,0.5);
            transition: all 0.25s;
            white-space: nowrap;
        }
        .univ-pill:hover {
            background: rgba(255,94,108,0.08);
            border-color: rgba(255,94,108,0.2);
            color: rgba(255,255,255,0.8);
        }

        /* ── Scroll universities ── */
        .univs-scroll {
            display: flex; gap: 10px; overflow-x: auto; padding-bottom: 8px;
            scrollbar-width: none; -ms-overflow-style: none;
        }
        .univs-scroll::-webkit-scrollbar { display: none; }

        .cc-mono { font-family: 'Space Mono', monospace; }
    </style>
</head>

<body class="text-white overflow-x-hidden">

    {{-- Orbs décoratifs --}}
    <div class="orb" style="width:400px;height:400px;background:#ff5e6c;top:-100px;right:-100px;"></div>
    <div class="orb" style="width:350px;height:350px;background:#a855f7;bottom:200px;left:-120px;"></div>

    <div class="relative z-10 w-full">

        {{-- ════════════════════════════════════════════
             NAVBAR
        ════════════════════════════════════════════ --}}
        <nav class="sticky top-0 z-50 px-5 py-4 backdrop-blur-xl border-b" style="background: rgba(12,10,26,0.8); border-color: rgba(255,255,255,0.05);">
            <div class="max-w-5xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg,#ff5e6c,#ffc145);">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-lg cc-gradient-text">Campus Crush</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm text-white/50 hover:text-white transition hidden sm:block">Connexion</a>
                    <a href="{{ route('register') }}" class="cc-btn cc-btn-primary text-sm !py-2.5 !px-5">S'inscrire</a>
                    <a href="/install" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-white/50 border border-white/10 hover:bg-white/5 transition">
                        📲 Installer
                    </a>
                </div>
            </div>
        </nav>

        {{-- ════════════════════════════════════════════
             HERO
        ════════════════════════════════════════════ --}}
        <section class="px-5 pt-16 pb-12 md:pt-24">
            <div class="max-w-5xl mx-auto">
                <div class="flex flex-col lg:flex-row items-center gap-16">

                    {{-- Texte --}}
                    <div class="flex-1 text-center lg:text-left">
                        <div class="inline-flex mb-6 fade-up">
                            <span class="cc-mono text-[11px] uppercase tracking-[3px] text-white/30 px-4 py-2 rounded-full border border-white/10 bg-white/[0.02]">
                                🇸🇳 Made for Sénégal
                            </span>
                        </div>

                        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-[1.1] mb-6 fade-up d1">
                            Trouve ton<br><span class="cc-gradient-text">crush</span> sur le campus
                        </h1>

                        <p class="text-lg text-white/40 max-w-lg mx-auto lg:mx-0 mb-10 fade-up d2 leading-relaxed">
                            L'appli de rencontres exclusivement conçue pour les étudiants sénégalais. Swipe, match, discute.
                        </p>

                        {{-- CTA --}}
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start fade-up d3 mb-10">
                            <a href="{{ route('register') }}" class="cc-btn cc-btn-primary text-lg">
                                Commencer gratuitement →
                            </a>
                            <a href="{{ route('login') }}" class="cc-btn cc-btn-ghost text-lg">
                                Se connecter
                            </a>
                        </div>

                        {{-- Social proof ── NOUVEAU --}}
                        <div class="flex flex-col sm:flex-row items-center gap-5 justify-center lg:justify-start fade-up d4">
                            {{-- Avatar stack --}}
                            <div class="flex items-center gap-3">
                                <div class="avatar-stack">
                                    @foreach(['AB','KD','FL','MS','AD'] as $initials)
                                    <img src="https://ui-avatars.com/api/?background={{ ['1a1145','ff5e6c','a855f7','ffc145','ff8a5c'][rand(0,4)] }}&color=fff&bold=true&name={{ $initials }}&size=64"
                                         alt="{{ $initials }}">
                                    @endforeach
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-white/80">
                                        {{ $stats['users'] }} étudiants inscrits
                                    </p>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        @for($i = 0; $i < 5; $i++)
                                        <span class="text-xs text-[#ffc145]">★</span>
                                        @endfor
                                        <span class="text-[11px] text-white/30 ml-1">par les étudiants</span>
                                    </div>
                                </div>
                            </div>

                            <div class="hidden sm:block w-px h-8 bg-white/10"></div>

                            {{-- Badges --}}
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] px-3 py-1.5 rounded-full font-medium text-white/60"
                                      style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                                    ✓ Gratuit
                                </span>
                                <span class="text-[11px] px-3 py-1.5 rounded-full font-medium text-white/60"
                                      style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                                    ✓ Sécurisé
                                </span>
                                <span class="text-[11px] px-3 py-1.5 rounded-full font-medium text-white/60"
                                      style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                                    ✓ Privé
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Phone mockup ── NOUVEAU --}}
                    <div class="flex-shrink-0 fade-up d3 hidden md:block">
                        <div class="phone-shadow" style="animation: cardFloat 5s ease-in-out infinite;">
                            <svg width="260" height="520" viewBox="0 0 260 520" fill="none" xmlns="http://www.w3.org/2000/svg">
                                {{-- Phone frame --}}
                                <rect x="2" y="2" width="256" height="516" rx="38" fill="#0c0a1a" stroke="rgba(255,255,255,0.12)" stroke-width="2"/>
                                {{-- Notch --}}
                                <rect x="90" y="12" width="80" height="22" rx="11" fill="#1a1145"/>
                                {{-- Status bar dots --}}
                                <circle cx="102" cy="23" r="4" fill="rgba(255,255,255,0.15)"/>
                                <circle cx="158" cy="23" r="3" fill="rgba(255,255,255,0.1)"/>

                                {{-- App header bar --}}
                                <rect x="2" y="42" width="256" height="48" fill="rgba(255,255,255,0.03)"/>
                                <circle cx="28" cy="66" r="14" fill="url(#avatarGrad)"/>
                                <text x="22" y="71" font-size="11" fill="white" font-family="sans-serif" font-weight="bold">AB</text>
                                <text x="50" y="63" font-size="11" fill="white" font-family="sans-serif" font-weight="700">🔥 Campus Crush</text>
                                <rect x="210" y="54" width="28" height="24" rx="8" fill="rgba(255,94,108,0.2)"/>
                                <text x="219" y="71" font-size="13" fill="white" font-family="sans-serif">♡</text>

                                {{-- Swipe card --}}
                                <rect x="20" y="102" width="220" height="280" rx="24" fill="url(#cardGrad)"/>
                                {{-- Photo simulée --}}
                                <rect x="20" y="102" width="220" height="200" rx="24" fill="url(#photoGrad)"/>
                                <rect x="20" y="278" width="220" height="104" rx="0" fill="transparent"/>
                                <rect x="20" y="258" width="220" height="124" rx="24" fill="url(#infoGrad)"/>
                                {{-- Nom et infos --}}
                                <text x="36" y="294" font-size="18" fill="white" font-family="sans-serif" font-weight="700">Aïssatou, 21</text>
                                <text x="36" y="314" font-size="11" fill="rgba(255,255,255,0.55)" font-family="sans-serif">📍 UCAD · Lettres</text>
                                {{-- Bio --}}
                                <rect x="36" y="326" width="140" height="8" rx="4" fill="rgba(255,255,255,0.08)"/>
                                {{-- Tags --}}
                                <rect x="36" y="344" width="52" height="18" rx="9" fill="rgba(255,94,108,0.2)"/>
                                <text x="46" y="357" font-size="9" fill="rgba(255,94,108,0.9)" font-family="sans-serif">Musique</text>
                                <rect x="96" y="344" width="48" height="18" rx="9" fill="rgba(168,85,247,0.2)"/>
                                <text x="105" y="357" font-size="9" fill="rgba(168,85,247,0.9)" font-family="sans-serif">Voyage</text>

                                {{-- Like stamp --}}
                                <g transform="rotate(-15, 185, 145)">
                                    <rect x="155" y="120" width="72" height="34" rx="8" fill="transparent" stroke="#4ade80" stroke-width="3"/>
                                    <text x="163" y="143" font-size="16" fill="#4ade80" font-family="sans-serif" font-weight="800">LIKE</text>
                                </g>

                                {{-- Swipe emoji hint --}}
                                <text x="82" y="418" font-size="11" fill="rgba(255,255,255,0.2)" font-family="sans-serif">← passe · like →</text>

                                {{-- Action buttons --}}
                                <circle cx="90" cy="454" r="26" fill="rgba(239,68,68,0.12)" stroke="rgba(239,68,68,0.2)" stroke-width="1.5"/>
                                <text x="80" y="462" font-size="20" font-family="sans-serif">✕</text>

                                <circle cx="170" cy="454" r="30" fill="url(#likeBtn)"/>
                                <text x="158" y="464" font-size="22" font-family="sans-serif">♡</text>

                                {{-- Bottom nav --}}
                                <rect x="20" y="494" width="220" height="18" rx="9" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.07)" stroke-width="1"/>
                                <rect x="28" y="498" width="48" height="10" rx="5" fill="url(#activeNav)"/>
                                <rect x="84" y="498" width="36" height="10" rx="5" fill="rgba(255,255,255,0.06)"/>
                                <rect x="128" y="498" width="36" height="10" rx="5" fill="rgba(255,255,255,0.06)"/>
                                <rect x="172" y="498" width="36" height="10" rx="5" fill="rgba(255,255,255,0.06)"/>

                                {{-- Gradients --}}
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
                                        <stop offset="0%" stop-color="rgba(12,10,26,0)" stop-opacity="0"/>
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
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             STATS — NOUVEAU
        ════════════════════════════════════════════ --}}
        <section class="px-5 py-12">
            <div class="max-w-3xl mx-auto">
                <div class="grid grid-cols-3 gap-4 rounded-3xl p-8 fade-up"
                     style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                    <div class="text-center">
                        <div class="stat-number">{{ $stats['users'] }}</div>
                        <p class="text-xs text-white/30 mt-1 font-medium">Étudiants inscrits</p>
                    </div>
                    <div class="text-center border-x" style="border-color: rgba(255,255,255,0.06);">
                        <div class="stat-number">{{ $stats['matches'] }}</div>
                        <p class="text-xs text-white/30 mt-1 font-medium">Matchs créés</p>
                    </div>
                    <div class="text-center">
                        <div class="stat-number">{{ $stats['univs'] }}</div>
                        <p class="text-xs text-white/30 mt-1 font-medium">Universités</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             COMMENT ÇA MARCHE
        ════════════════════════════════════════════ --}}
        <section class="px-5 py-16">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-center text-3xl md:text-4xl font-bold mb-3">
                    Comment ça <span class="cc-gradient-text">marche</span> ?
                </h2>
                <p class="text-center text-white/30 mb-14 text-sm">Trois étapes, et c'est parti</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach([
                        ['01', '📸', 'Crée ton profil', 'Photo, filière, bio et tes passions — 2 minutes chrono', '#ff5e6c'],
                        ['02', '🔥', 'Swipe & like', 'Découvre des étudiants de ton campus et like ceux qui te plaisent', '#a855f7'],
                        ['03', '💬', 'Match & discute', 'Mutual ? La conversation peut commencer ! Brise la glace.', '#ffc145'],
                    ] as $s)
                    <div class="glass-card rounded-3xl p-7 text-center group">
                        <span class="cc-mono text-xs tracking-wider" style="color: {{ $s[4] }}; opacity: 0.4;">{{ $s[0] }}</span>
                        <div class="text-4xl my-5 group-hover:scale-110 transition-transform duration-300">{{ $s[1] }}</div>
                        <h3 class="text-lg font-bold mb-2">{{ $s[2] }}</h3>
                        <p class="text-sm text-white/30 leading-relaxed">{{ $s[3] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             UNIVERSITÉS — NOUVEAU
        ════════════════════════════════════════════ --}}
        @if($universities->count() > 0)
        <section class="px-5 py-12">
            <div class="max-w-4xl mx-auto">
                <p class="text-center text-xs font-semibold tracking-widest text-white/20 uppercase mb-6">
                    Étudiants de toutes les universités du Sénégal
                </p>
                {{-- Scroll horizontal sur mobile, flex-wrap sur desktop --}}
                <div class="univs-scroll md:flex-wrap md:justify-center md:flex md:overflow-visible">
                    @foreach($universities as $univ)
                    <span class="univ-pill flex-shrink-0">
                        🎓 {{ $univ->short_name }}
                        @if($univ->city)
                        <span class="text-white/25"> · {{ $univ->city }}</span>
                        @endif
                    </span>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- ════════════════════════════════════════════
             AVIS UTILISATEURS
        ════════════════════════════════════════════ --}}
        @if($featuredReviews->count() > 0)
        <section class="px-5 py-16">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-center text-3xl md:text-4xl font-bold mb-3">
                    Ce qu'ils en <span class="cc-gradient-text">pensent</span>
                </h2>
                <p class="text-center text-white/30 mb-14 text-sm">Les étudiants parlent de Campus Crush</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($featuredReviews as $rev)
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex items-center gap-0.5 mb-3">
                            @for($i = 1; $i <= 5; $i++)
                            <span class="text-sm {{ $i <= $rev->rating ? 'text-[#ffc145]' : 'text-white/10' }}">★</span>
                            @endfor
                        </div>
                        <p class="text-sm text-white/50 leading-relaxed mb-4 italic">
                            « {{ Str::limit($rev->comment, 150) }} »
                        </p>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full overflow-hidden ring-1 ring-white/10">
                                <img src="{{ $rev->user->profile?->photo_url ?? 'https://ui-avatars.com/api/?background=1a1145&color=ff5e6c&bold=true&name=' . urlencode(substr($rev->user->name, 0, 2)) }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-white/70">{{ $rev->user->name }}</p>
                                <p class="text-[10px] text-white/25">{{ $rev->user->profile?->university_name ?? 'Étudiant·e' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- ════════════════════════════════════════════
             CTA FINAL
        ════════════════════════════════════════════ --}}
        <section class="px-5 py-16">
            <div class="max-w-xl mx-auto">
                <div class="rounded-3xl p-10 md:p-14 text-center relative overflow-hidden"
                     style="background: linear-gradient(135deg, rgba(255,94,108,0.12), rgba(168,85,247,0.08)); border: 1px solid rgba(255,94,108,0.15);">
                    <div class="absolute top-0 right-0 w-48 h-48 bg-[#ff5e6c] rounded-full blur-[90px] opacity-15 pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 w-40 h-40 bg-[#a855f7] rounded-full blur-[80px] opacity-10 pointer-events-none"></div>

                    <div class="text-5xl mb-5 float">💘</div>
                    <h2 class="text-3xl font-extrabold mb-3 relative z-10">Prêt(e) ?</h2>
                    <p class="text-white/40 mb-3 relative z-10 text-sm">
                        Rejoins {{ $stats['users'] }} étudiants du Sénégal
                    </p>
                    <p class="text-white/20 mb-8 relative z-10 text-xs">
                        Premier mois gratuit · Aucune carte bancaire requise
                    </p>
                    <a href="{{ route('register') }}" class="cc-btn cc-btn-primary text-lg relative z-10">
                        Créer mon compte →
                    </a>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             FOOTER
        ════════════════════════════════════════════ --}}
        <footer class="px-5 py-10 border-t" style="border-color: rgba(255,255,255,0.05);">
            <div class="max-w-4xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold cc-gradient-text">Campus Crush</span>
                </div>
                <div class="flex items-center gap-6 text-xs text-white/20">
                    <a href="{{ route('login') }}" class="hover:text-white/50 transition">Connexion</a>
                    <a href="{{ route('register') }}" class="hover:text-white/50 transition">S'inscrire</a>
                    <a href="/install" class="hover:text-white/50 transition">Installer l'app</a>
                </div>
                <p class="text-xs text-white/20">© {{ date('Y') }} · Fait avec ❤️ pour les étudiants du Sénégal</p>
            </div>
        </footer>
    </div>

    @include('components.pwa-install-banner')
    @include('components.promo-popup')
</body>
</html>