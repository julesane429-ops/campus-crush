<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Campus Crush - Inscription</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); min-height: 100vh; min-height: 100dvh; }
        .cc-gradient-text { background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cc-input {
            width: 100%; padding: 14px 16px;
            background: rgba(255,255,255,0.04); border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 14px; color: #f0eef5; font-size: 15px; transition: all .3s; outline: none;
        }
        .cc-input::placeholder { color: rgba(240,238,245,0.3); }
        .cc-input:focus { border-color: #ff5e6c; box-shadow: 0 0 0 4px rgba(255,94,108,0.15); background: rgba(255,255,255,0.06); }
        @keyframes fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }
        .fade-up { animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both; }
        .d1{animation-delay:.1s}.d2{animation-delay:.2s}.d3{animation-delay:.3s}

        /* Custom checkbox large */
        .cc-checkbox { display: none; }
        .cc-checkbox-label {
            display: flex; align-items: flex-start; gap: 12px; cursor: pointer;
            padding: 12px 14px; border-radius: 14px;
            background: rgba(255,255,255,0.02); border: 1.5px solid rgba(255,255,255,0.06);
            transition: all 0.2s;
        }
        .cc-checkbox-label:active { transform: scale(0.98); }
        .cc-checkbox:checked + .cc-checkbox-label { border-color: rgba(255,94,108,0.3); background: rgba(255,94,108,0.04); }
        .cc-checkbox-box {
            width: 22px; height: 22px; min-width: 22px; border-radius: 6px;
            border: 2px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.04);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; margin-top: 1px;
        }
        .cc-checkbox:checked + .cc-checkbox-label .cc-checkbox-box {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c); border-color: transparent;
        }
        .cc-check-icon { display: none; }
        .cc-checkbox:checked + .cc-checkbox-label .cc-check-icon { display: block; }
    </style>
</head>
<body class="flex items-center justify-center text-white">

    <div class="fixed bottom-0 right-0 w-80 h-80 bg-purple-600 rounded-full blur-[150px] opacity-8 pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-sm px-5 py-6" style="padding-top: max(env(safe-area-inset-top, 24px), 24px);">

        {{-- Logo --}}
        <div class="text-center mb-6 fade-up">
            <div class="inline-flex w-14 h-14 rounded-2xl items-center justify-center mb-2.5" style="background: linear-gradient(135deg, rgba(255,94,108,0.15), rgba(255,193,69,0.1)); border: 1px solid rgba(255,94,108,0.15);">
                <svg class="w-7 h-7" fill="url(#lg2)" viewBox="0 0 24 24">
                    <defs><linearGradient id="lg2" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ff5e6c"/><stop offset="100%" stop-color="#ffc145"/></linearGradient></defs>
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </div>
            <h1 class="text-lg font-bold cc-gradient-text">Campus Crush</h1>
        </div>

        {{-- Card --}}
        <div class="rounded-3xl p-5 sm:p-7 fade-up d1" style="background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02)); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(40px);">
            <h2 class="text-lg font-semibold text-center mb-5">Créer un compte</h2>

            <form id="signup-form" method="POST" action="{{ route('register') }}" class="space-y-3.5">
                @csrf
                <input type="text" name="prenom" id="prenom" placeholder="Prénom" required class="cc-input" autocomplete="given-name">
                <input type="email" name="email" id="email" placeholder="Email" required class="cc-input" autocomplete="email">
                <div class="relative">
                    <input type="password" name="password" id="password" placeholder="Mot de passe (min. 8)" required class="cc-input pr-12" autocomplete="new-password">
                    <button type="button" onclick="const el=document.getElementById('password');el.type=el.type==='password'?'text':'password'" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirmer le mot de passe" required class="cc-input" autocomplete="new-password">

                <div id="error-msg" class="hidden text-red-400 text-xs text-center bg-red-500/10 py-2.5 px-4 rounded-xl border border-red-500/10"></div>
                <div id="success-msg" class="hidden text-green-400 text-xs text-center bg-green-500/10 py-2.5 px-4 rounded-xl border border-green-500/10"></div>

                {{-- Custom checkbox (grand, facile à cliquer) --}}
                <input type="checkbox" name="terms" id="terms-cb" required class="cc-checkbox">
                <label for="terms-cb" class="cc-checkbox-label">
                    <div class="cc-checkbox-box">
                        <svg class="cc-check-icon w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-[12px] text-white/40 leading-relaxed">
                        J'accepte les
                        <a href="/terms" target="_blank" class="text-[#ff5e6c] underline">conditions</a>
                        et la
                        <a href="/privacy" target="_blank" class="text-[#ff5e6c] underline">politique de confidentialité</a>.
                        Je confirme avoir au moins 18 ans.
                    </span>
                </label>

                @if(session('referral_code'))
                <input type="hidden" name="ref" value="{{ session('referral_code') }}">
                @endif

                <button type="submit" id="submit-btn" class="w-full py-3.5 rounded-2xl font-semibold text-white text-base transition active:scale-[0.98]" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                    Créer mon compte
                </button>
            </form>
        </div>

        {{-- Links --}}
        <div class="flex items-center justify-center gap-4 mt-5 fade-up d2">
            <a href="{{ route('home') }}" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 transition" title="Accueil">
                <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </a>
            <p class="text-sm">
                <a href="{{ route('login') }}" class="text-[#ff5e6c] font-semibold hover:underline">Déjà inscrit ? Se connecter</a>
            </p>
        </div>
    </div>

    <script>
    document.getElementById('signup-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const err = document.getElementById('error-msg'), ok = document.getElementById('success-msg');
        err.classList.add('hidden'); ok.classList.add('hidden');

        if (!document.getElementById('terms-cb').checked) {
            err.textContent = 'Tu dois accepter les conditions d\'utilisation.';
            err.classList.remove('hidden');
            return;
        }

        const fd = new FormData();
        fd.append('prenom', document.getElementById('prenom').value);
        fd.append('email', document.getElementById('email').value);
        fd.append('password', document.getElementById('password').value);
        fd.append('password_confirmation', document.getElementById('password_confirmation').value);
        fd.append('terms', '1');

        const refInput = document.querySelector('input[name="ref"]');
        if (refInput) fd.append('ref', refInput.value);

        const btn = document.getElementById('submit-btn');
        btn.disabled = true; btn.textContent = 'Création...';

        try {
            const r = await fetch(this.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: fd
            });
            const data = await r.json();

            if (!r.ok) {
                err.textContent = data.errors ? Object.values(data.errors).flat()[0] : 'Erreur';
                err.classList.remove('hidden');
                btn.disabled = false; btn.textContent = 'Créer mon compte';
                return;
            }

            if (data.success) {
                ok.textContent = 'Compte créé ! 🎉 Redirection...';
                ok.classList.remove('hidden');
                setTimeout(() => window.location.href = data.redirect || '/profile/create', 1000);
            }
        } catch (e) {
            err.textContent = 'Erreur serveur'; err.classList.remove('hidden');
            btn.disabled = false; btn.textContent = 'Créer mon compte';
        }
    });
    </script>
    @include('components.pwa-install-banner')
</body>
</html>
