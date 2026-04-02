<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('components.pwa-meta')
    <title>Campus Crush - Rencontres Universitaires</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; }

        .hero-bg {
            background: radial-gradient(ellipse at 30% 20%, rgba(255,94,108,0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at 70% 80%, rgba(168,85,247,0.1) 0%, transparent 50%),
                        linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
        }

        .hero-bg::before {
            content: ''; position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        .cc-gradient-text { background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-size: 200% 200%; animation: shimmer 3s ease-in-out infinite; }
        @keyframes shimmer { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }

        .cc-btn { display: inline-flex; align-items: center; justify-content: center; padding: 16px 32px; border-radius: 16px; font-weight: 600; font-size: 16px; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); cursor: pointer; text-decoration: none; }
        .cc-btn-primary { background: linear-gradient(135deg, #ff5e6c, #ff8a5c); color: white; box-shadow: 0 8px 30px rgba(255,94,108,0.3); }
        .cc-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 40px rgba(255,94,108,0.4); }
        .cc-btn-ghost { background: rgba(255,255,255,0.04); color: #f0eef5; border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(20px); }
        .cc-btn-ghost:hover { background: rgba(255,255,255,0.08); transform: translateY(-2px); }

        .step-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(20px); transition: all 0.4s cubic-bezier(0.4,0,0.2,1); }
        .step-card:hover { transform: translateY(-6px); border-color: rgba(255,94,108,0.2); box-shadow: 0 20px 60px rgba(0,0,0,0.3); }

        @keyframes fadeUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:none; } }
        @keyframes float { 0%,100% { transform: translateY(0) rotate(0deg); } 33% { transform: translateY(-12px) rotate(2deg); } 66% { transform: translateY(-6px) rotate(-1deg); } }

        .fade-up { animation: fadeUp 0.7s cubic-bezier(0.22,1,0.36,1) both; }
        .float { animation: float 6s ease-in-out infinite; }
        .d1{animation-delay:.15s}.d2{animation-delay:.3s}.d3{animation-delay:.45s}.d4{animation-delay:.6s}

        .cc-mono { font-family: 'Space Mono', monospace; }
    </style>
</head>
<body class="hero-bg text-white overflow-x-hidden">
<div class="relative z-10 w-full">

    {{-- Navbar --}}
    <nav class="sticky top-0 z-50 px-5 py-4 backdrop-blur-xl bg-[#0c0a1a]/70 border-b border-white/5">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ff5e6c, #ffc145);">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <span class="font-bold text-lg cc-gradient-text">Campus Crush</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm text-white/50 hover:text-white transition hidden sm:block">Connexion</a>
                <a href="{{ route('register') }}" class="cc-btn cc-btn-primary text-sm !py-2.5 !px-5">S'inscrire</a>
            </div>
                    <a href="/install" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-semibold text-white border border-white/10 hover:bg-white/5 transition">
    📲 Installer l'app
</a>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="px-5 pt-16 pb-20 md:pt-24 md:pb-28">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex mb-6 fade-up">
                <span class="cc-mono text-[11px] uppercase tracking-[3px] text-white/30 px-4 py-2 rounded-full border border-white/10 bg-white/[0.02]">
                    🇸🇳 Fait pour les étudiants
                </span>
            </div>

            <h1 class="text-4xl sm:text-5xl md:text-7xl font-extrabold leading-[1.1] mb-6 fade-up d1">
                Trouve ton <span class="cc-gradient-text">crush</span><br>
                sur le campus
            </h1>

            <p class="text-lg sm:text-xl text-white/40 max-w-lg mx-auto mb-10 fade-up d2 leading-relaxed">
                L'appli de rencontres exclusivement conçue pour les étudiants sénégalais. Swipe, match, discute.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center fade-up d3">
                <a href="{{ route('register') }}" class="cc-btn cc-btn-primary text-lg">
                    Commencer gratuitement →
                </a>
                <a href="{{ route('login') }}" class="cc-btn cc-btn-ghost text-lg">
                    Se connecter
                </a>
            </div>

            <div class="mt-10 flex justify-center items-center gap-8 text-sm text-white/25 fade-up d4">
                <span>✓ Gratuit</span>
                <span>✓ Sécurisé</span>
                <span>✓ Homme & Femme</span>
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section class="px-5 py-20">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-center text-3xl md:text-4xl font-bold mb-4">Comment ça <span class="cc-gradient-text">marche</span> ?</h2>
            <p class="text-center text-white/30 mb-14">Trois étapes simples pour trouver l'amour</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach([
                    ['01', '📸', 'Crée ton profil', 'Ajoute ta photo, ta filière et tes passions'],
                    ['02', '🔥', 'Swipe', 'Découvre des étudiants et like ceux qui te plaisent'],
                    ['03', '💬', 'Match & Chat', 'C\'est réciproque ? Commencez à discuter !'],
                ] as $s)
                <div class="step-card rounded-3xl p-7 text-center group">
                    <span class="cc-mono text-xs text-white/15 tracking-wider">{{ $s[0] }}</span>
                    <div class="text-4xl my-5 group-hover:scale-110 transition-transform duration-300">{{ $s[1] }}</div>
                    <h3 class="text-lg font-bold mb-2">{{ $s[2] }}</h3>
                    <p class="text-sm text-white/30 leading-relaxed">{{ $s[3] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="px-5 py-16">
        <div class="max-w-xl mx-auto">
            <div class="rounded-3xl p-10 md:p-14 text-center relative overflow-hidden" style="background: linear-gradient(135deg, rgba(255,94,108,0.15), rgba(168,85,247,0.1)); border: 1px solid rgba(255,94,108,0.15);">
                <div class="absolute top-0 right-0 w-40 h-40 bg-[#ff5e6c] rounded-full blur-[80px] opacity-20"></div>
                <h2 class="text-3xl font-bold mb-3 relative z-10">Prêt(e) ? 💘</h2>
                <p class="text-white/40 mb-8 relative z-10">Rejoins des centaines d'étudiants du Sénégal</p>
                <a href="{{ route('register') }}" class="cc-btn cc-btn-primary text-lg relative z-10">Créer mon compte</a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="px-5 py-10 border-t border-white/5">
        <div class="max-w-4xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold cc-gradient-text">Campus Crush</span>
            </div>
            <p class="text-xs text-white/20">© 2026 · Fait avec ❤️ pour les étudiants du Sénégal</p>
        </div>
    </footer>
</div>
@include('components.pwa-install-banner')
</body>
</html>
