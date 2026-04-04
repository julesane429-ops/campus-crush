<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Campus Crush Admin</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Sora', sans-serif;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: #0c0a1a;
        }

        .cc-mono {
            font-family: 'Space Mono', monospace;
        }

        .cc-gradient-text {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .admin-surface {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
        }

        .admin-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* Sidebar */
        .sidebar {
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .sidebar-overlay {
            transition: opacity 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay.open {
                opacity: 1;
                pointer-events: auto;
            }
        }

        .nav-link {
            transition: all 0.2s ease;
            border-radius: 12px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 94, 108, 0.08);
        }

        .nav-link.active {
            border-left: 3px solid #ff5e6c;
        }

        /* Toast */
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .toast {
            animation: slideDown 0.3s ease;
        }

        /* Table responsive */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap::-webkit-scrollbar {
            height: 3px;
        }

        .table-wrap::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
    </style>
</head>

<body class="text-white min-h-screen">

    {{-- Mobile header --}}
    <header class="md:hidden sticky top-0 z-40 flex items-center justify-between px-4 py-3" style="background: rgba(12,10,26,0.95); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,0.05);">
        <button id="menu-toggle" class="p-2 rounded-xl hover:bg-white/5 active:scale-95 transition">
            <svg class="w-5 h-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <span class="text-sm font-bold cc-gradient-text">Admin Panel</span>
        <a href="{{ route('swipe') }}" class="p-2 rounded-xl hover:bg-white/5 transition">
            <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
    </header>

    {{-- Overlay --}}
    <div id="sidebar-overlay" class="sidebar-overlay fixed inset-0 bg-black/60 z-40 opacity-0 pointer-events-none md:hidden" onclick="closeSidebar()"></div>

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside id="sidebar" class="sidebar w-64 flex-shrink-0 h-screen sticky top-0 flex flex-col" style="background: rgba(12,10,26,0.98); border-right: 1px solid rgba(255,255,255,0.05);">

            {{-- Logo --}}
            <div class="px-5 py-5 flex items-center gap-2.5 border-b border-white/5">
                <span class="text-xl">🔥</span>
                <div>
                    <h1 class="text-sm font-bold cc-gradient-text">Campus Crush</h1>
                    <p class="text-[9px] text-white/20 uppercase tracking-widest cc-mono">Admin Panel</p>
                </div>
                <button class="md:hidden ml-auto p-1.5 rounded-lg hover:bg-white/5" onclick="closeSidebar()">
                    <svg class="w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('admin.dashboard') ? 'active text-white' : 'text-white/50' }}">
                    <span class="text-base">📊</span> Dashboard
                </a>
                <a href="{{ route('admin.analytics') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 text-sm text-white/60 hover:text-white {{ request()->routeIs('admin.analytics') ? 'active text-white' : '' }}">
                    <span>📊</span>
                    <span>Analytics</span>
                </a>
                <a href="{{ route('admin.users') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('admin.users') ? 'active text-white' : 'text-white/50' }}">
                    <span class="text-base">👥</span> Utilisateurs
                </a>
                <a href="{{ route('admin.reports') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('admin.reports') ? 'active text-white' : 'text-white/50' }}">
                    <span class="text-base">⚠️</span> Signalements
                    @php $pendingCount = \App\Models\Report::where('status', 'pending')->count(); @endphp
                    @if($pendingCount > 0)
                    <span class="ml-auto min-w-[20px] h-5 px-1.5 bg-red-500/20 text-red-400 text-[10px] font-bold rounded-full flex items-center justify-center">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.payments') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('admin.payments') ? 'active text-white' : 'text-white/50' }}">
                    <span class="text-base">💰</span> Paiements
                </a>
                <a href="{{ route('admin.reviews') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('admin.reviews') ? 'active text-white' : 'text-white/50' }}">
                    <span class="text-base">💬</span> Avis
                    @php $pendingReviews = \App\Models\Review::where('status', 'pending')->count(); @endphp
                    @if($pendingReviews > 0)
                    <span class="ml-auto min-w-[20px] h-5 px-1.5 bg-[#ffc145]/20 text-[#ffc145] text-[10px] font-bold rounded-full flex items-center justify-center">{{ $pendingReviews }}</span>
                    @endif
                </a>

                <div class="h-px bg-white/5 my-3"></div>

                <a href="{{ route('swipe') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 text-sm text-white/30 hover:text-white/50">
                    <span class="text-base">🔥</span> Retour à l'app
                </a>
            </nav>

            {{-- Admin info + logout --}}
            <div class="px-4 py-4 border-t border-white/5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-[#ff5e6c]/10 flex items-center justify-center text-xs font-bold text-[#ff5e6c]">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium truncate text-white/70">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-white/25 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-2 rounded-xl text-[11px] text-white/25 hover:text-red-400 hover:bg-red-500/5 transition">
                        Déconnexion
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 min-w-0 p-4 md:p-6 lg:p-8">

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="toast mb-4 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="toast mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-2">
                <span>❌</span> {{ session('error') }}
            </div>
            @endif

            @yield('admin-content')
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggle = document.getElementById('menu-toggle');

        if (toggle) {
            toggle.addEventListener('click', () => {
                sidebar.classList.add('open');
                overlay.classList.add('open');
            });
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        }

        // Close on nav click (mobile)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) closeSidebar();
            });
        });
    </script>
</body>

</html>