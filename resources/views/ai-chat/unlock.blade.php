<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Débloquer l'IA — Campus Crush</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); min-height: 100vh; color: #fff; }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; opacity: 0.10; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }
        .fade-up { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; }
        .method-btn { background: rgba(255,255,255,0.04); border: 2px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 14px 16px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 12px; }
        .method-btn:hover { border-color: rgba(168,85,247,0.4); background: rgba(168,85,247,0.06); }
        .method-btn.selected { border-color: #a855f7; background: rgba(168,85,247,0.10); }
        .method-btn input[type=radio] { display: none; }
        .cc-input { width:100%; padding:14px 16px; border-radius:14px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.10); color:#fff; font-size:15px; font-family:'Sora',sans-serif; outline:none; transition:border-color 0.2s; }
        .cc-input:focus { border-color: rgba(168,85,247,0.5); }
        .cc-input::placeholder { color: rgba(255,255,255,0.25); }
    </style>
</head>
<body>
    <div class="orb" style="width:260px;height:260px;background:#a855f7;top:-80px;right:-60px;"></div>
    <div class="orb" style="width:200px;height:200px;background:#ff5e6c;bottom:-60px;left:-60px;"></div>

    <div class="max-w-md mx-auto px-4 pb-10" style="padding-top: max(env(safe-area-inset-top,16px), 16px);">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6 fade-up">
            <a href="{{ route('ai.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08);">
                <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-lg font-bold">Débloquer l'IA</h1>
        </div>

        {{-- Hero --}}
        <div class="rounded-2xl p-6 mb-6 fade-up text-center" style="background: linear-gradient(135deg, rgba(168,85,247,0.08), rgba(255,94,108,0.08)); border: 1px solid rgba(168,85,247,0.18);">
            <div class="text-5xl mb-3">🤖</div>
            <h2 class="text-xl font-extrabold mb-1">IA Campus Crush</h2>
            <p class="text-sm text-white/40 mb-4">4 assistants IA pour t'aider à matcher</p>
            <div class="text-3xl font-extrabold" style="background:linear-gradient(135deg,#a855f7,#ff5e6c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">500 FCFA</div>
            <p class="text-[10px] text-white/25 mt-1">Paiement unique · Accès permanent</p>
        </div>

        {{-- Features --}}
        <div class="space-y-3 mb-6 fade-up" style="animation-delay:0.1s">
            @foreach([
                ['👩🏾', 'AI Campus Girl / Boy', 'Discute avec Aïda ou Moussa comme un vrai match', '#ff5e6c'],
                ['🎯', 'Coach Profil', 'Analyse ton profil et donne des conseils pour plus de matchs', '#ffc145'],
                ['💬', 'Entraînement Drague', 'Pratique tes conversations avec feedback en temps réel', '#a855f7'],
                ['🤖', 'Support 24/7', 'Aide sur le paiement, le swipe, les matchs (GRATUIT)', '#3b82f6'],
            ] as [$icon, $title, $desc, $color])
            <div class="flex items-start gap-3 px-4 py-3 rounded-xl" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-lg" style="background: {{ $color }}15;">{{ $icon }}</div>
                <div>
                    <p class="text-xs font-semibold text-white/80">{{ $title }}</p>
                    <p class="text-[11px] text-white/35">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Payment form --}}
        <form action="{{ route('ai.pay') }}" method="POST" class="space-y-4 fade-up" style="animation-delay:0.2s">
            @csrf

            <div>
                <p class="text-xs font-semibold mb-3" style="color:rgba(255,255,255,0.40); letter-spacing:0.06em;">MOYEN DE PAIEMENT</p>
                <div class="space-y-2">
                    @php $waveEnabled = (bool) config('paydunya.wave_enabled', true); @endphp
                    @foreach([['orange_money','🟠','Orange Money'],['wave','🔵','Wave'],['free_money','🟢','Free Money']] as [$val,$icon,$label])
                    @if($val === 'wave' && !$waveEnabled)
                        @continue
                    @endif
                    <label class="method-btn" onclick="selectMethod(this,'{{ $val }}')">
                        <input type="radio" name="payment_method" value="{{ $val }}" required {{ old('payment_method', 'orange_money') === $val ? 'checked' : '' }}>
                        <span class="text-xl">{{ $icon }}</span>
                        <span class="font-medium text-sm text-white">{{ $label }}</span>
                        <span class="ml-auto text-xs" style="color:rgba(255,255,255,0.30);">500 FCFA</span>
                    </label>
                    @endforeach
                </div>
                @unless($waveEnabled)
                <p class="text-[11px] text-yellow-300/70 mt-2">Wave est temporairement indisponible via PayDunya. Orange Money fonctionne.</p>
                @endunless
            </div>

            <div>
                <p class="text-xs font-semibold mb-2" style="color:rgba(255,255,255,0.40); letter-spacing:0.06em;">NUMÉRO DE TÉLÉPHONE</p>
                <input type="tel" name="phone_number" placeholder="77 XXX XX XX" class="cc-input" maxlength="9" inputmode="numeric">
            </div>

            {{-- Guide --}}
            <div class="rounded-2xl p-4" style="background: rgba(59,130,246,0.06); border: 1px solid rgba(59,130,246,0.15);">
                <p class="text-xs font-semibold text-blue-400 mb-3">📋 Comment payer :</p>
                <div class="space-y-2.5">
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold" style="background:rgba(59,130,246,0.15); color:#60a5fa;">1</span>
                        <p class="text-[11px] text-white/45">Choisis ton moyen de paiement et entre ton numéro</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold" style="background:rgba(59,130,246,0.15); color:#60a5fa;">2</span>
                        <p class="text-[11px] text-white/45">Sur PayDunya, <strong class="text-white/60">remplis nom + email + numéro</strong> puis clique sur ton opérateur</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold" style="background:rgba(59,130,246,0.15); color:#60a5fa;">3</span>
                        <p class="text-[11px] text-white/45"><strong class="text-white/60">Confirme sur ton téléphone</strong> — l'IA s'active automatiquement ✅</p>
                    </div>
                </div>
            </div>

            @if(session('error'))
            <div class="rounded-xl px-4 py-3 text-sm" style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.2); color:#fca5a5;">{{ session('error') }}</div>
            @endif

            <button type="submit" class="w-full py-4 rounded-2xl font-bold text-white text-base transition active:scale-[0.98]" style="background:linear-gradient(135deg,#a855f7,#ff5e6c); box-shadow:0 8px 30px rgba(168,85,247,0.25);">
                🤖 Débloquer l'IA — 500 FCFA
            </button>
        </form>
    </div>

    @include('components.bottom-nav')

    <script>
    function selectMethod(el, val) {
        document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');
        el.querySelector('input[type=radio]').checked = true;
    }
    document.querySelectorAll('.method-btn input[type=radio]').forEach(r => {
        if (r.checked) r.closest('.method-btn').classList.add('selected');
    });
    </script>
</body>
</html>
