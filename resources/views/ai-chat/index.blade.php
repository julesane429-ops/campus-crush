<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Campus Crush - IA</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); min-height: 100vh; }
        .cc-surface { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(24px); }
        .cc-gradient-text { background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; opacity: 0.1; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }
        .fade-up { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; }
        .safe-top { padding-top: max(env(safe-area-inset-top, 12px), 12px); }
        .bot-card { transition: transform 0.2s, box-shadow 0.2s; }
        .bot-card:active { transform: scale(0.97); }
    </style>
</head>
<body class="text-white">
    <div class="orb" style="width:250px;height:250px;background:#a855f7;top:-60px;right:-60px;"></div>
    <div class="orb" style="width:200px;height:200px;background:#ff5e6c;bottom:100px;left:-60px;"></div>

    <div class="min-h-screen w-full pb-28">
        <div class="relative z-10 max-w-md mx-auto px-5">

            {{-- Header --}}
            <div class="flex items-center justify-between safe-top pb-4 fade-up">
                <a href="{{ route('swipe') }}" class="p-2 -ml-2 rounded-xl hover:bg-white/5 active:scale-95 transition">
                    <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="flex items-center gap-1.5">
                    <span class="text-base">🤖</span>
                    <span class="text-sm font-bold cc-gradient-text">IA Campus Crush</span>
                </div>
                <div class="w-9"></div>
            </div>

            {{-- Flash --}}
            @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-xs fade-up">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs fade-up">❌ {{ session('error') }}</div>
            @endif

            {{-- Unlock banner --}}
            @if(!$isUnlocked)
            <div class="cc-surface rounded-2xl p-5 mb-6 text-center fade-up" style="background: linear-gradient(135deg, rgba(168,85,247,0.08), rgba(255,94,108,0.08)); border-color: rgba(168,85,247,0.15);">
                <div class="text-3xl mb-2">🔒</div>
                <h2 class="text-lg font-bold mb-1">Débloque l'IA Campus Crush</h2>
                <p class="text-xs text-white/35 mb-4 max-w-[260px] mx-auto">Accède à l'IA Match, au Coach Profil et à l'Entraînement Drague</p>
                <a href="{{ route('ai.unlock') }}" class="inline-block px-6 py-3 rounded-2xl text-sm font-bold text-white active:scale-95 transition" style="background: linear-gradient(135deg, #a855f7, #ff5e6c); box-shadow: 0 8px 30px rgba(168,85,247,0.3);">
                    Débloquer — 500 FCFA
                </a>
                <p class="text-[10px] text-white/20 mt-2">Paiement unique · Accès permanent</p>
            </div>
            @endif

            {{-- Bot list --}}
            <div class="space-y-3">
                @foreach($bots as $type => $bot)
                @php
                    $isLocked = !$bot['free'] && !$isUnlocked;
                    $session = $sessions->get($type);
                    $lastMsg = $session?->messages?->first();
                @endphp
                <a href="{{ $isLocked ? route('ai.unlock') : route('ai.session', $type) }}"
                   class="bot-card cc-surface rounded-2xl p-4 flex items-center gap-4 hover:bg-white/[0.06] transition block fade-up" style="animation-delay: {{ $loop->index * 0.06 }}s; {{ $isLocked ? 'opacity: 0.5;' : '' }}">

                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 text-2xl" style="background: {{ $bot['color'] }}15; border: 1px solid {{ $bot['color'] }}25;">
                        {{ $bot['avatar'] }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold">{{ $bot['name'] }}</h3>
                            @if($isLocked)
                            <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-white/5 text-white/25">🔒 500F</span>
                            @endif
                            @if($bot['free'])
                            <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-green-500/10 text-green-400">Gratuit</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-white/30 truncate mt-0.5">
                            @if($lastMsg)
                            {{ Str::limit($lastMsg->content, 50) }}
                            @else
                            {{ $bot['description'] }}
                            @endif
                        </p>
                    </div>

                    <svg class="w-4 h-4 text-white/15 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endforeach
            </div>

            {{-- Info --}}
            <div class="mt-6 px-4 py-3 rounded-xl text-center fade-up" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04);">
                <p class="text-[10px] text-white/20">🤖 Les conversations IA sont générées par intelligence artificielle.<br>Les personnages ne sont pas de vraies personnes.</p>
            </div>
        </div>
    </div>

    @include('components.bottom-nav')
</body>
</html>
