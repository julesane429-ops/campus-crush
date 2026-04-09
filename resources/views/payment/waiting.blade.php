@php $omDeeplink = $omDeeplink ?? null; @endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Paiement en cours — Campus Crush</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); min-height: 100vh; color: #fff; }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; opacity: 0.10; }
        @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.08); opacity: 0.7; } }
        .pulse { animation: pulse 1.5s ease-in-out infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 1.2s linear infinite; }
        .cc-mono { font-family: 'Space Mono', monospace; }
    </style>
</head>
<body>
    <div class="orb" style="width:260px;height:260px;background:#ffc145;top:-80px;right:-60px;"></div>

    <div class="max-w-md mx-auto px-5 flex flex-col items-center justify-center min-h-screen text-center">

        {{-- État : en attente --}}
        <div id="state-waiting">
            <div class="text-6xl mb-6 pulse">
                @if($paymentMethod === 'wave') 🔵
                @elseif($paymentMethod === 'orange_money') 🟠
                @else 🟢
                @endif
            </div>

            <h1 class="text-xl font-bold mb-2">
                @if($method === 'free_ussd')
                    Confirme sur ton téléphone
                @else
                    Redirection en cours...
                @endif
            </h1>

            <p class="text-sm text-white/40 mb-2 max-w-[300px]">
                @if($paymentMethod === 'wave')
                    L'application <strong class="text-white/60">Wave</strong> va s'ouvrir pour confirmer le paiement de <strong class="cc-mono text-white/60">{{ number_format($amount) }} FCFA</strong>
                @elseif($paymentMethod === 'orange_money')
                    L'application <strong class="text-white/60">Orange Money</strong> va s'ouvrir pour confirmer le paiement de <strong class="cc-mono text-white/60">{{ number_format($amount) }} FCFA</strong>
                @else
                    Tape <strong class="text-white/70 text-lg">#150#</strong> sur ton téléphone pour confirmer le paiement de <strong class="cc-mono text-white/60">{{ number_format($amount) }} FCFA</strong>
                @endif
            </p>

            @if($softpayMessage)
            <div class="rounded-2xl p-4 mb-4 mt-4" style="background: rgba(255,193,69,0.08); border: 1px solid rgba(255,193,69,0.15);">
                <p class="text-xs text-white/60">{{ $softpayMessage }}</p>
            </div>
            @endif

            @if(!empty($redirectUrl))
            @if($paymentMethod === 'orange_money')
                {{--
                    Orange Money iOS/Android :
                    - Android → deep link natif orangemoney://... (ouvre l'app directement)
                    - iOS     → Universal Link https://qrcode.orange.sn/mp/... (ouvre OM ou OM Pay
                                si installé via Universal Links, sinon Safari pour payer en ligne)
                --}}
                <div id="om-cta" class="w-full mt-4 mb-2">
                    {{-- Bouton principal (JS remplacera le href selon la plateforme) --}}
                    <a id="om-btn" href="{{ $redirectUrl }}"
                        class="w-full flex items-center justify-center gap-3 px-5 py-3.5 rounded-2xl text-sm font-bold text-white active:scale-95 transition mb-2"
                        style="background: linear-gradient(135deg, #f97316, #ea580c);">
                        <span class="text-xl">🟠</span>
                        <span id="om-btn-label">Payer avec Orange Money</span>
                        <span>→</span>
                    </a>
                    {{-- Fallback web toujours visible en dessous --}}
                    <a href="{{ $redirectUrl }}"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs text-white/35 border border-white/10 active:scale-95 transition">
                        🌐 Payer via le navigateur
                    </a>
                </div>
            @else
                {{-- Wave : URL HTTPS universelle --}}
                <a href="{{ $redirectUrl }}"
                    class="inline-block mt-4 mb-2 px-6 py-3 rounded-2xl text-sm font-bold text-white active:scale-95 transition"
                    style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                    Ouvrir Wave →
                </a>
                <p class="text-[10px] text-white/20 mb-4">Si l'app ne s'ouvre pas automatiquement, clique ci-dessus</p>
            @endif
            @endif

            {{-- Spinner + polling --}}
            <div class="flex items-center justify-center gap-3 mt-6 mb-4">
                <svg class="w-5 h-5 text-[#ffc145] spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-xs text-white/30" id="status-text">En attente de confirmation...</span>
            </div>

            <p class="text-[10px] text-white/15 cc-mono">Vérification auto dans <span id="timer">90</span>s</p>

            {{-- Bouton retry --}}
            <div class="mt-6">
                <a href="{{ $cancelUrl }}" class="text-xs text-white/25 hover:text-white/40 transition underline">
                    Annuler et réessayer
                </a>
            </div>
        </div>

        {{-- État : succès --}}
        <div id="state-success" class="hidden">
            <div class="text-6xl mb-4">🎉</div>
            <h1 class="text-2xl font-bold mb-2" style="background:linear-gradient(135deg,#ff5e6c,#ffc145);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Paiement confirmé !</h1>
            <p class="text-sm text-white/40 mb-6">{{ number_format($amount) }} FCFA payés avec succès</p>
            <a href="{{ $successUrl }}" class="inline-block px-8 py-3.5 rounded-2xl font-bold text-white text-sm" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                Continuer →
            </a>
        </div>

        {{-- État : timeout --}}
        <div id="state-timeout" class="hidden">
            <div class="text-5xl mb-4">⏰</div>
            <h2 class="text-lg font-bold mb-2">Pas encore confirmé</h2>
            <p class="text-sm text-white/40 mb-6 max-w-[280px]">
                @if($paymentMethod === 'wave')
                    Ouvre l'app Wave et accepte le paiement, puis clique "Vérifier".
                @elseif($paymentMethod === 'orange_money')
                    Ouvre l'app Orange Money et confirme, puis clique "Vérifier".
                @else
                    Tape #150# sur ton téléphone pour confirmer, puis clique "Vérifier".
                @endif
            </p>
            <div class="flex flex-col gap-3">
                <button onclick="startPolling()" class="px-6 py-3 rounded-2xl text-sm font-semibold text-white" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                    🔄 Vérifier à nouveau
                </button>
                <a href="{{ $cancelUrl }}" class="px-6 py-3 rounded-2xl text-sm font-medium text-white/30 border border-white/10">
                    Réessayer le paiement
                </a>
            </div>
        </div>
    </div>

    <script>
    const token = @json($token);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let attempts = 0;
    const maxAttempts = 18; // 18 × 5s = 90 secondes
    let timerInterval, pollInterval;

    function startPolling() {
        document.getElementById('state-waiting').classList.remove('hidden');
        document.getElementById('state-timeout').classList.add('hidden');
        document.getElementById('state-success').classList.add('hidden');
        attempts = 0;

        let seconds = 90;
        document.getElementById('timer').textContent = seconds;
        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            seconds--;
            const el = document.getElementById('timer');
            if (el) el.textContent = seconds;
            if (seconds <= 0) clearInterval(timerInterval);
        }, 1000);

        clearInterval(pollInterval);
        pollInterval = setInterval(checkStatus, 5000);
        // Premier check après 8s (laisser le temps de confirmer)
        setTimeout(checkStatus, 8000);
    }

    async function checkStatus() {
        attempts++;
        const statusEl = document.getElementById('status-text');
        if (statusEl) statusEl.textContent = 'Vérification... (' + attempts + '/' + maxAttempts + ')';

        try {
            const res = await fetch('/payment/check/' + token, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            const data = await res.json();

            if (data.status === 'completed') {
                clearInterval(pollInterval);
                clearInterval(timerInterval);
                document.getElementById('state-waiting').classList.add('hidden');
                document.getElementById('state-success').classList.remove('hidden');
                return;
            }
        } catch(e) {}

        if (attempts >= maxAttempts) {
            clearInterval(pollInterval);
            clearInterval(timerInterval);
            document.getElementById('state-waiting').classList.add('hidden');
            document.getElementById('state-timeout').classList.remove('hidden');
        }
    }

    startPolling();

    const isIOS     = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isAndroid = /android/i.test(navigator.userAgent);

    @if($paymentMethod === 'wave' && !empty($redirectUrl))
        // Wave : URL HTTPS universelle, fonctionne partout
        setTimeout(() => { window.location.href = @json($redirectUrl); }, 1500);
    @endif

    @if($paymentMethod === 'orange_money' && !empty($redirectUrl))
    (function() {
        const androidDeepLink = @json($omDeeplink);  // orangemoney://qrcode.orange.sn/mp/TOKEN
        const universalLink   = @json($redirectUrl); // https://qrcode.orange.sn/mp/TOKEN
        const btn             = document.getElementById('om-btn');
        const btnLabel        = document.getElementById('om-btn-label');

        if (isAndroid && androidDeepLink) {
            // Android : deep link natif → ouvre l'app Orange Money directement
            btn.href = androidDeepLink;
            btnLabel.textContent = 'Ouvrir Orange Money';
            setTimeout(() => { window.location.href = androidDeepLink; }, 1500);

        } else if (isIOS) {
            // iOS : Universal Link HTTPS
            // Si Orange Money ou OM Pay est installé → iOS ouvre l'app automatiquement
            // Sinon → Safari, l'utilisateur peut payer en ligne
            btn.href = universalLink;
            btnLabel.textContent = 'Ouvrir Orange Money / OM Pay';
            setTimeout(() => { window.location.href = universalLink; }, 1500);

        } else {
            // Desktop : lien web, pas d'auto-redirect
            btn.href = universalLink;
            btnLabel.textContent = 'Payer avec Orange Money (web)';
        }
    })();
    @endif
    </script>
</body>
</html>