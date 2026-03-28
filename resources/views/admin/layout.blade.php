<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Campus Crush - Admin</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Sora', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #0c0a1a;
            color: #f0eef5;
        }

        .admin-surface {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
        }

        .admin-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .cc-gradient-text {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .cc-mono {
            font-family: 'Space Mono', monospace;
        }
    </style>
</head>

<body class="min-h-screen">

    {{-- Admin Sidebar / Header --}}
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-64 admin-surface border-r border-white/5 p-6 flex-shrink-0 hidden md:flex flex-col">
            <div class="flex items-center gap-2 mb-10">
                <span class="text-xl">🔥</span>
                <span class="font-bold cc-gradient-text">Admin Panel</span>
            </div>

            <nav class="space-y-1 flex-1">
                @php $route = request()->route()?->getName(); @endphp

                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition {{ $route === 'admin.dashboard' ? 'bg-[#ff5e6c]/10 text-[#ff5e6c]' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                    <span>📊</span> Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition {{ $route === 'admin.users' ? 'bg-[#ff5e6c]/10 text-[#ff5e6c]' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                    <span>👥</span> Utilisateurs
                </a>
                <a href="{{ route('admin.reports') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition {{ $route === 'admin.reports' ? 'bg-[#ff5e6c]/10 text-[#ff5e6c]' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                    <span>⚠️</span> Signalements
                    @php $pendingCount = \App\Models\Report::where('status','pending')->count(); @endphp
                    @if($pendingCount > 0)
                    <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.payments') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition {{ $route === 'admin.payments' ? 'bg-[#ff5e6c]/10 text-[#ff5e6c]' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                    <span>💰</span> Paiements
                </a>
            </nav>

            <div class="pt-6 border-t border-white/5 space-y-3">
                <a href="{{ route('swipe') }}" class="flex items-center gap-2 text-xs text-white/30 hover:text-white transition">
                    ← Retour à l'app
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-xs text-red-400/60 hover:text-red-400 transition">
                        🚪 Déconnexion
                    </button>
                </form>
            </div>
        </aside>

        {{-- Mobile Header --}}
        <div class="md:hidden fixed top-0 left-0 right-0 z-50 admin-surface border-b border-white/5 px-4 py-3">
            <div class="flex items-center justify-between">
                <span class="font-bold cc-gradient-text text-sm">🔥 Admin</span>
                <div class="flex gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-lg {{ $route === 'admin.dashboard' ? 'bg-[#ff5e6c]/10' : 'hover:bg-white/5' }}"><span class="text-sm">📊</span></a>
                    <a href="{{ route('admin.users') }}" class="p-2 rounded-lg {{ $route === 'admin.users' ? 'bg-[#ff5e6c]/10' : 'hover:bg-white/5' }}"><span class="text-sm">👥</span></a>
                    <a href="{{ route('admin.reports') }}" class="p-2 rounded-lg {{ $route === 'admin.reports' ? 'bg-[#ff5e6c]/10' : 'hover:bg-white/5' }}"><span class="text-sm">⚠️</span></a>
                    <a href="{{ route('admin.payments') }}" class="p-2 rounded-lg {{ $route === 'admin.payments' ? 'bg-[#ff5e6c]/10' : 'hover:bg-white/5' }}"><span class="text-sm">💰</span></a>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <main class="flex-1 p-6 md:p-8 overflow-auto md:mt-0 mt-14">
            {{-- Flash --}}
            @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-6 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                {{ session('error') }}
            </div>
            @endif

            @yield('admin-content')
        </main>
    </div>
</body>

</html>