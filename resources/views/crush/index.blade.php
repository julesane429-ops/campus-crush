<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Campus Crush - Crush Anonyme</title>
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
        .safe-top { padding-top: max(env(safe-area-inset-top, 12px), 12px); }
        @keyframes heartFloat { 0%,100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-8px) scale(1.1); } }
        .heart-float { animation: heartFloat 2s ease-in-out infinite; }
    </style>
</head>
<body class="text-white">

    <div class="orb" style="width:250px;height:250px;background:#a855f7;top:-60px;left:-60px;"></div>
    <div class="orb" style="width:200px;height:200px;background:#ff5e6c;bottom:100px;right:-60px;"></div>

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
                    <span class="text-base">👀</span>
                    <span class="text-sm font-bold cc-gradient-text">Crush Anonyme</span>
                </div>
                <div class="w-9"></div>
            </div>

            {{-- Hero --}}
            <div class="cc-surface rounded-2xl p-6 mb-6 text-center fade-up" style="animation-delay:0.08s">
                <div class="text-4xl mb-3 heart-float">💘</div>
                <h2 class="text-lg font-bold mb-1">Envoie un crush anonyme</h2>
                <p class="text-xs text-white/30 leading-relaxed max-w-[280px] mx-auto">
                    La personne recevra "Quelqu'un de ton université a un crush sur toi" sans savoir que c'est toi 👀
                </p>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-xs">
                ✅ {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs">
                ❌ {{ session('error') }}
            </div>
            @endif

            {{-- Send form --}}
            <div class="cc-surface rounded-2xl p-5 mb-6 fade-up" style="animation-delay:0.15s">
                <h3 class="text-[10px] text-white/25 uppercase tracking-widest font-medium mb-4">Envoyer un crush</h3>

                <form method="POST" action="{{ route('crush.send') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="text-xs text-white/40 mb-1.5 block">Email ou numéro de la personne</label>
                        <input type="text" name="target" placeholder="email@univ.sn ou 77XXXXXXX"
                            value="{{ old('target') }}"
                            class="w-full px-4 py-3 bg-white/[0.04] rounded-xl text-sm text-white placeholder-white/20 outline-none border border-white/[0.06] focus:border-[#ff5e6c]/40 transition" required>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs text-white/40 mb-1.5 block">Message (optionnel)</label>
                        <input type="text" name="message" placeholder="Un indice ? Un petit mot doux ? 😏"
                            value="{{ old('message') }}" maxlength="200"
                            class="w-full px-4 py-3 bg-white/[0.04] rounded-xl text-sm text-white placeholder-white/20 outline-none border border-white/[0.06] focus:border-[#ff5e6c]/40 transition">
                        <p class="text-[10px] text-white/15 mt-1">Max 200 caractères · La personne ne saura pas qui c'est</p>
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-2xl font-semibold text-white text-sm active:scale-[0.98] transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.2);">
                        💘 Envoyer le crush anonyme
                    </button>

                    <p class="text-[10px] text-white/15 text-center mt-2 cc-mono">{{ 5 - \App\Models\AnonymousCrush::where('sender_id', auth()->id())->whereDate('created_at', today())->count() }} crushes restants aujourd'hui</p>
                </form>
            </div>

            {{-- Received crushes --}}
            @if($receivedCrushes->count() > 0)
            <div class="mb-6 fade-up" style="animation-delay:0.22s">
                <h3 class="text-[10px] text-white/25 uppercase tracking-widest font-medium mb-3 flex items-center justify-between">
                    <span>Crushes reçus</span>
                    <span class="text-white/30 cc-mono">{{ $receivedCrushes->count() }}</span>
                </h3>

                <div class="space-y-3">
                    @foreach($receivedCrushes as $crush)
                    <div class="cc-surface rounded-2xl p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(255,94,108,0.1); border: 1px solid rgba(255,94,108,0.15);">
                                @if($crush->is_revealed)
                                <img src="{{ $crush->sender->profile?->photo_url ?? '' }}" class="w-full h-full rounded-full object-cover">
                                @else
                                <span class="text-lg">👤</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($crush->is_revealed)
                                <p class="text-sm font-medium text-white/70">{{ $crush->sender->name }}</p>
                                <p class="text-[10px] text-white/30">{{ $crush->sender->profile?->university_name ?? '' }} · {{ $crush->sender->profile?->ufr ?? '' }}</p>
                                @else
                                <p class="text-sm font-medium text-white/70">Quelqu'un de {{ $crush->sender_university }}</p>
                                <p class="text-[10px] text-white/30">a un crush sur toi 👀</p>
                                @endif

                                @if($crush->message)
                                <p class="text-xs text-white/40 mt-2 italic px-3 py-2 rounded-xl" style="background: rgba(255,255,255,0.03);">
                                    « {{ e($crush->message) }} »
                                </p>
                                @endif

                                <p class="text-[9px] text-white/15 mt-1.5 cc-mono">{{ $crush->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        @if(!$crush->is_revealed)
                        <form method="POST" action="{{ route('crush.reveal', $crush->id) }}" class="mt-3 pt-3 border-t border-white/5">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-semibold text-white active:scale-95 transition" style="background: linear-gradient(135deg, #a855f7, #6c5ce7);">
                                👁 Révéler qui c'est
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Sent crushes --}}
            @if($sentCrushes->count() > 0)
            <div class="fade-up" style="animation-delay:0.28s">
                <h3 class="text-[10px] text-white/25 uppercase tracking-widest font-medium mb-3">Crushes envoyés</h3>
                <div class="space-y-2">
                    @foreach($sentCrushes as $crush)
                    <div class="cc-surface rounded-xl px-4 py-3 flex items-center gap-3">
                        <span class="text-base">💌</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-white/50 truncate">{{ $crush->target_identifier }}</p>
                            @if($crush->message)
                            <p class="text-[10px] text-white/20 truncate">{{ $crush->message }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            @if($crush->target_user_id)
                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-green-500/10 text-green-400">Inscrit(e)</span>
                            @else
                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-white/5 text-white/20">Pas inscrit(e)</span>
                            @endif
                            <span class="text-[9px] text-white/15 cc-mono">{{ $crush->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Share CTA --}}
            <div class="mt-6 cc-surface rounded-2xl p-5 text-center fade-up" style="animation-delay:0.32s">
                <p class="text-xs text-white/30 mb-3">Ta cible n'est pas encore inscrite ?</p>
                <button onclick="shareInvite()" class="px-6 py-2.5 rounded-xl text-xs font-semibold text-white active:scale-95 transition" style="background: rgba(37,211,102,0.15); border: 1px solid rgba(37,211,102,0.25); color: #25d366;">
                    💬 Inviter via WhatsApp
                </button>
            </div>
        </div>
    </div>

    @include('components.bottom-nav')

    <script>
    function shareInvite() {
        const msg = encodeURIComponent(
            '👀 Quelqu\'un a un crush sur toi sur Campus Crush !\n' +
            '💘 Inscris-toi pour découvrir qui c\'est :\n' +
            'https://campus-crush-h9df.onrender.com'
        );
        window.open('https://wa.me/?text=' + msg, '_blank');
    }
    </script>
     @auth
    @include('components.ai-chat-fab')
    @endauth
</body>
</html>
