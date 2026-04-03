{{-- resources/views/referral/index.blade.php --}}
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Parrainer · Campus Crush</title>
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

        body {
            background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
            min-height: 100vh;
            color: #fff;
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            opacity: 0.10;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .fade-up {
            animation: fadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .d1 {
            animation-delay: .08s
        }

        .d2 {
            animation-delay: .16s
        }

        .d3 {
            animation-delay: .24s
        }

        .d4 {
            animation-delay: .32s
        }

        .link-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .link-box:active {
            border-color: rgba(168, 85, 247, 0.5);
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            flex: 1;
        }

        .filleul-row {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
    </style>
</head>

<body>
    <div class="orb" style="width:240px;height:240px;background:#a855f7;top:-80px;right:-60px;"></div>
    <div class="orb" style="width:200px;height:200px;background:#ff5e6c;bottom:-60px;left:-60px;"></div>

    <div class="max-w-md mx-auto px-4 pb-24" style="padding-top:max(env(safe-area-inset-top,16px),16px);">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6 fade-up">
            <a href="{{ route('profile.show') }}" class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);">
                <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-bold">Parrainer un(e) ami(e)</h1>
        </div>

        {{-- Hero --}}
        <div class="rounded-2xl p-6 mb-5 fade-up d1" style="background:linear-gradient(135deg,rgba(168,85,247,0.12),rgba(255,94,108,0.08));border:1px solid rgba(168,85,247,0.20);">
            <div class="text-4xl mb-3">🎁</div>
            <h2 class="text-xl font-extrabold text-white mb-2">Invite tes amis,<br>gagne du premium</h2>
            <p class="text-sm leading-relaxed" style="color:rgba(255,255,255,0.45);">
                Pour chaque ami(e) qui s'inscrit et crée son profil via ton lien,
                tu gagnes <span class="text-white font-semibold">7 jours de premium</span> offerts automatiquement.
            </p>
        </div>

        {{-- Stats --}}
        <div class="flex gap-3 mb-5 fade-up d2">
            <div class="stat-card">
                <p class="text-2xl font-extrabold text-white">{{ $rewardedCount }}</p>
                <p class="text-[11px] mt-1" style="color:rgba(255,255,255,0.35);">Ami(e)s parrainé(e)s</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-extrabold" style="background:linear-gradient(135deg,#a855f7,#ff5e6c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">{{ $totalDaysEarned }}j</p>
                <p class="text-[11px] mt-1" style="color:rgba(255,255,255,0.35);">Jours gagnés</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-extrabold" style="color:rgba(255,255,255,0.35);">{{ $pendingCount }}</p>
                <p class="text-[11px] mt-1" style="color:rgba(255,255,255,0.35);">En attente</p>
            </div>
        </div>

        {{-- Lien de parrainage --}}
        <div class="mb-5 fade-up d3">
            <p class="text-xs font-semibold mb-2" style="color:rgba(255,255,255,0.35);letter-spacing:0.06em;">TON LIEN DE PARRAINAGE</p>

            <div class="link-box" onclick="copyLink()" id="link-box">
                <svg class="w-4 h-4 flex-shrink-0" style="color:rgba(168,85,247,0.8)" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span class="text-sm flex-1 truncate" style="color:rgba(255,255,255,0.60);" id="link-text">{{ $referralLink }}</span>
                <span class="text-xs font-semibold flex-shrink-0" style="color:#a855f7;" id="copy-label">Copier</span>
            </div>

            {{-- Ton code --}}
            <div class="mt-3 flex items-center justify-between px-4 py-3 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                <span class="text-xs" style="color:rgba(255,255,255,0.35);">Ton code</span>
                <span class="text-sm font-bold tracking-widest" style="color:#a855f7;">{{ $user->referral_code }}</span>
            </div>
        </div>

        {{-- Partager --}}
        <div class="grid grid-cols-2 gap-3 mb-6 fade-up d3">
            <button onclick="shareWhatsApp()" class="flex items-center justify-center gap-2 py-3 rounded-xl font-semibold text-sm transition active:scale-95" style="background:rgba(37,211,102,0.12);border:1px solid rgba(37,211,102,0.25);color:#25d366;">
                <span class="text-base">💬</span> WhatsApp
            </button>
            <button onclick="shareNative()" class="flex items-center justify-center gap-2 py-3 rounded-xl font-semibold text-sm transition active:scale-95" style="background:rgba(168,85,247,0.10);border:1px solid rgba(168,85,247,0.20);color:#a855f7;">
                <span class="text-base">📤</span> Partager
            </button>
        </div>

        {{-- Comment ça marche --}}
        <div class="mb-6 fade-up d4">
            <p class="text-xs font-semibold mb-3" style="color:rgba(255,255,255,0.35);letter-spacing:0.06em;">COMMENT ÇA MARCHE</p>
            <div class="space-y-2">
                @foreach([
                ['1', 'Partage ton lien ou code à tes ami(e)s'],
                ['2', 'Ils s\'inscrivent et créent leur profil'],
                ['3', 'Tu reçois 7 jours de premium automatiquement 🎉'],
                ] as [$num, $text])
                <div class="flex items-center gap-3 py-2">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold flex-shrink-0" style="background:rgba(168,85,247,0.15);color:#a855f7;">{{ $num }}</span>
                    <span class="text-sm" style="color:rgba(255,255,255,0.55);">{{ $text }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Liste des filleuls --}}
        @if($referrals->count() > 0)
        <div class="fade-up d4">
            <p class="text-xs font-semibold mb-3" style="color:rgba(255,255,255,0.35);letter-spacing:0.06em;">MES FILLEUL(E)S ({{ $referrals->count() }})</p>
            <div class="space-y-2">
                @foreach($referrals as $ref)
                <div class="filleul-row">
                    <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0" style="background:rgba(255,255,255,0.08);">
                        @if($ref->referred->profile?->photo_url)
                        <img src="{{ $ref->referred->profile->photo_url }}" class="w-full h-full object-cover" alt="">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-xs font-bold" style="color:#a855f7;">
                            {{ strtoupper(substr($ref->referred->name, 0, 2)) }}
                        </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $ref->referred->name }}</p>
                        <p class="text-[11px]" style="color:rgba(255,255,255,0.35);">{{ $ref->created_at->diffForHumans() }}</p>
                    </div>
                    @if($ref->rewarded)
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full" style="background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.20);color:#22c55e;">+7 jours ✓</span>
                    @else
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:rgba(255,255,255,0.30);">En attente</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="text-center py-8 fade-up d4">
            <div class="text-4xl mb-3">👥</div>
            <p class="text-sm" style="color:rgba(255,255,255,0.30);">Aucun filleul pour l'instant.<br>Partage ton lien pour commencer !</p>
        </div>
        @endif

    </div>

    @include('components.bottom-nav')

    <script>
        const referralLink = @json($referralLink);
        const referralCode = @json($user - > referral_code);

        function copyLink() {
            navigator.clipboard.writeText(referralLink).then(() => {
                const label = document.getElementById('copy-label');
                label.textContent = 'Copié ✓';
                label.style.color = '#22c55e';
                setTimeout(() => {
                    label.textContent = 'Copier';
                    label.style.color = '#a855f7';
                }, 2000);
            }).catch(() => {
                // Fallback pour les navigateurs sans clipboard API
                const input = document.createElement('input');
                input.value = referralLink;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
            });
        }

        function shareWhatsApp() {
            const msg = encodeURIComponent(
                '🔥 Rejoins-moi sur Campus Crush, l\'appli de rencontres pour étudiants ! 💘\n' +
                'Utilise mon lien : ' + referralLink
            );
            window.open('https://wa.me/?text=' + msg, '_blank');
        }

        function shareNative() {
            if (navigator.share) {
                navigator.share({
                    title: 'Campus Crush',
                    text: '🔥 Rejoins-moi sur Campus Crush ! Utilise mon code : ' + referralCode,
                    url: referralLink,
                });
            } else {
                copyLink();
            }
        }
    </script>
</body>

</html>