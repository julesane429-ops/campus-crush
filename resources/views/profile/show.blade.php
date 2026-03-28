<!doctype html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Campus Crush - Mon Profil</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); }
        .cc-surface { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(24px); }
        .cc-surface-raised { background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02)); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(40px); box-shadow: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.05); }
        .cc-gradient-text { background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cc-mono { font-family: 'Space Mono', monospace; }

        .photo-ring {
            background: conic-gradient(from 0deg, #ff5e6c, #ffc145, #a855f7, #ff5e6c);
            padding: 3px;
            border-radius: 50%;
            animation: ringRotate 4s linear infinite;
        }
        @keyframes ringRotate { to { filter: hue-rotate(360deg); } }

        @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:none; } }
        .fade-up { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; }
        .d1 { animation-delay: .1s; } .d2 { animation-delay: .2s; } .d3 { animation-delay: .3s; } .d4 { animation-delay: .4s; }
    </style>
</head>

<body class="h-full text-white">
<div class="min-h-full w-full overflow-auto">

    {{-- Orbs --}}
    <div class="fixed top-10 right-0 w-60 h-60 bg-[#ff5e6c] rounded-full blur-[120px] opacity-10 pointer-events-none"></div>
    <div class="fixed bottom-40 -left-20 w-60 h-60 bg-purple-600 rounded-full blur-[120px] opacity-10 pointer-events-none"></div>

    <div class="relative z-10 max-w-md mx-auto px-5 py-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8 fade-up">
            <div class="flex items-center gap-2">
                <span class="text-lg">🔥</span>
                <span class="font-bold cc-gradient-text">Campus Crush</span>
            </div>
            <a href="{{ route('settings') }}" class="w-9 h-9 rounded-xl cc-surface flex items-center justify-center hover:bg-white/10 transition">
                <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
        </div>

        {{-- Photo + Name --}}
        <div class="flex flex-col items-center mb-8 fade-up d1">
            <div class="photo-ring mb-5">
                <div class="w-28 h-28 rounded-full overflow-hidden">
                    <img src="{{ $profile->photo_url }}" class="w-full h-full object-cover" alt="">
                </div>
            </div>
            <h1 class="text-2xl font-bold mb-0.5">{{ e($user->name) }}<span class="text-white/50 font-normal ml-2">{{ $profile->age }}</span></h1>
            <p class="text-white/40 text-sm">{{ $profile->university ?? 'UGB' }} · {{ $profile->gender === 'homme' ? '♂' : '♀' }}</p>
        </div>

        {{-- Badges --}}
        <div class="flex flex-wrap justify-center gap-2 mb-6 fade-up d2">
            <span class="px-4 py-2 rounded-full text-xs font-medium" style="background: rgba(255,94,108,0.1); border: 1px solid rgba(255,94,108,0.2); color: #ff8a8a;">
                📚 {{ $profile->ufr ?? 'N/A' }}
            </span>
            <span class="px-4 py-2 rounded-full text-xs font-medium" style="background: rgba(168,85,247,0.1); border: 1px solid rgba(168,85,247,0.2); color: #c084fc;">
                🎓 {{ $profile->level }}
            </span>
            @if($profile->promotion)
            <span class="px-4 py-2 rounded-full text-xs font-medium" style="background: rgba(255,193,69,0.1); border: 1px solid rgba(255,193,69,0.2); color: #ffc145;">
                📋 {{ $profile->promotion }}
            </span>
            @endif
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3 mb-6 fade-up d2">
            <div class="cc-surface-raised rounded-2xl p-4 text-center">
                <p class="text-2xl font-bold cc-mono cc-gradient-text">{{ $matchesCount }}</p>
                <p class="text-[10px] text-white/30 mt-1 uppercase tracking-wider">Matchs</p>
            </div>
            <div class="cc-surface-raised rounded-2xl p-4 text-center">
                <p class="text-2xl font-bold cc-mono" style="background: linear-gradient(135deg, #a855f7, #6c5ce7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $likesCount }}</p>
                <p class="text-[10px] text-white/30 mt-1 uppercase tracking-wider">Likes</p>
            </div>
            <div class="cc-surface-raised rounded-2xl p-4 text-center">
                <p class="text-2xl">{{ $profile->gender === 'homme' ? '♂️' : '♀️' }}</p>
                <p class="text-[10px] text-white/30 mt-1 uppercase tracking-wider">{{ ucfirst($profile->gender) }}</p>
            </div>
        </div>

        {{-- Bio --}}
        <div class="cc-surface rounded-2xl p-5 mb-4 fade-up d3">
            <h2 class="text-xs text-white/30 uppercase tracking-widest mb-3">À propos</h2>
            <p class="text-sm text-white/60 leading-relaxed">{{ e($profile->bio ?? 'Aucune bio.') }}</p>
        </div>

        {{-- Interests --}}
        @if($profile->interests)
        <div class="cc-surface rounded-2xl p-5 mb-6 fade-up d3">
            <h2 class="text-xs text-white/30 uppercase tracking-widest mb-3">Centres d'intérêt</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($profile->interests_array as $interest)
                <span class="px-3 py-1.5 rounded-full text-xs bg-white/5 text-white/50 border border-white/10">{{ e($interest) }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="space-y-3 fade-up d4">
            <a href="{{ route('profile.edit') }}" class="block w-full py-4 rounded-2xl text-center font-semibold text-white transition hover:-translate-y-0.5" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.25);">
                ✏️ Modifier profil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-4 rounded-2xl text-center font-medium text-red-300/60 cc-surface hover:bg-white/5 transition">
                    Déconnexion
                </button>
            </form>
        </div>

        @include('components.bottom-nav')
    </div>
</div>
</body>
</html>
