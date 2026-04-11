@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
<div class="relative z-10 w-full max-w-md flex flex-col text-white">

    {{-- Orbs --}}
    <div class="fixed top-0 right-0 w-60 h-60 bg-purple-600 rounded-full blur-[100px] opacity-10 pointer-events-none"></div>

    {{-- Header --}}
    <header class="sticky top-0 z-20 px-5 pt-5 pb-3">
        <div class="cc-surface-raised rounded-2xl px-5 py-4">
            <div class="flex items-center justify-between mb-1">
                <h1 class="text-xl font-bold">Mes Matchs</h1>
                <a href="{{ route('swipe') }}" class="text-xs text-white/40 hover:text-white transition px-3 py-1.5 rounded-full bg-white/5">← Swiper</a>
            </div>

            {{-- Horizontal match strip --}}
            @if($matches->count() > 0)
            <div class="mt-4">
                <p class="text-[10px] text-white/30 uppercase tracking-widest mb-3">Nouveaux</p>
                <div class="flex gap-4 overflow-x-auto pb-2 -mx-1 px-1" style="scrollbar-width: none;">
                    @foreach($matches as $i => $match)
                    <a href="{{ route('messages.chat', $match['match_id']) }}"
                       class="flex-shrink-0 flex flex-col items-center cc-fade-up"
                       @php $delay = $loop->index * 0.05; @endphp
style="animation-delay: {{ $delay }}s">
                        <div class="relative">
                            <div class="w-[60px] h-[60px] rounded-full p-[2px]" style="background: linear-gradient(135deg, #ff5e6c, #ffc145);">
                                <div class="w-full h-full rounded-full overflow-hidden">
                                    <img src="{{ $match['photo'] }}" class="w-full h-full object-cover" alt="{{ e($match['name']) }}">
                                </div>
                            </div>
                            @if($match['unread'] > 0)
                            <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-[#ff5e6c] rounded-full flex items-center justify-center text-[9px] font-bold shadow-lg shadow-[#ff5e6c]/30">
                                {{ $match['unread'] }}
                            </span>
                            @endif
                        </div>
                        <span class="mt-1.5 text-[10px] text-white/50 truncate w-16 text-center">{{ e($match['name']) }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </header>

    {{-- Message List --}}
    <main class="flex-1 px-5 py-3 overflow-y-auto">
        <p class="text-[10px] text-white/30 uppercase tracking-widest mb-4">Conversations</p>

        <div class="space-y-2">
            @forelse($matches as $i => $match)
            <a href="{{ route('messages.chat', $match['match_id']) }}"
               class="cc-surface rounded-2xl p-3.5 flex items-center gap-3.5 transition-all duration-300 hover:bg-white/[0.06] active:scale-[0.98] cc-slide-in"
               @php $delay = $loop->index * 0.06; @endphp
style="animation-delay: {{ $delay }}s">

                <div class="relative flex-shrink-0">
                    <img src="{{ $match['photo'] }}" class="w-13 h-13 rounded-full object-cover" style="width:52px;height:52px;" alt="">
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center mb-0.5">
                        <h3 class="font-semibold text-sm truncate">{{ e($match['name']) }}</h3>
                        <span class="text-[10px] text-white/25 ml-2 whitespace-nowrap cc-mono">{{ $match['last_time'] }}</span>
                    </div>
                    <p class="text-xs text-white/40 truncate">
                        {{ $match['last_message'] ?? '✨ Nouveau match ! Dis quelque chose...' }}
                    </p>
                </div>

                @if($match['unread'] > 0)
                <span class="ml-1 w-5 h-5 flex-shrink-0 bg-gradient-to-r from-[#ff5e6c] to-[#ff8a5c] text-[10px] font-bold rounded-full flex items-center justify-center shadow-lg shadow-[#ff5e6c]/20">
                    {{ $match['unread'] }}
                </span>
                @endif
            </a>
            @empty
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center mb-5 border border-white/10">
                    <span class="text-3xl">💫</span>
                </div>
                <h3 class="font-semibold text-lg mb-1">Pas encore de match</h3>
                <p class="text-sm text-white/30 mb-6 max-w-[240px]">Continue à swiper, ton crush t'attend peut-être</p>
                <a href="{{ route('swipe') }}" class="px-6 py-3 rounded-2xl text-sm font-semibold text-white" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                    Découvrir des profils
                </a>
            </div>
            @endforelse
        </div>
    </main>
    @include('components.feature-reminders')
    @include('components.bottom-nav')
</div>
</div>
@endsection

@push('scripts')
<script>
// ── Pull-to-refresh amélioré ──────────────────────────────────────
(function () {
    const scrollEl = document.querySelector('main') || document.documentElement;

    let startY = 0, pulling = false, pullDist = 0, releasing = false;
    const THRESHOLD = 60;

    const style = document.createElement('style');
    style.textContent = `
        @keyframes _ptr_spin { to { transform: rotate(360deg); } }
        ._ptr_spinning { animation: _ptr_spin 0.7s linear infinite !important; }
        @keyframes _ptr_pulse { 0%,100% { opacity:0.6; } 50% { opacity:1; } }
        ._ptr_pulse { animation: _ptr_pulse 1s ease-in-out infinite; }
    `;
    document.head.appendChild(style);

    const wrap = document.createElement('div');
    wrap.style.cssText = [
        'position:fixed', 'top:0', 'left:50%', 'transform:translateX(-50%)',
        'z-index:9999', 'pointer-events:none',
        'width:44px', 'height:0', 'overflow:visible',
        'display:flex', 'align-items:center', 'justify-content:center',
    ].join(';');
    wrap.innerHTML = `
        <div id="_ptr_circle" style="
            width:40px; height:40px; border-radius:50%;
            background: rgba(12,10,26,0.85);
            border: 2px solid rgba(255,94,108,0.25);
            backdrop-filter: blur(12px);
            display:flex; align-items:center; justify-content:center;
            opacity:0; transform:scale(0.5) translateY(-20px);
            transition: opacity 0.2s, transform 0.25s cubic-bezier(0.22,1,0.36,1);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        ">
            <svg id="_ptr_svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff5e6c" stroke-width="2.5" stroke-linecap="round">
                <path d="M12 5v14M5 12l7-7 7 7"/>
            </svg>
        </div>`;
    document.body.appendChild(wrap);

    const circle = wrap.querySelector('#_ptr_circle');
    const svg = wrap.querySelector('#_ptr_svg');

    function setPos(y, animated) {
        wrap.style.transition = animated ? 'height 0.3s cubic-bezier(0.22,1,0.36,1)' : 'none';
        wrap.style.height = y + 'px';
    }

    scrollEl.addEventListener('touchstart', e => {
        if (releasing || scrollEl.scrollTop > 0) return;
        startY = e.touches[0].clientY;
        pulling = true;
        pullDist = 0;
    }, { passive: true });

    scrollEl.addEventListener('touchmove', e => {
        if (!pulling || releasing) return;
        const dist = e.touches[0].clientY - startY;
        if (dist <= 0) { pullDist = 0; circle.style.opacity = '0'; circle.style.transform = 'scale(0.5) translateY(-20px)'; return; }
        pullDist = dist;

        const progress = Math.min(pullDist / THRESHOLD, 1);
        const y = Math.min(Math.sqrt(pullDist) * 5, 80);
        setPos(y, false);

        circle.style.opacity = Math.min(progress * 1.5, 1);
        circle.style.transform = `scale(${0.5 + progress * 0.5}) translateY(0px)`;

        // Rotation progressive de la flèche
        const rotation = progress * 180;
        svg.style.transform = `rotate(${rotation}deg)`;
        svg.style.transition = 'transform 0.1s';

        // Couleur de la bordure selon la progression
        circle.style.borderColor = progress >= 1
            ? 'rgba(255,94,108,0.7)'
            : 'rgba(255,94,108,0.25)';
    }, { passive: false });

    scrollEl.addEventListener('touchend', () => {
        if (!pulling) return;
        pulling = false;
        releasing = true;

        if (pullDist >= THRESHOLD) {
            // Lancer le refresh
            circle.style.borderColor = 'rgba(255,94,108,0.6)';
            svg.innerHTML = '<path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>';
            svg.classList.add('_ptr_spinning');
            circle.classList.add('_ptr_pulse');
            setPos(56, true);
            setTimeout(() => window.location.reload(), 500);
        } else {
            circle.style.opacity = '0';
            circle.style.transform = 'scale(0.5) translateY(-20px)';
            setPos(0, true);
            setTimeout(() => { releasing = false; }, 300);
        }
        pullDist = 0;
    }, { passive: true });
})();
</script>
@endpush