@php
$omUrl    = $omUrl    ?? null;
$maxitUrl = $maxitUrl ?? null;
@endphp
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

        /* QR box */
        #om-qr-box { display:none; }
        #om-qr-box.visible { display:block; }
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

            <p class="text-sm text-white/40 mb-4 max-w-[300px]">
                @if($paymentMethod === 'wave')
                    L'application <strong class="text-white/60">Wave</strong> va s'ouvrir pour confirmer le paiement de <strong class="cc-mono text-white/60">{{ number_format($amount) }} FCFA</strong>
                @elseif($paymentMethod === 'orange_money')
                    Choisis comment payer <strong class="cc-mono text-white/60">{{ number_format($amount) }} FCFA</strong> avec Orange Money
                @else
                    Tape <strong class="text-white/70 text-lg">#150#</strong> sur ton téléphone pour confirmer le paiement de <strong class="cc-mono text-white/60">{{ number_format($amount) }} FCFA</strong>
                @endif
            </p>

            @if($softpayMessage)
            <div class="rounded-2xl p-4 mb-4" style="background: rgba(255,193,69,0.08); border: 1px solid rgba(255,193,69,0.15);">
                <p class="text-xs text-white/60">{{ $softpayMessage }}</p>
            </div>
            @endif

            {{-- ═══ ORANGE MONEY ═══ --}}
            @if($paymentMethod === 'orange_money')
            <div class="w-full mt-2 mb-2">

                {{-- Bouton Orange Money (visible sur mobile, caché sur desktop) --}}
                @if(!empty($omUrl))
                <div id="om-mobile-btn">
                    <a href="{{ $omUrl }}" id="btn-om"
                        class="w-full flex items-center gap-3 px-5 py-3.5 rounded-2xl text-sm font-bold text-white active:scale-95 transition mb-2"
                        style="background: linear-gradient(135deg, #f97316, #ea580c);">
                        <span class="text-xl">🟠</span>
                        <div class="text-left flex-1">
                            <div>Orange Money</div>
                            <div class="text-[10px] font-normal opacity-70">Appuie pour ouvrir l'app</div>
                        </div>
                        <span>→</span>
                    </a>
                </div>
                @endif

                {{-- QR Code (visible sur desktop, ou si le deep link échoue sur mobile) --}}
                <div id="om-qr-box" class="mb-3">
                    <div style="background:rgba(255,102,0,0.08); border:1px solid rgba(255,102,0,0.20); border-radius:18px; padding:16px;">
                        <p class="text-xs text-white/50 mb-3">Scanne ce QR code avec<br>ton téléphone pour payer</p>
                        <div style="background:#fff; border-radius:12px; padding:10px; display:inline-block;">
                            <img id="om-qr-img" src="" alt="QR Code Orange Money" width="160" height="160" style="border-radius:6px; display:block;">
                        </div>
                        <p class="text-[10px] text-white/30 mt-3">Ouvre l'app Orange Money → Scanner QR</p>
                    </div>
                </div>

                {{-- Maxit / Sugu (toujours visible) --}}
                @if(!empty($maxitUrl))
                <a href="{{ $maxitUrl }}" id="btn-maxit"
                    class="w-full flex items-center gap-3 px-5 py-3.5 rounded-2xl text-sm font-bold text-white active:scale-95 transition mb-2"
                    style="background: linear-gradient(135deg, #ea580c, #b45309);">
                    <span class="text-xl">🔶</span>
                    <div class="text-left flex-1">
                        <div>Maxit / Sugu</div>
                        <div class="text-[10px] font-normal opacity-70">Portefeuille Orange Sonatel</div>
                    </div>
                    <span>→</span>
                </a>
                @endif

                <p class="text-[10px] text-white/20 text-center pt-1">
                    Clique sur ton application pour confirmer le paiement
                </p>
            </div>

            {{-- ═══ WAVE ═══ --}}
            @elseif($paymentMethod === 'wave' && !empty($redirectUrl))
            <a href="{{ $redirectUrl }}"
                class="w-full inline-flex items-center justify-center gap-2 mt-4 mb-2 px-6 py-3.5 rounded-2xl text-sm font-bold text-white active:scale-95 transition"
                style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                <span class="text-xl">🔵</span> Ouvrir Wave →
            </a>
            <p class="text-[10px] text-white/20 mb-4">Si l'app ne s'ouvre pas automatiquement, clique ci-dessus</p>
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
    const token   = @json($token);
    const csrf    = document.querySelector('meta[name="csrf-token"]').content;
    const omUrl   = @json($omUrl ?? null);
    const maxitUrl= @json($maxitUrl ?? null);

    let attempts = 0;
    const maxAttempts = 18;
    let timerInterval, pollInterval;

    // ── Détection device ──────────────────────────────────────────
    const isAndroid = /android/i.test(navigator.userAgent);
    const isIOS     = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isMobile  = isAndroid || isIOS;

    // ── Orange Money : logique d'affichage ────────────────────────
    @if($paymentMethod === 'orange_money')
    (function () {
        const mobileBtn = document.getElementById('om-mobile-btn');
        const qrBox     = document.getElementById('om-qr-box');
        const qrImg     = document.getElementById('om-qr-img');

        // URL à encoder dans le QR — on préfère maxit (HTTPS stable)
        const qrTarget = maxitUrl || omUrl;

        function showQr() {
            if (qrBox && qrImg && qrTarget) {
                qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&color=000000&bgcolor=ffffff&data='
                    + encodeURIComponent(qrTarget);
                qrBox.classList.add('visible');
            }
        }

        if (!isMobile) {
            // ── DESKTOP : cacher le bouton app, afficher QR directement ──
            if (mobileBtn) mobileBtn.style.display = 'none';
            showQr();
        } else {
            // ── MOBILE : afficher le bouton, détecter échec du deep link ──
            // Safari iOS ne supporte pas orangemoney://, on intercepte le clic
            const btn = document.getElementById('btn-om');
            if (btn) {
                btn.addEventListener('click', function (e) {
                    // Laisser le navigateur tenter l'ouverture
                    // Si après 2s on est toujours sur la page → l'app ne s'est pas ouverte
                    const t = Date.now();
                    setTimeout(function () {
                        // Si la page est encore visible (l'app ne s'est pas ouverte)
                        if (!document.hidden && Date.now() - t < 3500) {
                            // Basculer vers QR + cacher le bouton OM
                            if (mobileBtn) mobileBtn.style.display = 'none';
                            showQr();
                        }
                    }, 2500);
                });
            }

            // Auto-redirect vers Orange Money sur mobile après 1.5s
            if (omUrl) {
                setTimeout(function () {
                    window.location.href = omUrl;
                    // Détection d'échec après 2.5s supplémentaires
                    setTimeout(function () {
                        if (!document.hidden) {
                            if (mobileBtn) mobileBtn.style.display = 'none';
                            showQr();
                        }
                    }, 2500);
                }, 1500);
            }
        }
    })();
    @endif

    // ── Wave : auto-redirect ──────────────────────────────────────
    @if($paymentMethod === 'wave' && !empty($redirectUrl))
    setTimeout(() => { window.location.href = @json($redirectUrl); }, 1500);
    @endif

    // ── Polling statut paiement ───────────────────────────────────
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
        setTimeout(checkStatus, 8000);
    }

    async function checkStatus() {
        attempts++;
        const statusEl = document.getElementById('status-text');
        if (statusEl) statusEl.textContent = 'Vérification... (' + attempts + '/' + maxAttempts + ')';

        try {
            const res  = await fetch('/payment/check/' + token, {
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
    </script>
</body>
</html>