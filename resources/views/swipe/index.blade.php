<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Campus Crush</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Sora', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            touch-action: pan-y;
        }

        body {
            background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
        }

        /* ═══ CARD STACK ═══ */
        .card-stack {
            position: relative;
            width: 100%;
            max-width: 340px;
            aspect-ratio: 3/4.2;
            margin: 0 auto;
        }

        .swipe-card {
            position: absolute;
            inset: 0;
            border-radius: 24px;
            overflow: hidden;
            will-change: transform, opacity;
            touch-action: none;
            cursor: grab;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.05);
        }

        .swipe-card:active {
            cursor: grabbing;
        }

        .swipe-card.card-behind-1 {
            transform: scale(0.95) translateY(12px);
            opacity: 0.7;
            pointer-events: none;
            filter: brightness(0.7);
        }

        .swipe-card.card-behind-2 {
            transform: scale(0.90) translateY(24px);
            opacity: 0.4;
            pointer-events: none;
            filter: brightness(0.5);
        }

        .swipe-card.card-behind-1,
        .swipe-card.card-behind-2 {
            transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.4s ease, filter 0.4s ease;
        }

        /* Card exit animations */
        .swipe-card.exit-left {
            transition: transform 0.45s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.4s ease;
            transform: translateX(-140%) rotate(-18deg) !important;
            opacity: 0 !important;
        }

        .swipe-card.exit-right {
            transition: transform 0.45s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.4s ease;
            transform: translateX(140%) rotate(18deg) !important;
            opacity: 0 !important;
        }

        /* Spring back */
        .swipe-card.spring-back {
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* Card gradient overlay */
        .card-gradient {
            background: linear-gradient(0deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.4) 35%, rgba(0, 0, 0, 0.05) 60%, transparent 100%);
        }

        /* Swipe stamps */
        .stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.5);
            opacity: 0;
            transition: opacity 0.15s, transform 0.15s;
            pointer-events: none;
            z-index: 10;
        }

        .stamp-like {
            color: #22c55e;
            border: 4px solid #22c55e;
        }

        .stamp-nope {
            color: #ef4444;
            border: 4px solid #ef4444;
        }

        .stamp.visible {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        /* ═══ SURFACES ═══ */
        .cc-surface {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(24px);
        }

        .cc-gradient-text {
            background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ═══ ACTION BUTTONS ═══ */
        .action-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s cubic-bezier(0.22, 1, 0.36, 1);
            flex-shrink: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .action-btn:active {
            transform: scale(0.88);
        }

        .action-btn-pass {
            background: rgba(239, 68, 68, 0.12);
            border: 2px solid rgba(239, 68, 68, 0.25);
        }

        .action-btn-pass:active {
            background: rgba(239, 68, 68, 0.25);
        }

        .action-btn-like {
            background: rgba(34, 197, 94, 0.12);
            border: 2px solid rgba(34, 197, 94, 0.25);
        }

        .action-btn-like:active {
            background: rgba(34, 197, 94, 0.25);
        }

        .action-btn-filter {
            background: rgba(255, 255, 255, 0.06);
            border: 2px solid rgba(255, 255, 255, 0.08);
            width: 44px;
            height: 44px;
        }

        /* ═══ ORBS ═══ */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            opacity: 0.1;
        }

        /* ═══ ANIMATIONS ═══ */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .fade-up {
            animation: fadeUp 0.5s ease both;
        }

        @keyframes matchPop {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.08);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .match-pop {
            animation: matchPop 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes confetti {
            0% {
                transform: translateY(0) rotate(0);
                opacity: 1;
            }

            100% {
                transform: translateY(-80px) rotate(360deg);
                opacity: 0;
            }
        }

        /* ═══ EMPTY STATE ═══ */
        .empty-float {
            animation: emptyFloat 4s ease-in-out infinite;
        }

        @keyframes emptyFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        /* ═══ FILTER MODAL ═══ */
        .modal-overlay {
            transition: opacity 0.3s ease;
        }

        .modal-content {
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease;
        }

        /* Safe area */
        .safe-bottom {
            padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);
        }
    </style>
</head>

<body class="h-full text-white overflow-hidden">

    <div class="orb" style="width:280px;height:280px;background:#ff5e6c;top:-80px;right:-80px;"></div>
    <div class="orb" style="width:220px;height:220px;background:#a855f7;bottom:-60px;left:-60px;"></div>

    <div class="h-full w-full flex flex-col max-w-md mx-auto relative">

        {{-- ═══════════════════════════════ --}}
        {{-- HEADER --}}
        {{-- ═══════════════════════════════ --}}
        <header class="flex items-center justify-between px-4 pt-3 pb-2 flex-shrink-0 fade-up" style="padding-top: max(env(safe-area-inset-top, 12px), 12px);">
            <a href="{{ route('profile.show') }}" class="relative w-9 h-9 rounded-full overflow-hidden border-2 border-white/10 flex-shrink-0">
                <img src="{{ $user->profile->photo_url }}" class="w-full h-full object-cover" alt="">
                @if(($user->streak_days ?? 0) >= 3)
                <span class="absolute -bottom-0.5 -right-0.5 text-[10px] leading-none">{{ $user->streak_badge }}</span>
                @endif
            </a>

            <div class="flex items-center gap-1.5">
                <span class="text-lg">🔥</span>
                <h1 class="text-lg font-bold cc-gradient-text">Campus Crush</h1>
            </div>

            <div class="flex items-center gap-1">
                @include('components.notification-bell')
                <a href="{{ route('matches') }}" class="relative p-2 rounded-xl hover:bg-white/5 transition">
                    <svg class="w-5 h-5 text-white/50" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                    </svg>
                    <?php $unread = $messagesCount ?? 0; ?>
                    <?php if ($unread > 0): ?>
                        <span id="unread-badge" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-[#ff5e6c] text-[10px] font-bold rounded-full flex items-center justify-center px-1"><?php echo $unread; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </header>
        @include('components.daily-match')
        {{-- ═══════════════════════════════ --}}
        {{-- CARD AREA --}}
        {{-- ═══════════════════════════════ --}}
        <main class="flex-1 flex items-center justify-center px-4 overflow-hidden relative" id="card-area">

            {{-- Empty state --}}
            <div id="empty-state" class="hidden absolute inset-0 flex flex-col items-center justify-center text-center px-8">
                <div class="w-20 h-20 rounded-3xl flex items-center justify-center mb-5 empty-float" style="background: linear-gradient(135deg, rgba(255,94,108,0.1), rgba(168,85,247,0.1)); border: 1px solid rgba(255,94,108,0.1);">
                    <span class="text-4xl">💫</span>
                </div>
                <h3 class="text-xl font-bold text-white/80 mb-2">Plus de profils !</h3>
                <p class="text-white/30 text-sm leading-relaxed">Reviens plus tard, de nouvelles personnes t'attendent</p>
            </div>

            {{-- Card stack container --}}
            <div class="card-stack" id="card-stack"></div>
        </main>

        {{-- ═══════════════════════════════ --}}
        {{-- ACTION BUTTONS --}}
        {{-- ═══════════════════════════════ --}}
        <div class="flex-shrink-0 px-6 pb-2 fade-up" style="animation-delay:0.15s">
            <div class="flex items-center justify-center gap-5">
                {{-- Pass --}}
                <button id="btn-pass" class="action-btn action-btn-pass">
                    <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Like --}}
                <button id="btn-like" class="action-btn action-btn-like" style="width:64px;height:64px;">
                    <svg class="w-8 h-8 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                    </svg>
                </button>

                {{-- Filter --}}
                <button id="open-filter" class="action-btn action-btn-filter">
                    <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M3 4h18M6 12h12M10 20h4" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════ --}}
        {{-- BOTTOM NAV --}}
        {{-- ═══════════════════════════════ --}}
        @include('components.bottom-nav')
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- MATCH POPUP --}}
    {{-- ═══════════════════════════════ --}}
    <div id="match-popup" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6" style="background:rgba(12,10,26,0.92); backdrop-filter:blur(20px);">
        <div class="match-pop text-center max-w-xs w-full">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-3xl font-extrabold cc-gradient-text mb-2">C'est un Match !</h2>
            <p class="text-white/40 text-sm mb-8">Toi et <span id="match-name" class="text-white/70 font-medium">...</span> vous êtes likés</p>
            <div class="flex flex-col gap-3">
                <button id="send-message" class="w-full py-3.5 rounded-2xl font-semibold text-white text-sm transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                    Envoyer un message 💬
                </button>
                <button id="keep-swiping" class="w-full py-3.5 rounded-2xl font-medium text-white/40 text-sm border border-white/10 hover:bg-white/5 transition">
                    Continuer à swiper
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- FILTER MODAL --}}
    {{-- ═══════════════════════════════ --}}
    <div id="filter-modal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="modal-overlay absolute inset-0" style="background:rgba(12,10,26,0.8); backdrop-filter:blur(10px);" onclick="closeFilter()"></div>
        <div class="modal-content relative w-full max-w-md mx-4 mb-4 sm:mb-0 rounded-3xl p-6" style="background: linear-gradient(135deg, rgba(26,17,69,0.98), rgba(15,26,58,0.98)); border: 1px solid rgba(255,255,255,0.08);">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold">Filtres</h2>
                <button onclick="closeFilter()" class="p-2 rounded-xl hover:bg-white/5 transition">
                    <svg class="w-5 h-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="text-xs text-white/40 font-medium mb-2 block">Université</label>
                    <select id="filter-university" class="w-full px-4 py-3 rounded-xl text-sm text-white outline-none" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08);">
                        <option value="" style="color:#000">Toutes</option>
                        @foreach($universities as $uni)
                        <option value="{{ $uni->id }}" style="color:#000">{{ $uni->short_name }} - {{ $uni->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-white/40 font-medium mb-2 block">UFR</label>
                    <select id="filter-ufr" class="w-full px-4 py-3 rounded-xl text-sm text-white outline-none" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08);">
                        <option value="" style="color:#000">Toutes</option>
                        <option value="SAT" style="color:#000">SAT</option>
                        <option value="SJP" style="color:#000">SJP</option>
                        <option value="S2ATA" style="color:#000">S2ATA</option>
                        <option value="LSH" style="color:#000">LSH</option>
                        <option value="SEFS" style="color:#000">SEFS</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-white/40 font-medium mb-2 block">Âge min</label>
                        <input id="filter-age-min" type="number" placeholder="18" class="w-full px-4 py-3 rounded-xl text-sm text-white placeholder-white/20 outline-none" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08);">
                    </div>
                    <div>
                        <label class="text-xs text-white/40 font-medium mb-2 block">Âge max</label>
                        <input id="filter-age-max" type="number" placeholder="30" class="w-full px-4 py-3 rounded-xl text-sm text-white placeholder-white/20 outline-none" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08);">
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="resetFilters()" class="flex-1 py-3 rounded-xl text-sm font-medium text-white/40 border border-white/10 hover:bg-white/5 transition">Réinitialiser</button>
                <button onclick="applyFilters()" class="flex-1 py-3 rounded-xl text-sm font-semibold text-white transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">Appliquer</button>
            </div>
        </div>
    </div>

    <script>
        // ═══════════════════════════════════════════
        // DATA
        // ═══════════════════════════════════════════
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let profiles = @json($profilesForJs ?? []);
        let currentIndex = 0;
        let filters = {};
        let currentMatchId = null;
        let isDragging = false;
        let startX = 0,
            startY = 0,
            currentDragX = 0;
        let activeCard = null;

        const cardStack = document.getElementById('card-stack');
        const emptyState = document.getElementById('empty-state');
        const matchPopup = document.getElementById('match-popup');
        const btnLike = document.getElementById('btn-like');
        const btnPass = document.getElementById('btn-pass');

        // ═══════════════════════════════════════════
        // RENDER CARDS (show up to 3 stacked)
        // ═══════════════════════════════════════════
        function renderCards() {
            cardStack.innerHTML = '';

            if (currentIndex >= profiles.length) {
                emptyState.classList.remove('hidden');
                return;
            }
            emptyState.classList.add('hidden');

            // Render up to 3 cards (back to front)
            const cardsToShow = Math.min(3, profiles.length - currentIndex);

            for (let i = cardsToShow - 1; i >= 0; i--) {
                const idx = currentIndex + i;
                const p = profiles[idx];
                const card = createCardElement(p, i);
                cardStack.appendChild(card);
            }

            // Attach drag to top card
            activeCard = cardStack.lastElementChild;
            if (activeCard) attachDrag(activeCard);
        }

        function createCardElement(profile, stackPos) {
            const card = document.createElement('div');
            card.className = 'swipe-card' + (stackPos === 1 ? ' card-behind-1' : stackPos === 2 ? ' card-behind-2' : '');
            card.dataset.userId = profile.id;

            const tags = (profile.tags || []).slice(0, 4).map(t =>
                '<span style="background:rgba(255,255,255,0.12); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.08);" class="px-2.5 py-1 rounded-full text-[10px] text-white/80 font-medium">' + escapeHtml(t) + '</span>'
            ).join('');

            card.innerHTML = `
            <img src="${profile.photo}" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy" onerror="this.src='https://ui-avatars.com/api/?background=1a1145&color=ff5e6c&bold=true&name=${encodeURIComponent(profile.name)}'">
            <div class="card-gradient absolute inset-0"></div>

            <div class="stamp stamp-like rounded-xl px-5 py-2 font-extrabold text-2xl tracking-widest" style="transform-origin:center; left:30%; top:40%;">LIKE</div>
            <div class="stamp stamp-nope rounded-xl px-5 py-2 font-extrabold text-2xl tracking-widest" style="transform-origin:center; left:55%; top:40%;">NOPE</div>

            <div class="absolute bottom-0 left-0 right-0 p-5">
                <div class="flex items-end justify-between mb-2">
                    <div>
                        <div class="flex items-baseline gap-2">
    <h2 class="text-2xl font-bold text-white leading-none">${escapeHtml(profile.name)}</h2>
    <span class="text-lg text-white/60 font-light">${profile.age}</span>
</div>
${profile.badge === 'queen'
    ? `<span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(255,193,69,0.15);border:1px solid rgba(255,193,69,0.35);color:#ffc145;margin-top:4px;">👑 Campus Queen</span>`
    : profile.badge === 'king'
    ? `<span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(143,199,64,0.15);border:1px solid rgba(143,199,64,0.35);color:#8fc740;margin-top:4px;">🏆 Campus King</span>`
    : ''}
    ${profile.boosted
    ? `<span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(255,193,69,0.15);border:1px solid rgba(255,193,69,0.35);color:#ffc145;margin-top:4px;">🚀 Boosté</span>`
    : ''}
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <svg class="w-3.5 h-3.5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span class="text-xs text-white/50">${escapeHtml(profile.major || '')} ${profile.year ? '• ' + profile.year : ''}</span>
                        </div>
                        ${profile.university ? '<p class="text-[10px] text-white/30 mt-0.5">🎓 ' + escapeHtml(profile.university) + '</p>' : ''}
                    </div>
                    ${profile.compatibility ? '<div class="flex-shrink-0 px-2.5 py-1 rounded-xl text-[11px] font-semibold" style="background:rgba(255,94,108,0.2); border:1px solid rgba(255,94,108,0.15); color:#ff8a5c;">💜 ' + profile.compatibility + '%</div>' : ''}
                </div>

                ${profile.bio ? '<p class="text-xs text-white/50 leading-relaxed line-clamp-2 mb-2.5">' + escapeHtml(profile.bio) + '</p>' : ''}

                ${tags ? '<div class="flex flex-wrap gap-1.5">' + tags + '</div>' : ''}
            </div>
        `;

            return card;
        }

        // ═══════════════════════════════════════════
        // DRAG / TOUCH HANDLING
        // ═══════════════════════════════════════════
        function attachDrag(card) {
            card.addEventListener('touchstart', onDragStart, {
                passive: true
            });
            card.addEventListener('mousedown', onDragStart);
        }

        function onDragStart(e) {
            if (!activeCard) return;
            isDragging = true;
            startX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
            startY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
            currentDragX = 0;
            activeCard.classList.remove('spring-back');
        }

        function onDragMove(e) {
            if (!isDragging || !activeCard) return;
            const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
            currentDragX = clientX - startX;
            const rotation = currentDragX * 0.08;
            const absX = Math.abs(currentDragX);

            activeCard.style.transform = 'translateX(' + currentDragX + 'px) rotate(' + rotation + 'deg)';

            // Show stamps
            const likeStamp = activeCard.querySelector('.stamp-like');
            const nopeStamp = activeCard.querySelector('.stamp-nope');

            if (currentDragX > 40) {
                likeStamp.classList.add('visible');
                nopeStamp.classList.remove('visible');
            } else if (currentDragX < -40) {
                nopeStamp.classList.add('visible');
                likeStamp.classList.remove('visible');
            } else {
                likeStamp.classList.remove('visible');
                nopeStamp.classList.remove('visible');
            }

            // Promote cards behind
            const cards = cardStack.querySelectorAll('.swipe-card');
            const progress = Math.min(absX / 150, 1);
            cards.forEach((c, i) => {
                if (c === activeCard) return;
                if (c.classList.contains('card-behind-1')) {
                    c.style.transform = 'scale(' + (0.95 + 0.05 * progress) + ') translateY(' + (12 - 12 * progress) + 'px)';
                    c.style.opacity = 0.7 + 0.3 * progress;
                    c.style.filter = 'brightness(' + (0.7 + 0.3 * progress) + ')';
                } else if (c.classList.contains('card-behind-2')) {
                    c.style.transform = 'scale(' + (0.90 + 0.05 * progress) + ') translateY(' + (24 - 12 * progress) + 'px)';
                    c.style.opacity = 0.4 + 0.3 * progress;
                    c.style.filter = 'brightness(' + (0.5 + 0.2 * progress) + ')';
                }
            });
        }

        function onDragEnd() {
            if (!isDragging || !activeCard) return;
            isDragging = false;

            if (currentDragX > 100) {
                swipe('right');
            } else if (currentDragX < -100) {
                swipe('left');
            } else {
                // Spring back
                activeCard.classList.add('spring-back');
                activeCard.style.transform = '';
                const likeStamp = activeCard.querySelector('.stamp-like');
                const nopeStamp = activeCard.querySelector('.stamp-nope');
                if (likeStamp) likeStamp.classList.remove('visible');
                if (nopeStamp) nopeStamp.classList.remove('visible');

                // Reset behind cards
                cardStack.querySelectorAll('.card-behind-1').forEach(c => {
                    c.style.transform = '';
                    c.style.opacity = '';
                    c.style.filter = '';
                });
                cardStack.querySelectorAll('.card-behind-2').forEach(c => {
                    c.style.transform = '';
                    c.style.opacity = '';
                    c.style.filter = '';
                });
            }
        }

        document.addEventListener('mousemove', onDragMove);
        document.addEventListener('touchmove', onDragMove, {
            passive: true
        });
        document.addEventListener('mouseup', onDragEnd);
        document.addEventListener('touchend', onDragEnd);

        // ═══════════════════════════════════════════
        // SWIPE ACTION
        // ═══════════════════════════════════════════
        async function swipe(direction) {
            if (!activeCard || currentIndex >= profiles.length) return;
            const profile = profiles[currentIndex];
            const card = activeCard;
            activeCard = null; // prevent double swipe

            // Animate exit
            card.classList.add(direction === 'right' ? 'exit-right' : 'exit-left');

            // API call
            if (direction === 'right') {
                try {
                    const res = await fetch('/like/' + profile.id, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.match) {
                        currentMatchId = data.match_id;
                        document.getElementById('match-name').textContent = profile.name;
                        setTimeout(() => matchPopup.classList.remove('hidden'), 400);
                    }
                } catch (e) {
                    console.error('Like error:', e);
                }
            } else {
                fetch('/pass/' + profile.id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                }).catch(() => {});
            }

            // Next card after animation
            setTimeout(() => {
                currentIndex++;
                renderCards();
            }, 350);
        }

        // ═══════════════════════════════════════════
        // BUTTON CLICKS
        // ═══════════════════════════════════════════
        btnLike.addEventListener('click', () => {
            if (activeCard) {
                const likeStamp = activeCard.querySelector('.stamp-like');
                if (likeStamp) likeStamp.classList.add('visible');
            }
            swipe('right');
        });

        btnPass.addEventListener('click', () => {
            if (activeCard) {
                const nopeStamp = activeCard.querySelector('.stamp-nope');
                if (nopeStamp) nopeStamp.classList.add('visible');
            }
            swipe('left');
        });

        // ═══════════════════════════════════════════
        // MATCH POPUP
        // ═══════════════════════════════════════════
        document.getElementById('keep-swiping').addEventListener('click', () => matchPopup.classList.add('hidden'));
        document.getElementById('send-message').addEventListener('click', () => {
            matchPopup.classList.add('hidden');
            if (currentMatchId) window.location.href = '/messages/' + currentMatchId;
        });

        // ═══════════════════════════════════════════
        // FILTERS
        // ═══════════════════════════════════════════
        document.getElementById('open-filter').addEventListener('click', () => document.getElementById('filter-modal').classList.remove('hidden'));

        function closeFilter() {
            document.getElementById('filter-modal').classList.add('hidden');
        }

        function resetFilters() {
            document.getElementById('filter-ufr').value = '';
            document.getElementById('filter-age-min').value = '';
            document.getElementById('filter-age-max').value = '';
            document.getElementById('filter-university').value = '';
        }

        async function applyFilters() {
            filters = {
                ufr: document.getElementById('filter-ufr').value,
                age_min: document.getElementById('filter-age-min').value,
                age_max: document.getElementById('filter-age-max').value,
                university_id: document.getElementById('filter-university').value,
            };

            currentIndex = 0;
            profiles = [];
            await loadMoreProfiles();
            renderCards();
            closeFilter();
        }

        async function loadMoreProfiles() {
            try {
                const params = new URLSearchParams(filters);
                const res = await fetch('/load-profiles?' + params);
                const data = await res.json();
                if (data.length > 0) profiles.push(...data);
            } catch (e) {
                console.error('Load error:', e);
            }
        }

        // ═══════════════════════════════════════════
        // NAV COUNTS
        // ═══════════════════════════════════════════
        async function refreshNavCounts() {
            try {
                const res = await fetch('/nav-counts');
                const data = await res.json();
                const ub = document.getElementById('unread-badge');
                if (ub) {
                    if (data.messages > 0) {
                        ub.textContent = data.messages;
                        ub.classList.remove('hidden');
                    } else {
                        ub.classList.add('hidden');
                    }
                }
            } catch (e) {}
        }
        setInterval(refreshNavCounts, 8000);

        // ═══════════════════════════════════════════
        // HELPERS
        // ═══════════════════════════════════════════
        function escapeHtml(text) {
            if (!text) return '';
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }

        // ═══════════════════════════════════════════
        // INIT
        // ═══════════════════════════════════════════
        renderCards();
    </script>

    @include('components.pwa-install-banner')
    @auth
    @include('components.push-notifications')
    @endauth
    @include('components.features-popup')
    @include('components.feature-reminders')
</body>

</html>