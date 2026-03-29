<!doctype html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <meta name="theme-color" content="#0c0a1a">
    <meta name="user-id" content="{{ Auth::id() }}">
    <title>Campus Crush</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --cc-accent: #ff5e6c;
            --cc-accent-glow: rgba(255,94,108,0.3);
            --cc-secondary: #ffc145;
        }
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; }

        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); }

        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none; z-index: 0;
        }

        .cc-surface { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(24px); }
        .cc-surface-raised { background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02)); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(40px); box-shadow: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.05); }
        .cc-gradient-text { background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cc-mono { font-family: 'Space Mono', monospace; }

        .profile-card {
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.35s ease;
            transform-origin: center 80%;
            will-change: transform;
        }
        .profile-card.dragging { transition: none; }

        .card-gradient {
            background: linear-gradient(to top,
                rgba(12,10,26,0.95) 0%,
                rgba(12,10,26,0.6) 35%,
                transparent 70%
            );
        }

        .like-stamp, .nope-stamp {
            opacity: 0; transition: opacity 0.2s ease;
            font-family: 'Space Mono', monospace;
        }

        .action-ring {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .action-ring::before {
            content: ''; position: absolute; inset: -3px;
            border-radius: 50%; opacity: 0;
            transition: opacity 0.3s;
        }
        .action-ring:hover::before { opacity: 1; }
        .action-ring:active { transform: scale(0.88); }

        .ring-pass::before { background: linear-gradient(135deg, #ff5e6c, #ff3d4f); }
        .ring-like::before { background: linear-gradient(135deg, #22c55e, #16a34a); }

        @keyframes matchBoom {
            0% { transform: scale(0.3) rotate(-10deg); opacity: 0; }
            50% { transform: scale(1.08) rotate(2deg); }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        .match-boom { animation: matchBoom 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

        @keyframes confetti {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(-100px) rotate(720deg); opacity: 0; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
    </style>
</head>

<body class="h-full text-white overflow-hidden">

{{-- Decorative orbs --}}
<div class="fixed top-20 -left-20 w-60 h-60 bg-purple-600 rounded-full blur-[120px] opacity-10 pointer-events-none"></div>
<div class="fixed bottom-40 -right-20 w-80 h-80 bg-[#ff5e6c] rounded-full blur-[120px] opacity-8 pointer-events-none"></div>

<div class="relative z-10 h-full w-full flex flex-col max-w-md mx-auto">

    {{-- Header --}}
    <nav class="flex items-center justify-between px-5 py-4 fade-up">
        <a href="{{ route('profile.show') }}" class="relative group">
            @if($user->profile && $user->profile->photo)
            <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-white/10 group-hover:ring-[#ff5e6c]/50 transition-all">
                <img src="{{ $user->profile->photo_url }}" class="w-full h-full object-cover" alt="">
            </div>
            @else
            <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center ring-2 ring-white/10 group-hover:ring-[#ff5e6c]/50 transition-all">
                <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            @endif
        </a>

        <div class="flex items-center gap-2">
            <span class="text-lg">🔥</span>
            <h1 class="text-xl font-bold cc-gradient-text tracking-tight">Campus Crush</h1>
        </div>

        <div class="flex items-center gap-1">
            @include('components.notification-bell')

            <a href="{{ route('matches') }}" class="relative p-2 rounded-xl hover:bg-white/5 transition">
                <svg class="w-6 h-6 text-white/50 hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                @if($matchesCount > 0)
                <span id="match-count" class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-gradient-to-r from-[#ff5e6c] to-[#ff8a5c] text-[10px] font-bold rounded-full flex items-center justify-center shadow-lg shadow-[#ff5e6c]/30">
                    {{ $matchesCount }}
                </span>
                @endif
            </a>
        </div>
    </nav>

    {{-- Card Stack --}}
    <div class="flex-1 px-4 flex items-center justify-center relative overflow-hidden fade-up delay-1">

        {{-- Empty State --}}
        <div id="empty-state" class="hidden absolute inset-0 flex flex-col items-center justify-center text-center px-8">
            <div class="w-24 h-24 rounded-3xl bg-white/5 flex items-center justify-center mb-6 border border-white/10">
                <span class="text-4xl">💫</span>
            </div>
            <h3 class="text-xl font-semibold mb-2">Plus de profils !</h3>
            <p class="text-white/40 text-sm">Reviens plus tard, de nouvelles personnes t'attendent</p>
        </div>

        {{-- Card --}}
        <div id="profile-card" class="profile-card w-full max-w-[340px] aspect-[3/4.2] rounded-[28px] overflow-hidden relative cursor-grab active:cursor-grabbing shadow-2xl shadow-black/40">

            <div class="absolute inset-0 bg-[#1a1145]">
                <img id="profile-photo" src="" alt="" class="w-full h-full object-cover" style="transition: opacity 0.4s ease;">
            </div>
            <div class="card-gradient absolute inset-0"></div>

            {{-- Stamps --}}
            <div class="like-stamp absolute top-8 left-6 px-5 py-2 border-[3px] border-green-400 rounded-xl transform -rotate-12 shadow-lg shadow-green-400/20">
                <span class="cc-mono text-green-400 font-bold text-xl tracking-widest">LIKE</span>
            </div>
            <div class="nope-stamp absolute top-8 right-6 px-5 py-2 border-[3px] border-[#ff5e6c] rounded-xl transform rotate-12 shadow-lg shadow-[#ff5e6c]/20">
                <span class="cc-mono text-[#ff5e6c] font-bold text-xl tracking-widest">NOPE</span>
            </div>

            {{-- Profile Info --}}
            <div class="absolute bottom-0 left-0 right-0 p-5 pb-6">
                <div class="flex items-end justify-between mb-3">
                    <div>
                        <h2 class="flex items-baseline gap-2">
                            <span id="profile-name" class="text-2xl font-bold"></span>
                            <span id="profile-age" class="text-lg font-light text-white/70"></span>
                        </h2>
                        <p id="profile-major" class="text-sm text-white/60 mt-0.5"></p>
                    </div>
                    <div id="compat-badge" class="cc-mono text-xs font-bold px-3 py-1.5 rounded-full bg-gradient-to-r from-[#ff5e6c] to-[#ffc145] shadow-lg">
                        <span class="compatibility">0</span>%
                    </div>
                </div>

                <p id="profile-bio" class="text-white/60 text-[13px] leading-relaxed line-clamp-2 mb-3"></p>

                <div id="profile-tags" class="flex flex-wrap gap-1.5"></div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="pb-6 pt-3 px-6 flex items-center justify-center gap-5 fade-up delay-2">
        <button id="btn-pass" class="action-ring ring-pass w-16 h-16 rounded-full cc-surface-raised flex items-center justify-center z-10 group">
            <svg class="w-7 h-7 text-[#ff5e6c] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <button id="btn-like" class="action-ring ring-like w-[72px] h-[72px] rounded-full flex items-center justify-center z-10 group"
                style="background: linear-gradient(135deg, rgba(34,197,94,0.15), rgba(22,163,74,0.05)); border: 1px solid rgba(34,197,94,0.2); backdrop-filter: blur(40px); box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
            <svg class="w-8 h-8 text-green-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </button>

        <button id="open-filter" class="w-12 h-12 rounded-full cc-surface flex items-center justify-center hover:bg-white/10 transition z-10">
            <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" d="M3 4h18M6 12h12M10 20h4"/>
            </svg>
        </button>
    </div>
</div>

{{-- Match Popup --}}
<div id="match-popup" class="hidden fixed inset-0 bg-black/80 backdrop-blur-xl flex items-center justify-center z-50 p-6">
    <div class="match-boom max-w-sm w-full text-center">
        <div class="text-7xl mb-4">✨</div>
        <h2 class="text-4xl font-extrabold cc-gradient-text mb-2">C'est un Match !</h2>
        <p class="text-white/50 mb-8">Toi et <span id="match-name" class="text-white font-medium"></span> vous plaisez</p>
        <div class="flex flex-col gap-3">
            <button id="send-message" class="w-full py-4 rounded-2xl font-semibold text-white" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145);">
                Envoyer un message 💬
            </button>
            <button id="keep-swiping" class="w-full py-4 rounded-2xl font-medium text-white/60 cc-surface hover:bg-white/10 transition">
                Continuer à explorer
            </button>
        </div>
    </div>
</div>

{{-- Filter Modal --}}
<div id="filter-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center z-50">
    <div class="cc-surface-raised rounded-t-3xl sm:rounded-3xl p-6 w-full max-w-sm sm:mx-4" style="animation: fadeUp 0.3s ease both;">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold">Filtrer</h2>
            <button id="close-filter" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition">
                <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Université</label>
                <select id="filter-university" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-[#ff5e6c]">
                    <option value="" style="background:#1a1145">Toutes</option>
                    @foreach($universities ?? [] as $uni)
                    <option value="{{ $uni->id }}" style="background:#1a1145">{{ $uni->short_name }} - {{ $uni->city }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">UFR</label>
                <select id="filter-ufr" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-[#ff5e6c]">
                    <option value="" style="background:#1a1145">Toutes</option>
                    <option value="SAT" style="background:#1a1145">SAT</option>
                    <option value="SJP" style="background:#1a1145">SJP</option>
                    <option value="LSH" style="background:#1a1145">LSH</option>
                    <option value="S2ATA" style="background:#1a1145">S2ATA</option>
                    <option value="SEFS" style="background:#1a1145">SEFS</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Promotion</label>
                <input id="filter-promotion" type="text" placeholder="ex: P30" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-[#ff5e6c] placeholder-white/30">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Âge min</label>
                    <input id="filter-age-min" type="number" min="17" max="60" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-[#ff5e6c]">
                </div>
                <div>
                    <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Âge max</label>
                    <input id="filter-age-max" type="number" min="17" max="60" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-[#ff5e6c]">
                </div>
            </div>
        </div>

        <button id="apply-filter" class="w-full mt-6 py-4 rounded-2xl font-semibold text-white" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
            Appliquer les filtres
        </button>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let profiles = @json($profilesForJs ?? []);
if (!Array.isArray(profiles)) profiles = Object.values(profiles);

let filters = {}, currentIndex = 0, startX = 0, currentX = 0, isDragging = false, currentMatchId = null;

const card = document.getElementById('profile-card');
const emptyState = document.getElementById('empty-state');
const likeStamp = card.querySelector('.like-stamp');
const nopeStamp = card.querySelector('.nope-stamp');

async function updateProfile(i) {
    if (i >= profiles.length) {
        await loadMore();
        if (i >= profiles.length) { card.style.display='none'; emptyState.classList.remove('hidden'); return; }
    }
    const p = profiles[i];
    document.getElementById('profile-name').textContent = p.name;
    document.getElementById('profile-age').textContent = p.age;
    document.getElementById('profile-major').textContent = [p.major, p.year, p.promotion].filter(Boolean).join(' · ');
    document.getElementById('profile-bio').textContent = p.bio || '';
    document.getElementById('match-name').textContent = p.name;
    document.querySelector('.compatibility').textContent = p.compatibility ?? 0;

    const photo = document.getElementById('profile-photo');
    photo.style.opacity = 0;
    setTimeout(() => { photo.src = p.photo || '/storage/profiles/default-avatar.png'; photo.style.opacity = 1; }, 80);

    document.getElementById('profile-tags').innerHTML = (p.tags||[]).map(t =>
        `<span class="px-2.5 py-1 bg-white/10 rounded-full text-[11px] text-white/60 border border-white/5">${t}</span>`
    ).join('');

    card.style.transform = ''; card.style.display = '';
}

async function swipe(dir) {
    if (currentIndex >= profiles.length) return;
    const p = profiles[currentIndex], m = window.innerWidth * 1.5;

    if (dir === 'right') {
        card.style.transform = `translateX(${m}px) rotate(25deg)`;
        try {
            const r = await fetch(`/like/${p.id}`, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} });
            const d = await r.json();
            if (d.match) { currentMatchId = d.match_id; document.getElementById('match-popup').classList.remove('hidden'); }
        } catch(e) {}
    } else {
        card.style.transform = `translateX(-${m}px) rotate(-25deg)`;
        try { await fetch(`/pass/${p.id}`, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} }); } catch(e) {}
    }
    setTimeout(() => { currentIndex++; updateProfile(currentIndex); }, 350);
}

async function loadMore() {
    try {
        const params = new URLSearchParams(filters);
        const r = await fetch('/load-profiles?'+params);
        let np = await r.json();
        if (!Array.isArray(np)) np = Object.values(np);
        if (np.length > 0) profiles.push(...np);
    } catch(e) {}
}

// Drag
function onStart(e) { isDragging=true; startX = e.type==='touchstart' ? e.touches[0].clientX : e.clientX; currentX=startX; card.classList.add('dragging'); }
function onMove(e) {
    if (!isDragging) return;
    currentX = e.type==='touchmove' ? e.touches[0].clientX : e.clientX;
    const dx = currentX - startX;
    card.style.transform = `translateX(${dx}px) rotate(${dx*0.04}deg)`;
    likeStamp.style.opacity = dx > 40 ? Math.min((dx-40)/80, 1) : 0;
    nopeStamp.style.opacity = dx < -40 ? Math.min((-dx-40)/80, 1) : 0;
}
function onEnd() {
    if (!isDragging) return; isDragging=false; card.classList.remove('dragging');
    const dx = currentX - startX;
    if (dx > 100) swipe('right');
    else if (dx < -100) swipe('left');
    else { card.style.transform=''; likeStamp.style.opacity=0; nopeStamp.style.opacity=0; }
}

card.addEventListener('mousedown', onStart);
card.addEventListener('touchstart', onStart, {passive:true});
document.addEventListener('mousemove', onMove);
document.addEventListener('touchmove', onMove, {passive:true});
document.addEventListener('mouseup', onEnd);
document.addEventListener('touchend', onEnd);

document.getElementById('btn-like').addEventListener('click', () => swipe('right'));
document.getElementById('btn-pass').addEventListener('click', () => swipe('left'));
document.getElementById('keep-swiping').addEventListener('click', () => document.getElementById('match-popup').classList.add('hidden'));
document.getElementById('send-message').addEventListener('click', () => { document.getElementById('match-popup').classList.add('hidden'); if(currentMatchId) window.location.href=`/messages/${currentMatchId}`; });

document.addEventListener('keydown', e => { if(e.key==='ArrowRight') swipe('right'); if(e.key==='ArrowLeft') swipe('left'); });

// Filters
document.getElementById('open-filter').onclick = () => document.getElementById('filter-modal').classList.remove('hidden');
document.getElementById('close-filter').onclick = () => document.getElementById('filter-modal').classList.add('hidden');
document.getElementById('apply-filter').onclick = async () => {
    filters = { ufr: document.getElementById('filter-ufr').value, promotion: document.getElementById('filter-promotion').value, age_min: document.getElementById('filter-age-min').value, age_max: document.getElementById('filter-age-max').value, university_id: document.getElementById('filter-university')?.value || '' };
    Object.keys(filters).forEach(k => { if(!filters[k]) delete filters[k]; });
    currentIndex=0; profiles.length=0; await loadMore(); updateProfile(0);
    document.getElementById('filter-modal').classList.add('hidden');
};

// Nav polling
setInterval(async () => {
    try {
        const r = await fetch('/nav-counts'), d = await r.json();
        const el = document.getElementById('match-count');
        if(el) { el.textContent=d.matches; el.classList.toggle('hidden', d.matches<=0); }
    } catch(e) {}
}, 30000);

updateProfile(0);
</script>

{{-- Real-time notifications --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.0/echo.iife.js"></script>
<script>
try {
   <?php
    $rKey = config('broadcasting.connections.reverb.key', 'campuscrush-key');
    $rHost = config('reverb.servers.reverb.hostname', 'localhost');
    $rPort = config('reverb.servers.reverb.port', 8080);
?>

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: '<?php echo $rKey; ?>',
    wsHost: '<?php echo $rHost; ?>',
    wsPort: <?php echo $rPort; ?>,
    wssPort: <?php echo $rPort; ?>,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});
} catch(e) { console.log('Reverb not available, real-time disabled'); }
</script>
@include('components.pwa-install-banner')
</body>
</html>
