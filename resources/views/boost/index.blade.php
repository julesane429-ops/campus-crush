{{-- resources/views/boost/index.blade.php --}}
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Booster mon profil · Campus Crush</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Sora', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
            min-height: 100vh;
            color: #fff;
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            opacity: 0.10;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .fade-up {
            animation: fadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .d1 {
            animation-delay: .08s
        }

        .d2 {
            animation-delay: .16s
        }

        .d3 {
            animation-delay: .24s
        }

        @keyframes pulse-boost {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(255, 193, 69, 0.4);
            }

            50% {
                box-shadow: 0 0 0 12px rgba(255, 193, 69, 0);
            }
        }

        .boost-pulse {
            animation: pulse-boost 2s ease-in-out infinite;
        }

        .method-btn {
            background: rgba(255, 255, 255, 0.04);
            border: 2px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 14px 16px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .method-btn:hover {
            border-color: rgba(255, 193, 69, 0.4);
            background: rgba(255, 193, 69, 0.06);
        }

        .method-btn.selected {
            border-color: #ffc145;
            background: rgba(255, 193, 69, 0.10);
        }

        .method-btn input[type=radio] {
            display: none;
        }

        .cc-input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            color: #fff;
            font-size: 15px;
            font-family: 'Sora', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }

        .cc-input:focus {
            border-color: rgba(255, 193, 69, 0.5);
        }

        .cc-input::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }
    </style>
</head>

<body>
    <div class="orb" style="width:260px;height:260px;background:#ffc145;top:-80px;right:-60px;"></div>
    <div class="orb" style="width:200px;height:200px;background:#ff5e6c;bottom:-60px;left:-60px;"></div>

    <div class="max-w-md mx-auto px-4 pb-10" style="padding-top: max(env(safe-area-inset-top,16px), 16px);">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6 fade-up">
            <a href="{{ route('profile.show') }}" class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08);">
                <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-bold">Booster mon profil</h1>
        </div>

        @if($isBoosted)
        {{-- Déjà boosté --}}
        <div class="rounded-2xl p-6 text-center mb-6 fade-up" style="background:rgba(255,193,69,0.08); border:1px solid rgba(255,193,69,0.20);">
            <div class="text-4xl mb-3 boost-pulse inline-block">🚀</div>
            <h2 class="text-lg font-bold text-white mb-1">Ton profil est boosté !</h2>
            <p class="text-sm" style="color:rgba(255,255,255,0.45);">
                Actif jusqu'au {{ $boostedUntil->format('d/m/Y à H:i') }}
            </p>
        </div>
        <a href="{{ route('swipe') }}" class="block w-full py-4 rounded-2xl text-center font-semibold text-white fade-up d1" style="background:linear-gradient(135deg,#ff5e6c,#ff8a5c);">
            Retour au swipe →
        </a>

        @else
        {{-- Hero boost --}}
        <div class="rounded-2xl p-6 mb-6 fade-up" style="background:rgba(255,193,69,0.07); border:1px solid rgba(255,193,69,0.18);">
            <div class="flex items-center gap-4 mb-4">
                <div class="text-5xl boost-pulse">🚀</div>
                <div>
                    <h2 class="text-xl font-extrabold text-white leading-tight">Boost 24h</h2>
                    <p class="text-sm mt-0.5" style="color:rgba(255,255,255,0.45);">Apparais en tête du swipe</p>
                </div>
                <div class="ml-auto text-right">
                    <p class="text-2xl font-extrabold" style="background:linear-gradient(135deg,#ffc145,#ff8a5c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">500</p>
                    <p class="text-xs" style="color:rgba(255,255,255,0.35);">FCFA</p>
                </div>
            </div>

            <div class="space-y-2">
                @foreach(['Ton profil vu en premier par tous les utilisateurs', 'Jusqu\'à 10× plus de vues en 24h', 'Badge 🚀 visible sur ta carte', 'Actif immédiatement après paiement'] as $benefit)
                <div class="flex items-center gap-2.5">
                    <span class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0 text-[10px]" style="background:rgba(255,193,69,0.2); color:#ffc145;">✓</span>
                    <span class="text-xs" style="color:rgba(255,255,255,0.55);">{{ $benefit }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Formulaire --}}
        <form action="{{ route('boost.pay') }}" method="POST" class="space-y-4 fade-up d1">
            @csrf

            <div>
                <p class="text-xs font-semibold mb-3" style="color:rgba(255,255,255,0.40); letter-spacing:0.06em;">MOYEN DE PAIEMENT</p>
                <div class="space-y-2" id="methods">
                    @foreach([
                    ['orange_money', '🟠', 'Orange Money'],
                    ['wave', '🔵', 'Wave'],
                    ['free_money', '🟢', 'Free Money'],
                    ] as [$val, $icon, $label])
                    <label class="method-btn" onclick="selectMethod(this, '{{ $val }}')">
                        <input type="radio" name="payment_method" value="{{ $val }}" {{ old('payment_method') === $val ? 'checked' : '' }}>
                        <span class="text-xl">{{ $icon }}</span>
                        <span class="font-medium text-sm text-white">{{ $label }}</span>
                        <span class="ml-auto text-xs" style="color:rgba(255,255,255,0.30);">500 FCFA</span>
                    </label>
                    @endforeach
                </div>
                @error('payment_method')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <p class="text-xs font-semibold mb-2" style="color:rgba(255,255,255,0.40); letter-spacing:0.06em;">NUMÉRO DE TÉLÉPHONE</p>
                <input type="tel" name="phone_number" value="{{ old('phone_number') }}"
                    placeholder="77 XXX XX XX"
                    class="cc-input" maxlength="9" inputmode="numeric">
                @error('phone_number')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if(session('error'))
            <div class="rounded-xl px-4 py-3 text-sm" style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.2); color:#fca5a5;">
                {{ session('error') }}
            </div>
            @endif

            {{-- Guide paiement --}}
            <div class="rounded-2xl p-4 mb-4" style="background: rgba(59,130,246,0.06); border: 1px solid rgba(59,130,246,0.15);">
                <p class="text-xs font-semibold text-blue-400 mb-3">📋 Comment payer en 3 étapes :</p>
                <div class="space-y-2.5">
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold" style="background:rgba(59,130,246,0.15); color:#60a5fa;">1</span>
                        <p class="text-[11px] text-white/45 leading-relaxed">Choisis Orange Money, Wave ou Free Money ci-dessus et entre ton numéro</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold" style="background:rgba(59,130,246,0.15); color:#60a5fa;">2</span>
                        <p class="text-[11px] text-white/45 leading-relaxed">Tu seras redirigé vers PayDunya — <strong class="text-white/60">clique sur le logo de ton opérateur</strong> puis remplis ton nom, email et numéro</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold" style="background:rgba(59,130,246,0.15); color:#60a5fa;">3</span>
                        <p class="text-[11px] text-white/45 leading-relaxed"><strong class="text-white/60">Confirme sur ton téléphone</strong> — tu recevras un pop-up ou un code USSD à valider. Le boost s'active automatiquement ✅</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-4 rounded-2xl font-bold text-white text-base transition active:scale-[0.98]" style="background:linear-gradient(135deg,#ffc145,#ff8a5c); box-shadow:0 8px 30px rgba(255,193,69,0.25);">
                🚀 Booster maintenant — 500 FCFA
            </button>
        </form>
        @endif

    </div>

    @include('components.bottom-nav')

    <script>
        function selectMethod(el, val) {
            document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input[type=radio]').checked = true;
        }
        // Sélectionner le choix déjà coché au chargement
        document.querySelectorAll('.method-btn input[type=radio]').forEach(r => {
            if (r.checked) r.closest('.method-btn').classList.add('selected');
        });
    </script>
</body>

</html>