<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Campus Crush - Mon Profil</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); min-height: 100vh; }

        .cc-surface       { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(24px); }
        .cc-surface-raised { background: linear-gradient(135deg,rgba(255,255,255,0.06),rgba(255,255,255,0.02)); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(40px); box-shadow: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.05); }
        .cc-gradient-text { background: linear-gradient(135deg,#ff5e6c,#ff8a5c,#ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cc-mono          { font-family: 'Space Mono', monospace; }

        .photo-ring { position: relative; width: 120px; height: 120px; border-radius: 50%; padding: 3px; background: conic-gradient(from 0deg,#ff5e6c,#ffc145,#a855f7,#ff5e6c); }
        .photo-ring::after { content:''; position:absolute; inset:-6px; border-radius:50%; background:conic-gradient(from 90deg,rgba(255,94,108,.15),rgba(168,85,247,.15),rgba(255,193,69,.15),rgba(255,94,108,.15)); z-index:-1; filter:blur(12px); }

        .orb { position:fixed; border-radius:50%; filter:blur(80px); pointer-events:none; opacity:.1; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:none} }
        .fade-up { animation: fadeUp .5s cubic-bezier(.22,1,.36,1) both; }
        .d1{animation-delay:.08s} .d2{animation-delay:.16s} .d3{animation-delay:.24s} .d4{animation-delay:.32s} .d5{animation-delay:.4s}

        @keyframes countUp { from{opacity:0;transform:scale(.5)} to{opacity:1;transform:scale(1)} }
        .count-up { animation: countUp .4s cubic-bezier(.22,1,.36,1) both; }

        .stat-card { transition: transform .2s ease, box-shadow .2s ease; }
        .stat-card:active { transform: scale(.97); }

        /* ── Feature cards ── */
        .feat-card { transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .feat-card:active { transform: scale(.97); }

        /* ── Shimmer on promo cards ── */
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position:  200% center; }
        }
        .shimmer-btn {
            background-size: 200% auto;
            animation: shimmer 2.5s linear infinite;
        }

        .safe-top { padding-top: max(env(safe-area-inset-top,12px),12px); }
    </style>
</head>

<body class="text-white">
    <div class="orb" style="width:250px;height:250px;background:#ff5e6c;top:-60px;right:-60px;"></div>
    <div class="orb" style="width:200px;height:200px;background:#a855f7;bottom:100px;left:-60px;"></div>

    <div class="min-h-screen w-full overflow-auto pb-28">
    <div class="relative z-10 max-w-md mx-auto px-5">

        {{-- ── HEADER ── --}}
        <div class="flex items-center justify-between safe-top pb-4 fade-up">
            <a href="{{ route('swipe') }}" class="p-2 -ml-2 rounded-xl hover:bg-white/5 active:scale-95 transition">
                <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="flex items-center gap-1.5">
                <span class="text-base">🔥</span>
                <span class="text-sm font-bold cc-gradient-text">Mon Profil</span>
            </div>
            <a href="{{ route('settings') }}" class="p-2 -mr-2 rounded-xl hover:bg-white/5 active:scale-95 transition">
                <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
        </div>

        {{-- ── PHOTO + NOM ── --}}
        <div class="flex flex-col items-center mb-6 fade-up d1">
            {{-- Photo cliquable → aperçu carte --}}
            <div class="photo-ring mb-3 cursor-pointer active:scale-95 transition-transform duration-200"
                 onclick="document.getElementById('card-preview-modal').classList.remove('hidden')">
                <div class="w-full h-full rounded-full overflow-hidden">
                    <img src="{{ $profile->photo_url }}" class="w-full h-full object-cover" alt="{{ e($user->name) }}">
                </div>
            </div>
            <p class="text-[10px] text-white/20 mb-3 flex items-center gap-1 cursor-pointer hover:text-white/40 transition"
               onclick="document.getElementById('card-preview-modal').classList.remove('hidden')">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Voir ma carte
            </p>
            <h1 class="text-2xl font-bold leading-tight">
                {{ e($user->name) }}<span class="text-white/40 font-normal ml-1.5 text-lg">{{ $profile->age }}</span>
            </h1>
            <p class="text-white/35 text-xs mt-1 flex items-center gap-1.5">
                <span>🎓</span>
                {{ $profile->university_name ?? $profile->university ?? 'UGB' }}
                <span class="text-white/15">·</span>
                {{ $profile->gender === 'homme' ? '♂ Homme' : '♀ Femme' }}
            </p>
        </div>

        {{-- ── BADGES UFR / LEVEL ── --}}
        <div class="flex flex-wrap justify-center gap-2 mb-5 fade-up d2">
            <span class="px-3.5 py-1.5 rounded-full text-[11px] font-medium" style="background:rgba(255,94,108,0.08);border:1px solid rgba(255,94,108,0.15);color:#ff8a8a;">📚 {{ $profile->ufr ?? 'N/A' }}</span>
            <span class="px-3.5 py-1.5 rounded-full text-[11px] font-medium" style="background:rgba(168,85,247,0.08);border:1px solid rgba(168,85,247,0.15);color:#c084fc;">🎓 {{ $profile->level }}</span>
            @if($profile->promotion)
            <span class="px-3.5 py-1.5 rounded-full text-[11px] font-medium" style="background:rgba(255,193,69,0.08);border:1px solid rgba(255,193,69,0.15);color:#ffc145;">📋 {{ $profile->promotion }}</span>
            @endif
        </div>

        {{-- ── SCORE COMPLÉTUDE ── --}}
        @php
            $criteria = [
                ['label'=>'Photo',     'done'=>(bool)($profile->photo),                         'weight'=>30, 'cta'=>'Ajoute une photo',       'route'=>route('profile.edit')],
                ['label'=>'Bio',       'done'=>!empty($profile->bio),                            'weight'=>25, 'cta'=>'Écris ta bio',            'route'=>route('profile.edit')],
                ['label'=>'Intérêts',  'done'=>!empty($profile->interests),                      'weight'=>20, 'cta'=>'Ajoute tes intérêts',    'route'=>route('profile.edit')],
                ['label'=>'Université','done'=>!empty($profile->university_id??$profile->university),'weight'=>15,'cta'=>'Ton université',       'route'=>route('profile.edit')],
                ['label'=>'Niveau',    'done'=>!empty($profile->level),                          'weight'=>10, 'cta'=>'Précise ton niveau',      'route'=>route('profile.edit')],
            ];
            $score       = collect($criteria)->where('done',true)->sum('weight');
            $nextMissing = collect($criteria)->firstWhere('done',false);
            $barColor    = $score>=90 ? 'linear-gradient(90deg,#22c55e,#4ade80)' : ($score>=60 ? 'linear-gradient(90deg,#ff5e6c,#ffc145)' : 'linear-gradient(90deg,#ff5e6c,#a855f7)');
            $scoreLabel  = $score>=90 ? ['🏆','Profil complet !','text-green-400'] : ($score>=60 ? ['🔥','Bon profil','text-[#ffc145]'] : ($score>=30 ? ['⚡','À compléter','text-[#ff8a5c]'] : ['🌱','Incomplet','text-white/40']));
        @endphp
        @if($score < 100)
        <div class="mb-5 fade-up d2">
            <div class="cc-surface rounded-2xl p-4">
                <div class="flex items-center justify-between mb-2.5">
                    <div class="flex items-center gap-2">
                        <span>{{ $scoreLabel[0] }}</span>
                        <span class="text-xs font-semibold {{ $scoreLabel[2] }}">{{ $scoreLabel[1] }}</span>
                    </div>
                    <span class="text-xs font-mono font-bold {{ $scoreLabel[2] }}">{{ $score }}%</span>
                </div>
                <div class="w-full h-1.5 rounded-full mb-3" style="background:rgba(255,255,255,0.06);">
                    <div class="h-1.5 rounded-full" style="width:{{ $score }}%; {{ $barColor }};"></div>
                </div>
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @foreach($criteria as $c)
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-medium"
                          style="{{ $c['done'] ? 'background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);color:rgba(74,222,128,0.7);' : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:rgba(255,255,255,0.3);' }}">
                        {{ $c['done'] ? '✓' : '○' }} {{ $c['label'] }}
                    </span>
                    @endforeach
                </div>
                @if($nextMissing)
                <a href="{{ $nextMissing['route'] }}" class="flex items-center justify-between w-full px-4 py-2.5 rounded-xl transition active:scale-95" style="background:rgba(255,94,108,0.08);border:1px solid rgba(255,94,108,0.15);">
                    <div class="flex items-center gap-2">
                        <span class="text-xs">✨</span>
                        <span class="text-xs font-medium text-white/60">{{ $nextMissing['cta'] }}</span>
                        <span class="text-[10px] text-white/25">+{{ $nextMissing['weight'] }}%</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-[#ff5e6c]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif
            </div>
        </div>
        @else
        <div class="mb-5 fade-up d2 flex justify-center">
            <span class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold text-green-400" style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.15);">
                🏆 Profil 100% — Tu maximises tes chances !
            </span>
        </div>
        @endif

        {{-- ── STATS ── --}}
        <div class="grid grid-cols-3 gap-2.5 mb-5 fade-up d2">
            <div class="stat-card cc-surface-raised rounded-2xl p-3.5 text-center">
                <p class="text-xl font-bold cc-mono cc-gradient-text count-up" style="animation-delay:.3s">{{ $matchesCount }}</p>
                <p class="text-[9px] text-white/25 mt-1 uppercase tracking-widest">Matchs</p>
            </div>
            <div class="stat-card cc-surface-raised rounded-2xl p-3.5 text-center">
                <p class="text-xl font-bold cc-mono count-up" style="background:linear-gradient(135deg,#a855f7,#6c5ce7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation-delay:.4s">{{ $likesCount }}</p>
                <p class="text-[9px] text-white/25 mt-1 uppercase tracking-widest">Likes reçus</p>
            </div>
            <div class="stat-card cc-surface-raised rounded-2xl p-3.5 text-center">
                <p class="text-xl count-up" style="animation-delay:.5s">{{ $profile->gender === 'homme' ? '♂️' : '♀️' }}</p>
                <p class="text-[9px] text-white/25 mt-1 uppercase tracking-widest">{{ ucfirst($profile->gender) }}</p>
            </div>
        </div>

        {{-- ── BIO ── --}}
        <div class="cc-surface rounded-2xl p-5 mb-3 fade-up d3">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-[10px] text-white/25 uppercase tracking-widest font-medium">À propos</h2>
                <a href="{{ route('profile.edit') }}" class="text-[10px] text-[#ff5e6c]/60 hover:text-[#ff5e6c] transition">Modifier</a>
            </div>
            <p class="text-[13px] text-white/50 leading-relaxed">
                {{ html_entity_decode($profile->bio ?? 'Aucune bio pour le moment. Ajoute une bio pour te démarquer !', ENT_QUOTES, 'UTF-8') }}
            </p>
        </div>

        {{-- ── INTÉRÊTS ── --}}
        @if($profile->interests)
        <div class="cc-surface rounded-2xl p-5 mb-5 fade-up d3">
            <h2 class="text-[10px] text-white/25 uppercase tracking-widest font-medium mb-3">Centres d'intérêt</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($profile->interests_array as $interest)
                <span class="px-3 py-1.5 rounded-full text-[11px] text-white/45 border border-white/8 transition hover:border-white/15 hover:text-white/60" style="background:rgba(255,255,255,0.03);">
                    {{ e($interest) }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ════════════════════════════════════════
             SECTION FONCTIONNALITÉS PREMIUM
        ════════════════════════════════════════ --}}
        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/20 mb-3 fade-up d3">Fonctionnalités</p>

        <div class="space-y-3 mb-5 fade-up d3">

            {{-- 🚀 BOOST — carte pleine largeur visuellement forte --}}
            <a href="{{ route('boost.index') }}"
               class="feat-card block rounded-2xl p-4 relative overflow-hidden"
               style="{{ $profile->isBoosted()
                   ? 'background: linear-gradient(135deg,rgba(255,193,69,0.18),rgba(255,138,92,0.12)); border:1px solid rgba(255,193,69,0.35);'
                   : 'background: linear-gradient(135deg,rgba(255,193,69,0.10),rgba(255,94,108,0.06)); border:1px solid rgba(255,193,69,0.2);' }}">
                {{-- Orb déco --}}
                <div class="absolute right-0 top-0 w-24 h-24 rounded-full pointer-events-none" style="background:#ffc145;filter:blur(40px);opacity:0.12;transform:translate(30%,-30%);"></div>
                <div class="flex items-center gap-3.5 relative z-10">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#ffc145,#ff8a5c);box-shadow:0 4px 16px rgba(255,193,69,0.35);">
                        <span class="text-xl">🚀</span>
                    </div>
                    <div class="flex-1">
                        @if($profile->isBoosted())
                        <h3 class="text-sm font-bold text-[#ffc145]">Boosté · actif !</h3>
                        <p class="text-[11px] text-white/40 mt-0.5">Jusqu'à {{ $profile->boosted_until->format('H:i') }} — tu es en tête des profils</p>
                        @else
                        <h3 class="text-sm font-bold text-[#ffc145]">Booster mon profil</h3>
                        <p class="text-[11px] text-white/40 mt-0.5">Apparaître en premier · 500 FCFA / 24h</p>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-[#ffc145]/50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            {{-- 🎁 PARRAINAGE — violet bien visible --}}
            <a href="{{ route('referral.index') }}"
               class="feat-card block rounded-2xl p-4 relative overflow-hidden"
               style="background:linear-gradient(135deg,rgba(168,85,247,0.14),rgba(99,102,241,0.08)); border:1px solid rgba(168,85,247,0.3);">
                <div class="absolute right-0 top-0 w-24 h-24 rounded-full pointer-events-none" style="background:#a855f7;filter:blur(40px);opacity:0.15;transform:translate(30%,-30%);"></div>
                <div class="flex items-center gap-3.5 relative z-10">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#a855f7,#6c5ce7);box-shadow:0 4px 16px rgba(168,85,247,0.35);">
                        <span class="text-xl">🎁</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="text-sm font-bold text-[#c084fc]">Parrainer un ami</h3>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(168,85,247,0.2);color:#c084fc;border:1px solid rgba(168,85,247,0.3);">GRATUIT</span>
                        </div>
                        <p class="text-[11px] text-white/40">Toi + ton ami gagnez chacun 7 jours premium</p>
                    </div>
                    <svg class="w-4 h-4 text-[#a855f7]/50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            {{-- 🤖 IA CAMPUS CRUSH — gradient rouge/violet --}}
            <a href="{{ route('ai.index') }}"
               class="feat-card block rounded-2xl p-4 relative overflow-hidden"
               style="background:linear-gradient(135deg,rgba(255,94,108,0.12),rgba(168,85,247,0.10)); border:1px solid rgba(255,94,108,0.25);">
                <div class="absolute right-0 top-0 w-24 h-24 rounded-full pointer-events-none" style="background:#ff5e6c;filter:blur(40px);opacity:0.12;transform:translate(30%,-30%);"></div>
                <div class="flex items-center gap-3.5 relative z-10">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#ff5e6c,#a855f7);box-shadow:0 4px 16px rgba(255,94,108,0.3);">
                        <span class="text-xl">🤖</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="text-sm font-bold text-white/80">IA Campus Crush</h3>
                            @if(!$user->ai_chat_unlocked)
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(255,94,108,0.15);color:#ff8a8a;border:1px solid rgba(255,94,108,0.25);">500 FCFA</span>
                            @else
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(34,197,94,0.12);color:#4ade80;border:1px solid rgba(34,197,94,0.2);">DÉBLOQUÉ</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-white/35">Coach · Match IA · Entraînement drague</p>
                    </div>
                    <svg class="w-4 h-4 text-[#ff5e6c]/40 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            {{-- 👀 CRUSH ANONYME --}}
            <a href="{{ route('crush.index') }}"
               class="feat-card block rounded-2xl p-4 relative overflow-hidden"
               style="background:linear-gradient(135deg,rgba(99,102,241,0.10),rgba(168,85,247,0.07)); border:1px solid rgba(99,102,241,0.2);">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(99,102,241,0.2);border:1px solid rgba(99,102,241,0.3);">
                        <span class="text-xl">👀</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-white/70">Crush anonyme</h3>
                        <p class="text-[11px] text-white/30">Envoie un crush sans te dévoiler</p>
                    </div>
                    <svg class="w-4 h-4 text-white/15 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
        </div>

        {{-- ── LIENS SECONDAIRES ── --}}
        <div class="space-y-2 mb-5 fade-up d4">
            <a href="{{ route('matches') }}" class="cc-surface rounded-2xl p-4 flex items-center gap-3.5 hover:bg-white/[0.06] active:scale-[0.98] transition">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(255,94,108,0.08);border:1px solid rgba(255,94,108,0.1);"><span class="text-lg">💕</span></div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium">Mes matchs</h3>
                    <p class="text-[11px] text-white/25">{{ $matchesCount }} conversation{{ $matchesCount > 1 ? 's' : '' }}</p>
                </div>
                <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('settings') }}" class="cc-surface rounded-2xl p-4 flex items-center gap-3.5 hover:bg-white/[0.06] active:scale-[0.98] transition">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);"><span class="text-lg">⚙️</span></div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium">Paramètres</h3>
                    <p class="text-[11px] text-white/25">Notifications, confidentialité</p>
                </div>
                <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="/safety" class="cc-surface rounded-2xl p-4 flex items-center gap-3.5 hover:bg-white/[0.06] active:scale-[0.98] transition">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.1);"><span class="text-lg">🛡️</span></div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium">Conseils de sécurité</h3>
                    <p class="text-[11px] text-white/25">Protège-toi lors des rencontres</p>
                </div>
                <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- ── STREAK ── --}}
        @if(($user->streak_days ?? 0) > 0)
        <div class="fade-up d4 mb-5">
            <div class="flex items-center justify-between px-4 py-3.5 rounded-2xl" style="background:rgba(255,193,69,0.08);border:1px solid rgba(255,193,69,0.18);">
                <div class="flex items-center gap-2.5">
                    <span class="text-xl">{{ $user->streak_badge ?: '🔥' }}</span>
                    <div>
                        <p class="text-sm font-bold text-white">{{ $user->streak_days }} jour{{ $user->streak_days > 1 ? 's' : '' }} de suite</p>
                        <p class="text-[10px] text-white/35">
                            @if($user->streak_days >= 100) Légendaire 🏆
                            @elseif($user->streak_days >= 30) Habitué ⚡
                            @elseif($user->streak_days >= 7) En feu 🔥
                            @else Continue comme ça !
                            @endif
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-[#ffc145]">Streak actif</p>
                    @if($user->streak_days == 6)<p class="text-[10px] text-white/25">+3j demain 🎁</p>@endif
                    @if($user->streak_days == 29)<p class="text-[10px] text-white/25">+7j demain 🎁</p>@endif
                </div>
            </div>
        </div>
        @endif

        {{-- ── PARTAGE ── --}}
        <div class="fade-up d4 mb-5">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-white/20 mb-3">Partager mon profil</p>
            <div class="grid grid-cols-2 gap-2">
                <button onclick="shareWhatsApp()" class="flex items-center justify-center gap-2 py-3 rounded-xl font-semibold text-sm transition active:scale-95" style="background:rgba(37,211,102,0.10);border:1px solid rgba(37,211,102,0.22);color:#25d366;">
                    <span>💬</span> WhatsApp
                </button>
                <button onclick="shareNative()" class="flex items-center justify-center gap-2 py-3 rounded-xl font-semibold text-sm transition active:scale-95" style="background:rgba(168,85,247,0.08);border:1px solid rgba(168,85,247,0.18);color:#a855f7;">
                    <span>📤</span> Partager
                </button>
            </div>
        </div>

        {{-- ── AVIS ── --}}
        <div class="fade-up d5 mb-5">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-white/20 mb-3">Ton avis compte</p>
            <div class="rounded-2xl p-5 relative overflow-hidden" style="background:linear-gradient(135deg,rgba(255,193,69,0.08),rgba(255,94,108,0.06));border:1px solid rgba(255,193,69,0.15);">
                <div class="absolute right-0 top-0 w-20 h-20 rounded-full pointer-events-none" style="background:#ffc145;filter:blur(35px);opacity:0.1;transform:translate(30%,-30%);"></div>
                <div class="flex items-center gap-2 mb-1 relative z-10">
                    <span class="text-lg">⭐</span>
                    <h3 class="text-sm font-bold text-white/80">Note l'application</h3>
                </div>
                <p class="text-[11px] text-white/30 mb-4 relative z-10">Ton avis aide d'autres étudiants à découvrir Campus Crush</p>
                @include('components.review-form')
            </div>
        </div>

        {{-- ── ACTIONS PRINCIPALES ── --}}
        <div class="space-y-3 fade-up d5 mb-4">
            <a href="{{ route('profile.edit') }}"
               class="block w-full py-3.5 rounded-2xl text-center font-semibold text-white text-sm active:scale-[0.98] transition"
               style="background:linear-gradient(135deg,#ff5e6c,#ff8a5c);box-shadow:0 8px 30px rgba(255,94,108,0.2);">
                ✏️ Modifier mon profil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-3.5 rounded-2xl text-center text-sm font-medium text-white/25 cc-surface hover:bg-white/5 hover:text-red-400/60 active:scale-[0.98] transition">
                    Déconnexion
                </button>
            </form>
        </div>

    </div>
    </div>

    {{-- ── MODAL APERÇU CARTE ── --}}
    <div id="card-preview-modal"
         class="hidden fixed inset-0 z-50 flex flex-col items-center justify-center px-6"
         style="background:rgba(10,8,22,0.92);backdrop-filter:blur(16px);"
         onclick="document.getElementById('card-preview-modal').classList.add('hidden')">
        <p class="text-[11px] font-semibold tracking-widest uppercase text-white/25 mb-5">Comme les autres te voient 👀</p>
        <div class="relative w-72 rounded-[28px] overflow-hidden"
             style="height:420px;box-shadow:0 40px 100px rgba(0,0,0,0.7),0 0 0 1px rgba(255,255,255,0.06);pointer-events:none;"
             onclick="event.stopPropagation()">
            <img src="{{ $profile->photo_url }}" class="absolute inset-0 w-full h-full object-cover" alt="">
            <div class="absolute inset-0" style="background:linear-gradient(to top,rgba(10,8,22,0.97) 0%,rgba(10,8,22,0.5) 45%,transparent 70%);"></div>
            <div class="absolute bottom-0 left-0 right-0 p-5">
                <div class="flex items-baseline gap-2 mb-1">
                    <h2 class="text-2xl font-bold text-white leading-none">{{ e($user->name) }}</h2>
                    <span class="text-lg text-white/55 font-light">{{ $profile->age }}</span>
                </div>
                @if($profile->university_name ?? $profile->university ?? null)
                <p class="text-[11px] text-white/35 mb-2">🎓 {{ $profile->university_name ?? $profile->university }}@if($profile->ufr) · {{ $profile->ufr }}@endif</p>
                @endif
                @if($profile->bio)
                <p class="text-xs text-white/50 leading-relaxed mb-3 line-clamp-2">{{ html_entity_decode($profile->bio, ENT_QUOTES, 'UTF-8') }}</p>
                @endif
                @if($profile->interests)
                <div class="flex flex-wrap gap-1.5">
                    @foreach(array_slice($profile->interests_array, 0, 4) as $interest)
                    <span class="px-2.5 py-1 rounded-full text-[10px] text-white/75 font-medium" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.08);backdrop-filter:blur(8px);">{{ e($interest) }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-5 mt-6">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);"><span class="text-lg">✕</span></div>
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#ff5e6c,#ff8a5c);box-shadow:0 4px 20px rgba(255,94,108,0.4);">
                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </div>
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);"><span class="text-lg opacity-40">⚙️</span></div>
        </div>
        <button onclick="document.getElementById('card-preview-modal').classList.add('hidden')"
                class="mt-6 px-6 py-2.5 rounded-xl text-xs font-medium text-white/30 border border-white/10 hover:bg-white/5 transition active:scale-95">Fermer</button>
        <p class="text-[10px] text-white/15 mt-3">
            Pour modifier → <a href="{{ route('profile.edit') }}" class="text-[#ff5e6c]/50 hover:text-[#ff5e6c] transition" onclick="event.stopPropagation()">Modifier le profil</a>
        </p>
    </div>

    <script>
        const publicUrl   = '{{ route("public.profile", auth()->user()->slug ?? "profil") }}';
        const profileName = '{{ e($user->name) }}';

        function shareWhatsApp() {
            const msg = encodeURIComponent('👋 Salut ! Voici mon profil sur Campus Crush, l\'appli de rencontres pour étudiants 🎓💘\nRejoins-moi : ' + publicUrl);
            window.open('https://wa.me/?text=' + msg, '_blank');
        }
        function shareNative() {
            if (navigator.share) {
                navigator.share({ title: profileName + ' sur Campus Crush', text: '👋 ' + profileName + ' est sur Campus Crush !', url: publicUrl });
            } else {
                navigator.clipboard?.writeText(publicUrl).then(() => alert('Lien copié !')).catch(()=>{});
            }
        }

        @if(session('streak_reward_7'))  showStreakToast('🔥 7 jours de suite ! +3 jours premium offerts 🎁'); @endif
        @if(session('streak_reward_30')) showStreakToast('⚡ 30 jours de suite ! +7 jours premium offerts 🎁'); @endif
        @if(session('streak_reward_100'))showStreakToast('🏆 100 jours de suite ! +30 jours premium offerts 🎁'); @endif

        function showStreakToast(msg) {
            const t = document.createElement('div');
            t.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:99999;padding:14px 24px;border-radius:16px;font-size:13px;font-weight:600;color:#fff;text-align:center;max-width:320px;background:linear-gradient(135deg,rgba(255,193,69,0.95),rgba(255,138,92,0.95));border:1px solid rgba(255,255,255,0.15);backdrop-filter:blur(20px);font-family:Sora,sans-serif;box-shadow:0 8px 30px rgba(255,193,69,0.3);';
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => { t.style.transition='opacity 0.5s'; t.style.opacity='0'; setTimeout(()=>t.remove(),500); }, 4000);
        }
    </script>

    @include('components.feature-reminders')
    @include('components.bottom-nav')
    @auth
    @include('components.ai-chat-fab')
    @endauth
</body>
</html>