<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ e($user->name) }} sur Campus Crush 💘</title>

    {{-- OG Meta Tags pour WhatsApp / Instagram / Facebook --}}
    <meta property="og:type" content="profile">
    <meta property="og:title" content="{{ e($user->name) }}, {{ $profile->age }} ans — Campus Crush">
    <meta property="og:description" content="{{ e($profile->bio ?? 'Étudiant(e) à ' . ($profile->university_name ?? 'une université sénégalaise') . '. Rejoins Campus Crush pour me contacter !') }}">
    <meta property="og:image" content="{{ $profile->photo_url }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Campus Crush">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ e($user->name) }} — Campus Crush 💘">
    <meta name="twitter:description" content="Étudiant(e) {{ $profile->university_name ?? '' }}. Rejoins Campus Crush !">
    <meta name="twitter:image" content="{{ $profile->photo_url }}">

    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); min-height: 100vh; }
        .cc-gradient-text { background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cc-mono { font-family: 'Space Mono', monospace; }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; opacity: 0.12; }

        @keyframes fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }
        .fade-up { animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both; }
        .d1 { animation-delay: .1s; }
        .d2 { animation-delay: .2s; }
        .d3 { animation-delay: .3s; }
        .d4 { animation-delay: .4s; }
        .d5 { animation-delay: .5s; }

        .photo-glow {
            position: relative;
        }
        .photo-glow::after {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, rgba(255,94,108,0.2), rgba(255,193,69,0.2), rgba(168,85,247,0.2), rgba(255,94,108,0.2));
            z-index: -1;
            filter: blur(16px);
        }

        @keyframes pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(255,94,108,0.3); } 50% { box-shadow: 0 0 0 12px rgba(255,94,108,0); } }
        .pulse-btn { animation: pulse 2s ease-in-out infinite; }
    </style>
</head>
<body class="text-white">

    <div class="orb" style="width:300px;height:300px;background:#ff5e6c;top:-80px;right:-80px;"></div>
    <div class="orb" style="width:250px;height:250px;background:#a855f7;bottom:50px;left:-80px;"></div>

    <div class="min-h-screen flex flex-col items-center justify-center px-5 py-10">
        <div class="w-full max-w-sm">

            {{-- Logo --}}
            <div class="text-center mb-8 fade-up">
                <div class="inline-flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #ff5e6c, #ffc145);">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <span class="font-bold cc-gradient-text">Campus Crush</span>
                </div>
            </div>

            {{-- Profile Card --}}
            <div class="rounded-[28px] overflow-hidden fade-up d1" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(24px); box-shadow: 0 20px 60px rgba(0,0,0,0.4);">

                {{-- Photo --}}
                <div class="relative" style="aspect-ratio: 3/3.5;">
                    <img src="{{ $profile->photo_url }}" class="w-full h-full object-cover" alt="{{ e($user->name) }}">
                    <div class="absolute inset-0" style="background: linear-gradient(0deg, rgba(12,10,26,0.95) 0%, rgba(12,10,26,0.4) 40%, transparent 70%);"></div>

                    {{-- Badge --}}
                    @if($profile->badge)
                    <div class="absolute top-4 right-4 px-3 py-1.5 rounded-full text-xs font-semibold" style="background: rgba(255,193,69,0.15); border: 1px solid rgba(255,193,69,0.3); color: #ffc145; backdrop-filter: blur(20px);">
                        {{ $profile->badge === 'queen' ? '👑 Campus Queen' : '🏆 Campus King' }}
                    </div>
                    @endif

                    {{-- Info overlay --}}
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <div class="flex items-baseline gap-2 mb-1">
                            <h1 class="text-3xl font-extrabold">{{ e($user->name) }}</h1>
                            <span class="text-xl text-white/50">{{ $profile->age }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-white/50 text-sm">
                            <span>🎓</span>
                            <span>{{ $profile->university_name ?? 'Université' }}</span>
                            <span class="text-white/15">·</span>
                            <span>{{ $profile->ufr }}</span>
                        </div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="p-6 space-y-4">

                    {{-- Bio --}}
                    @if($profile->bio)
                    <div class="fade-up d2">
                        <p class="text-sm text-white/50 leading-relaxed italic">« {{ e($profile->bio) }} »</p>
                    </div>
                    @endif

                    {{-- Tags --}}
                    @if($profile->interests)
                    <div class="flex flex-wrap gap-2 fade-up d3">
                        @foreach($profile->interests_array as $tag)
                        <span class="px-3 py-1 rounded-full text-[11px] text-white/45 border border-white/8" style="background: rgba(255,255,255,0.03);">
                            {{ e($tag) }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Info pills --}}
                    <div class="flex flex-wrap gap-2 fade-up d3">
                        <span class="px-3 py-1.5 rounded-full text-[11px] font-medium" style="background: rgba(255,94,108,0.08); border: 1px solid rgba(255,94,108,0.15); color: #ff8a8a;">
                            📚 {{ $profile->ufr }}
                        </span>
                        <span class="px-3 py-1.5 rounded-full text-[11px] font-medium" style="background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.15); color: #c084fc;">
                            🎓 {{ $profile->level }}
                        </span>
                        @if($profile->promotion)
                        <span class="px-3 py-1.5 rounded-full text-[11px] font-medium" style="background: rgba(255,193,69,0.08); border: 1px solid rgba(255,193,69,0.15); color: #ffc145;">
                            📋 Promo {{ $profile->promotion }}
                        </span>
                        @endif
                    </div>

                    {{-- Stats --}}
                    <div class="grid grid-cols-2 gap-3 fade-up d4">
                        <div class="text-center py-3 rounded-xl" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                            <p class="text-lg font-bold cc-mono cc-gradient-text">{{ $matchesCount }}</p>
                            <p class="text-[9px] text-white/20 uppercase tracking-widest">Matchs</p>
                        </div>
                        <div class="text-center py-3 rounded-xl" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                            <p class="text-lg font-bold cc-mono" style="color: #a855f7;">{{ $likesCount }}</p>
                            <p class="text-[9px] text-white/20 uppercase tracking-widest">Likes</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CTA --}}
            <div class="mt-6 space-y-3 fade-up d5">
                <a href="{{ route('register') }}" class="pulse-btn block w-full py-4 rounded-2xl text-center font-bold text-white text-base transition hover:-translate-y-1 active:scale-[0.98]" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                    Rejoindre Campus Crush pour me contacter 💘
                </a>

                <a href="{{ route('login') }}" class="block w-full py-3.5 rounded-2xl text-center text-sm font-medium text-white/35 transition hover:text-white/50" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                    Déjà inscrit ? Se connecter
                </a>
            </div>

            {{-- Footer --}}
            <p class="text-center text-[10px] text-white/15 mt-8">
                Campus Crush · Rencontres universitaires au Sénégal 🇸🇳
            </p>
        </div>
    </div>
</body>
</html>
