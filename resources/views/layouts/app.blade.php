<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <meta name="theme-color" content="#0c0a1a">

    <title>{{ config('app.name', 'Campus Crush') }}</title>

    <script src="https://cdn.tailwindcss.com/3.4.17"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --cc-bg: #0c0a1a;
            --cc-surface: rgba(255,255,255,0.04);
            --cc-surface-hover: rgba(255,255,255,0.08);
            --cc-border: rgba(255,255,255,0.06);
            --cc-text: #f0eef5;
            --cc-text-muted: rgba(240,238,245,0.5);
            --cc-accent: #ff5e6c;
            --cc-accent-glow: rgba(255,94,108,0.3);
            --cc-secondary: #ffc145;
            --cc-gradient-1: linear-gradient(135deg, #ff5e6c 0%, #ff8a5c 50%, #ffc145 100%);
            --cc-gradient-2: linear-gradient(135deg, #6c5ce7 0%, #a855f7 50%, #ff5e6c 100%);
            --cc-gradient-3: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
        }

        * { font-family: 'Sora', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--cc-bg);
            color: var(--cc-text);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* === SURFACES === */
        .cc-surface {
            background: var(--cc-surface);
            border: 1px solid var(--cc-border);
            backdrop-filter: blur(24px) saturate(1.2);
            -webkit-backdrop-filter: blur(24px) saturate(1.2);
        }

        .cc-surface-raised {
            background: linear-gradient(135deg, rgba(255,255,255,0.06) 0%, rgba(255,255,255,0.02) 100%);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(40px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.05);
        }

        /* === GRADIENT BACKGROUNDS === */
        .cc-bg-main {
            background: var(--cc-gradient-3);
            min-height: 100vh;
        }

        .cc-bg-noise::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* === BUTTONS === */
        .cc-btn-primary {
            background: var(--cc-gradient-1);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 16px;
            padding: 14px 28px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .cc-btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .cc-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px var(--cc-accent-glow);
        }
        .cc-btn-primary:hover::before { opacity: 1; }
        .cc-btn-primary:active { transform: translateY(0) scale(0.98); }

        .cc-btn-ghost {
            background: var(--cc-surface);
            color: var(--cc-text);
            border: 1px solid var(--cc-border);
            border-radius: 16px;
            padding: 14px 28px;
            font-weight: 500;
            cursor: pointer;
            backdrop-filter: blur(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .cc-btn-ghost:hover {
            background: var(--cc-surface-hover);
            border-color: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }

        /* === INPUTS === */
        .cc-input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255,255,255,0.04);
            border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            color: var(--cc-text);
            font-size: 15px;
            transition: all 0.3s;
            outline: none;
        }
        .cc-input::placeholder { color: var(--cc-text-muted); }
        .cc-input:focus {
            border-color: var(--cc-accent);
            box-shadow: 0 0 0 4px var(--cc-accent-glow), 0 4px 20px rgba(0,0,0,0.2);
            background: rgba(255,255,255,0.06);
        }

        .cc-select {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255,255,255,0.04);
            border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            color: var(--cc-text);
            font-size: 15px;
            transition: all 0.3s;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23666' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
        }
        .cc-select option { background: #1a1145; color: #f0eef5; }

        /* === TAGS === */
        .cc-tag {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.7);
            transition: all 0.25s;
            cursor: pointer;
        }
        .cc-tag.active, .cc-tag:hover {
            background: var(--cc-accent);
            border-color: var(--cc-accent);
            color: white;
            box-shadow: 0 4px 15px var(--cc-accent-glow);
        }

        /* === BADGE === */
        .cc-badge {
            background: var(--cc-gradient-1);
            padding: 6px 12px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* === ANIMATIONS === */
        @keyframes cc-float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            33% { transform: translateY(-8px) rotate(1deg); }
            66% { transform: translateY(-4px) rotate(-1deg); }
        }

        @keyframes cc-fade-up {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes cc-slide-in {
            from { opacity: 0; transform: translateX(-16px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes cc-scale-in {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes cc-glow-pulse {
            0%, 100% { box-shadow: 0 0 20px var(--cc-accent-glow); }
            50% { box-shadow: 0 0 40px var(--cc-accent-glow), 0 0 60px rgba(255,94,108,0.1); }
        }

        @keyframes cc-gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .cc-float { animation: cc-float 6s ease-in-out infinite; }
        .cc-fade-up { animation: cc-fade-up 0.6s cubic-bezier(0.22, 1, 0.36, 1) both; }
        .cc-slide-in { animation: cc-slide-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
        .cc-scale-in { animation: cc-scale-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }

        /* === SCROLLBAR === */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        /* === GRADIENT TEXT === */
        .cc-gradient-text {
            background: var(--cc-gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* === ORBS (decorative) === */
        .cc-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            pointer-events: none;
        }

        /* === MONO FONT FOR NUMBERS === */
        .cc-mono { font-family: 'Space Mono', monospace; }
    </style>

    @stack('styles')
</head>

<body>
    {{-- Flash messages --}}
    @if(session('success'))
    <div id="flash-success" class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] cc-fade-up">
        <div class="cc-surface-raised rounded-2xl px-6 py-3 flex items-center gap-3">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    </div>
    <script>setTimeout(() => document.getElementById('flash-success')?.remove(), 3500);</script>
    @endif

    @if(session('error'))
    <div id="flash-error" class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] cc-fade-up">
        <div class="cc-surface-raised rounded-2xl px-6 py-3 flex items-center gap-3 border-red-500/20">
            <span class="w-2 h-2 bg-red-400 rounded-full animate-pulse"></span>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    </div>
    <script>setTimeout(() => document.getElementById('flash-error')?.remove(), 3500);</script>
    @endif

    <main>
        @yield('content')
    </main>

    @stack('scripts')
    @include('components.pwa-install-banner')
</body>
</html>
