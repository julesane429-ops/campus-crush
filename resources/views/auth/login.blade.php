<!doctype html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campus Crush - Connexion</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); min-height: 100vh; }
        .cc-gradient-text { background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cc-input { width:100%; padding:16px 20px; background:rgba(255,255,255,0.04); border:1.5px solid rgba(255,255,255,0.08); border-radius:14px; color:#f0eef5; font-size:15px; transition:all .3s; outline:none; }
        .cc-input::placeholder { color: rgba(240,238,245,0.3); }
        .cc-input:focus { border-color: #ff5e6c; box-shadow: 0 0 0 4px rgba(255,94,108,0.15); background: rgba(255,255,255,0.06); }

        @keyframes fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }
        .fade-up { animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both; }
        .d1{animation-delay:.1s}.d2{animation-delay:.2s}.d3{animation-delay:.3s}

        @keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.04)} }
        .logo-pulse { animation: pulse 3s ease-in-out infinite; }
    </style>
</head>
<body class="flex items-center justify-center p-6 text-white">

    {{-- Orb --}}
    <div class="fixed top-1/4 left-1/4 w-80 h-80 bg-[#ff5e6c] rounded-full blur-[150px] opacity-8 pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-sm">

        {{-- Logo --}}
        <div class="text-center mb-10 fade-up">
            <div class="logo-pulse inline-flex w-20 h-20 rounded-3xl items-center justify-center mb-4" style="background: linear-gradient(135deg, rgba(255,94,108,0.15), rgba(255,193,69,0.1)); border: 1px solid rgba(255,94,108,0.15);">
                <svg class="w-10 h-10" fill="url(#lg)" viewBox="0 0 24 24">
                    <defs><linearGradient id="lg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ff5e6c"/><stop offset="100%" stop-color="#ffc145"/></linearGradient></defs>
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold cc-gradient-text">Campus Crush</h1>
            <p class="text-sm text-white/30 mt-1">Rencontres universitaires</p>
        </div>

        {{-- Card --}}
        <div class="rounded-3xl p-7 fade-up d1" style="background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02)); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(40px);">
            <h2 class="text-xl font-semibold text-center mb-6">Connexion</h2>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email universitaire" required class="cc-input">
                    @error('email')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div class="relative">
                    <input type="password" name="password" id="pwd" placeholder="Mot de passe" required class="cc-input pr-12">
                    <button type="button" onclick="document.getElementById('pwd').type = document.getElementById('pwd').type==='password'?'text':'password'" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    @error('password')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full py-4 rounded-2xl font-semibold text-white text-base transition hover:-translate-y-0.5 active:scale-[0.98]" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                    Se connecter
                </button>
            </form>
        </div>

        <div class="flex justify-center mt-6 fade-up d2">
            <a href="{{ route('home') }}" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 transition" title="Accueil">
                <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </a>
        </div>

        <p class="text-center mt-4 text-sm fade-up d3">
            <span class="text-white/30">Pas de compte ?</span>
            <a href="{{ route('register') }}" class="text-[#ff5e6c] font-semibold ml-1 hover:underline">S'inscrire</a>
        </p>
    </div>
</body>
</html>
