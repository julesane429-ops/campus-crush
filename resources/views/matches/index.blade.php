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
// ── Pull-to-refresh ───────────────────────────────────────────────
(function () {
    // Sur cette page le scroll est dans <main class="overflow-y-auto">, pas window
    const scrollEl = document.querySelector('main') || document.documentElement;

    let startY = 0, pulling = false, pullDist = 0, releasing = false;
    const THRESHOLD = 52; // px — seuil réduit pour mobile

    // ── Indicateur ────────────────────────────────────────────────
    const style = document.createElement('style');
    style.textContent = `
        @keyframes _ptr_spin { to { transform: rotate(360deg); } }
        ._ptr_spinning { animation: _ptr_spin 0.7s linear infinite !important; }
    `;
    document.head.appendChild(style);

    const wrap = document.createElement('div');
    // Pas de transition pendant le drag — on l'ajoute seulement au relâchement
    wrap.style.cssText = [
        'position:fixed', 'top:0', 'left:0', 'right:0', 'z-index:9999',
        'display:flex', 'align-items:center', 'justify-content:center',
        'height:0', 'overflow:hidden', 'pointer-events:none',
        'background:rgba(255,94,108,0.09)',
        'backdrop-filter:blur(14px)',
        'border-bottom:1px solid rgba(255,94,108,0.15)',
    ].join(';');
    wrap.innerHTML = `
        <div id="_ptr_inner" style="display:flex;align-items:center;gap:10px;opacity:0;">
            <span id="_ptr_icon" style="font-size:18px;display:inline-block;transition:transform 0.15s ease;">↓</span>
            <span id="_ptr_txt" style="font-size:12px;color:rgba(255,255,255,0.55);font-family:'Sora',sans-serif;font-weight:600;letter-spacing:0.01em;">Tirer pour rafraîchir</span>
        </div>`;
    document.body.appendChild(wrap);

    const inner = wrap.querySelector('#_ptr_inner');
    const icon  = wrap.querySelector('#_ptr_icon');
    const txt   = wrap.querySelector('#_ptr_txt');

    function setHeight(h, animated) {
        wrap.style.transition = animated ? 'height 0.22s cubic-bezier(0.22,1,0.36,1)' : 'none';
        wrap.style.height = h + 'px';
    }

    // ── Touch listeners sur le vrai container ─────────────────────
    scrollEl.addEventListener('touchstart', e => {
        if (releasing || scrollEl.scrollTop > 0) return;
        startY   = e.touches[0].clientY;
        pulling  = true;
        pullDist = 0;
    }, { passive: true });

    scrollEl.addEventListener('touchmove', e => {
        if (!pulling || releasing) return;
        const dist = e.touches[0].clientY - startY;
        if (dist <= 0) { pullDist = 0; setHeight(0, false); inner.style.opacity = '0'; return; }
        pullDist = dist;
        e.preventDefault();

        // Hauteur suit le doigt sans lag (résistance légère via sqrt)
        const h = Math.min(Math.sqrt(pullDist) * 6.5, 70);
        setHeight(h, false); // pas d'animation pendant le drag

        // Opacité proportionnelle — visible dès 10px
        inner.style.opacity = Math.min(pullDist / (THRESHOLD * 0.6), 1);

        const ready = pullDist >= THRESHOLD;
        icon.style.transform = ready ? 'rotate(180deg)' : 'rotate(0deg)';
        icon.textContent = ready ? '↑' : '↓';
        txt.textContent  = ready ? 'Relâcher pour rafraîchir' : 'Tirer pour rafraîchir';
        txt.style.color  = ready ? 'rgba(255,94,108,0.9)' : 'rgba(255,255,255,0.55)';
    }, { passive: false });

    scrollEl.addEventListener('touchend', () => {
        if (!pulling) return;
        pulling   = false;
        releasing = true;

        if (pullDist >= THRESHOLD) {
            // État chargement : indicateur fixe + spinner
            icon.textContent = '↻';
            icon.classList.add('_ptr_spinning');
            txt.textContent  = 'Chargement...';
            txt.style.color  = 'rgba(255,255,255,0.6)';
            inner.style.opacity = '1';
            setHeight(52, true); // fixe à 52px avec animation
            // Reload après 600ms pour que l'utilisateur voit l'état de chargement
            setTimeout(() => window.location.reload(), 600);
        } else {
            // Rétraction animée
            inner.style.opacity = '0';
            setHeight(0, true);
            setTimeout(() => { releasing = false; }, 250);
        }
        pullDist = 0;
    }, { passive: true });
})();
</script>
@endpush