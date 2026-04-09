<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Campus Crush - Chat</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Sora', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            height: 100%;
        }

        body {
            height: 100%;
            overflow: hidden;
            background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
        }

        .cc-surface {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(24px);
        }

        .bubble-sent {
            background: linear-gradient(135deg, #ff5e6c 0%, #ff8a5c 100%);
            border-radius: 20px 20px 6px 20px;
            box-shadow: 0 4px 20px rgba(255, 94, 108, 0.12);
        }

        .bubble-received {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px 20px 20px 6px;
        }

        @keyframes msgSlideIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .msg-in {
            animation: msgSlideIn 0.25s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes typingDot {

            0%,
            60%,
            100% {
                transform: translateY(0);
                opacity: 0.4;
            }

            30% {
                transform: translateY(-4px);
                opacity: 1;
            }
        }

        .typing-dot {
            animation: typingDot 1.4s ease-in-out infinite;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.15s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.3s;
        }

        @keyframes newMsgGlow {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 94, 108, 0.15);
            }

            100% {
                box-shadow: none;
            }
        }

        .new-msg-glow {
            animation: newMsgGlow 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .chat-scroll::-webkit-scrollbar {
            width: 2px;
        }

        .chat-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 2px;
        }

        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }

        .chat-input:focus {
            border-color: rgba(255, 94, 108, 0.4);
            box-shadow: 0 0 0 3px rgba(255, 94, 108, 0.08);
        }

        .img-preview {
            cursor: zoom-in;
            transition: transform 0.2s;
        }

        .img-preview:hover {
            transform: scale(1.03);
        }

        .safe-bottom {
            padding-bottom: max(env(safe-area-inset-bottom, 0px), 4px);
        }

        .menu-appear {
            animation: fadeIn 0.15s ease;
        }
    </style>
</head>

@php
$other = Auth::id() == $match->user1_id ? $match->user2 : $match->user1;
$otherProfile = $other->profile;
$otherPhoto = $otherProfile?->photo_url ?? 'https://ui-avatars.com/api/?background=1a1145&color=ff5e6c&bold=true&name=' . urlencode(substr($other->name, 0, 2));
@endphp

<body class="h-full text-white">
    <div class="h-full w-full flex flex-col max-w-md mx-auto">

        {{-- HEADER --}}
        <header class="flex items-center gap-3 px-4 pb-2.5 cc-surface border-b border-white/5 flex-shrink-0" style="padding-top: max(env(safe-area-inset-top, 12px), 12px);">
            <a href="{{ route('matches') }}" class="p-2 -ml-1 rounded-xl hover:bg-white/5 active:scale-95 transition">
                <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div class="w-10 h-10 rounded-full p-[2px] flex-shrink-0" style="background: linear-gradient(135deg, #ff5e6c, #ffc145);">
                <div class="w-full h-full rounded-full overflow-hidden">
                    <img src="{{ $otherPhoto }}" class="w-full h-full object-cover" alt="{{ e($other->name) }}">
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <h1 class="font-semibold text-sm truncate leading-tight cursor-pointer hover:text-[#ff5e6c] transition" onclick="document.getElementById('profile-modal').classList.remove('hidden')">{{ e($other->name) }}</h1>
                <p id="online-status" class="text-[11px] text-white/35 flex items-center gap-1.5 mt-0.5">
                    <span id="status-dot" class="w-1.5 h-1.5 rounded-full bg-white/15 flex-shrink-0"></span>
                    <span id="status-text">Hors ligne</span>
                </p>
            </div>

            <div class="relative">
                <button id="menu-toggle" class="p-2 rounded-xl hover:bg-white/5 active:scale-95 transition">
                    <svg class="w-5 h-5 text-white/35" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="5" r="1.5" />
                        <circle cx="12" cy="12" r="1.5" />
                        <circle cx="12" cy="19" r="1.5" />
                    </svg>
                </button>
                <div id="chat-menu" class="hidden menu-appear absolute right-0 top-full mt-1 w-48 rounded-2xl overflow-hidden z-50" style="background: rgba(20,14,50,0.97); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(40px); box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
                    <form method="POST" action="{{ route('messages.block', $match->id) }}">@csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-xs text-white/60 hover:bg-white/5 hover:text-white/80 transition flex items-center gap-2.5" onclick="return confirm('Bloquer cet utilisateur ?')">
                            <span class="text-base">🚫</span> Bloquer
                        </button>
                    </form>
                    <form method="POST" action="{{ route('messages.report', $match->id) }}">@csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-xs text-white/60 hover:bg-white/5 hover:text-white/80 transition flex items-center gap-2.5" onclick="return confirm('Signaler cet utilisateur ?')">
                            <span class="text-base">⚠️</span> Signaler
                        </button>
                    </form>
                    <div class="h-px bg-white/5 mx-3"></div>
                    <form method="POST" action="{{ route('messages.delete', $match->id) }}">@csrf @method('DELETE')
                        <button type="submit" class="w-full text-left px-4 py-3 text-xs text-red-400/70 hover:bg-red-500/5 hover:text-red-400 transition flex items-center gap-2.5" onclick="return confirm('Supprimer cette conversation ?')">
                            <span class="text-base">🗑️</span> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- MESSAGES --}}
        <main id="chat-area" class="flex-1 overflow-y-auto chat-scroll px-4 py-4 space-y-2.5">
            @if($messages->isEmpty())
            @php
            // Icebreakers contextuels selon les intérêts du profil
            $interests = $otherProfile?->interests ?? '';
            $interestList = array_map('trim', explode(',', $interests));
            $firstName = explode(' ', $other->name)[0];

            $allSuggestions = [
            // Universels
            "👋 Salut {$firstName} ! Comment tu trouves Campus Crush jusqu'ici ?",
            "🎓 T'es en quelle année à {$otherProfile?->university_name} ?",
            "☀️ Bonne journée {$firstName} ! T'as des cours aujourd'hui ?",
            "🍛 Tu connais un bon endroit pour manger près du campus ?",
            "🎵 T'écoutes quoi en ce moment comme musique ?",
            "📱 T'as découvert quelque chose de cool récemment ?",
            "🌊 Tu préfères la plage ou la ville pour sortir ?",
            "⚽ Tu suis le foot sénégalais ?",
            ];

            // Suggestions liées aux intérêts détectés
            $contextual = [];
            foreach ($interestList as $interest) {
            $i = mb_strtolower(trim($interest));
            if (str_contains($i, 'music') || str_contains($i, 'musique'))
            $contextual[] = "🎵 J'ai vu que t'aimes la musique — t'écoutes quoi en ce moment ?";
            if (str_contains($i, 'foot') || str_contains($i, 'sport'))
            $contextual[] = "⚽ T'as regardé le dernier match des Lions ?";
            if (str_contains($i, 'cin') || str_contains($i, 'film') || str_contains($i, 'série'))
            $contextual[] = "🎬 T'as regardé quelque chose de bien récemment ?";
            if (str_contains($i, 'voyage') || str_contains($i, 'travel'))
            $contextual[] = "✈️ Tu voyages souvent ? T'as une destination de rêve ?";
            if (str_contains($i, 'cuisine') || str_contains($i, 'food') || str_contains($i, 'manger'))
            $contextual[] = "🍽️ T'as un plat sénégalais préféré ?";
            if (str_contains($i, 'photo') || str_contains($i, 'art'))
            $contextual[] = "📸 Tu fais de la photo ? Tu as un compte Instagram ?";
            if (str_contains($i, 'jeu') || str_contains($i, 'game') || str_contains($i, 'gaming'))
            $contextual[] = "🎮 Tu joues à quoi en ce moment ?";
            if (str_contains($i, 'lecture') || str_contains($i, 'livre') || str_contains($i, 'book'))
            $contextual[] = "📚 T'es en train de lire quelque chose d'intéressant ?";
            }

            // Prioriser les contextuels, compléter avec les universels
            $suggestions = array_slice(
            array_unique(array_merge($contextual, $allSuggestions)),
            0, 4
            );
            @endphp

            <div class="flex flex-col items-center justify-center h-full text-center px-4 pb-4">
                {{-- Avatar avec aura --}}
                <div class="relative mb-5">
                    <div class="w-16 h-16 rounded-full overflow-hidden ring-2 ring-white/10"
                        style="box-shadow: 0 0 0 6px rgba(255,94,108,0.08);">
                        <img src="{{ $otherPhoto }}" class="w-full h-full object-cover" alt="{{ e($other->name) }}">
                    </div>
                    <span class="absolute -bottom-1 -right-1 text-xl">💬</span>
                </div>

                <p class="text-sm font-semibold text-white/70 mb-1">
                    Vous venez de matcher avec {{ e($firstName) }} !
                </p>
                <p class="text-[11px] text-white/25 mb-5">Brise la glace avec une question sympa 👇</p>

                {{-- Chips icebreakers --}}
                <div class="flex flex-col gap-2 w-full max-w-xs">
                    @foreach($suggestions as $suggestion)
                    <button type="button"
                        onclick="fillIcebreaker(this)"
                        data-msg="{{ e($suggestion) }}"
                        class="icebreaker-chip text-left px-4 py-2.5 rounded-2xl text-[12px] text-white/60 transition-all duration-200 active:scale-95"
                        style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                        {{ $suggestion }}
                    </button>
                    @endforeach
                </div>
            </div>

            <style>
                .icebreaker-chip:hover {
                    background: rgba(255, 94, 108, 0.08) !important;
                    border-color: rgba(255, 94, 108, 0.2) !important;
                    color: rgba(255, 255, 255, 0.85);
                }

                .icebreaker-chip:active {
                    background: rgba(255, 94, 108, 0.15) !important;
                }
            </style>

            <script>
                function fillIcebreaker(btn) {
                    const input = document.getElementById('msg-input');
                    if (!input) return;
                    input.value = btn.dataset.msg;
                    input.focus();
                    // Scroll l'input en vue sur mobile
                    input.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    // Petite animation de confirmation sur le chip
                    btn.style.transition = 'all 0.15s';
                    btn.style.background = 'rgba(255,94,108,0.18)';
                    btn.style.borderColor = 'rgba(255,94,108,0.35)';
                    btn.style.color = 'rgba(255,255,255,0.9)';
                }
            </script>
            @endif

            @php $myId = Auth::id(); $prevSender = null; @endphp
            @foreach($messages as $msg)
            @php
            $isMe = $msg->sender_id == $myId;
            $sameSender = $prevSender === $msg->sender_id;
            $prevSender = $msg->sender_id;
            @endphp
            <div class="flex items-end gap-2 max-w-[80%] msg-in {{ $isMe ? 'ml-auto flex-row-reverse' : '' }}" style="margin-top: {{ $sameSender ? '2px' : '10px' }};">
                @if(!$isMe && !$sameSender)
                <img src="{{ $msg->sender->profile?->photo_url ?? $otherPhoto }}" class="w-6 h-6 rounded-full object-cover flex-shrink-0" alt="">
                @elseif(!$isMe && $sameSender)
                <div class="w-6 flex-shrink-0"></div>
                @endif
                <div class="{{ $isMe ? 'items-end' : 'items-start' }} flex flex-col">
                    <div class="{{ $isMe ? 'bubble-sent' : 'bubble-received' }} px-3.5 py-2.5 max-w-full">
                        @if($msg->message)
                        <p class="text-[13px] leading-relaxed {{ $isMe ? 'text-white' : 'text-white/80' }} break-words">{!! nl2br(e($msg->message)) !!}</p>
                        @endif
                        @if($msg->attachments && $msg->attachments->count())
                        <div class="flex flex-wrap gap-1.5 {{ $msg->message ? 'mt-2' : '' }}">
                            @foreach($msg->attachments as $file)
                            <a href="{{ $file->url }}" target="_blank" class="block">
                                <img src="{{ $file->url }}" class="w-32 h-32 object-cover rounded-xl img-preview" alt="" loading="lazy">
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <span class="text-[9px] text-white/15 mt-1 px-1 {{ $isMe ? 'text-right' : '' }}" style="font-family:monospace;">
                        {{ $msg->created_at->format('H:i') }}
                        @if($isMe && $msg->read_at)<span class="text-blue-400/60 ml-0.5">✓✓</span>@endif
                    </span>
                </div>
            </div>
            @endforeach

            <div id="typing-indicator" class="hidden flex items-end gap-2 max-w-[80%] mt-2">
                <img src="{{ $otherPhoto }}" class="w-6 h-6 rounded-full object-cover flex-shrink-0" alt="">
                <div class="bubble-received px-4 py-3 flex items-center gap-1.5">
                    <span class="typing-dot w-1.5 h-1.5 bg-white/40 rounded-full"></span>
                    <span class="typing-dot w-1.5 h-1.5 bg-white/40 rounded-full"></span>
                    <span class="typing-dot w-1.5 h-1.5 bg-white/40 rounded-full"></span>
                </div>
            </div>
        </main>

        {{-- INPUT --}}
        <footer class="flex-shrink-0 cc-surface border-t border-white/5 safe-bottom">
            <form id="msg-form" method="POST" action="{{ route('messages.send', $match->id) }}" enctype="multipart/form-data">
                @csrf
                <div id="attach-preview" class="hidden flex gap-2 overflow-x-auto px-4 pt-3 pb-1"></div>
                <div class="flex items-end gap-2 px-3 py-2.5">
                    <button type="button" id="attach-btn" class="p-2 text-white/25 hover:text-[#ff5e6c] active:scale-90 transition rounded-xl hover:bg-white/5 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                    </button>
                    <input type="file" name="attachment[]" id="attach-input" class="hidden" accept="image/*" multiple>
                    <div class="flex-1 relative">
                        <input type="text" name="message" id="msg-input" placeholder="Message..." autocomplete="off"
                            class="chat-input w-full pl-4 pr-10 py-2.5 bg-white/[0.04] rounded-2xl text-[13px] text-white placeholder-white/20 outline-none border border-white/[0.06] transition">
                        <button type="button" id="emoji-toggle" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/20 hover:text-[#ffc145] active:scale-90 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div id="emoji-picker" class="absolute bottom-full mb-2 right-0 w-64 p-3 rounded-2xl hidden z-50" style="background: rgba(20,14,50,0.97); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(40px); box-shadow: 0 -10px 40px rgba(0,0,0,0.4);">
                            <div class="emoji-grid"></div>
                        </div>
                    </div>
                    <button type="submit" id="send-btn" class="p-2.5 rounded-2xl text-white flex-shrink-0 active:scale-90 transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 4px 16px rgba(255,94,108,0.25);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
            </form>
        </footer>
    </div>

    {{-- PROFILE CARD MODAL --}}
    <div id="profile-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6" style="background:rgba(12,10,26,0.92); backdrop-filter:blur(20px);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="w-full max-w-[300px] rounded-[24px] overflow-hidden relative" style="aspect-ratio: 3/4.2; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
            <img src="{{ $otherPhoto }}" class="absolute inset-0 w-full h-full object-cover" alt="">
            <div class="absolute inset-0" style="background: linear-gradient(0deg, rgba(12,10,26,0.9) 0%, rgba(12,10,26,0.3) 35%, transparent 70%);"></div>
            <div class="absolute bottom-0 left-0 right-0 p-5">
                <div class="flex items-baseline gap-2 mb-1.5">
                    <h2 class="text-2xl font-bold text-white">{{ e($other->name) }}</h2>
                    <span class="text-lg text-white/50">{{ $otherProfile?->age }}</span>
                </div>
                <div class="flex items-center gap-1.5 mb-2">
                    <svg class="w-3.5 h-3.5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-xs text-white/50">{{ $otherProfile?->ufr }} · {{ $otherProfile?->level }}</span>
                </div>
                @if($otherProfile?->university_name ?? $otherProfile?->university)
                <p class="text-[10px] text-white/30 mb-2">🎓 {{ $otherProfile->university_name ?? $otherProfile->university }}</p>
                @endif
                @if($otherProfile?->bio)
                <p class="text-xs text-white/50 leading-relaxed line-clamp-3 mb-3">{{ e($otherProfile->bio) }}</p>
                @endif
                @if($otherProfile?->interests)
                <div class="flex flex-wrap gap-1.5">
                    @foreach(explode(',', $otherProfile->interests) as $tag)
                    <span class="px-2 py-0.5 bg-white/10 rounded-full text-[10px] text-white/60">{{ trim($tag) }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            <button onclick="document.getElementById('profile-modal').classList.add('hidden')" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/40 flex items-center justify-center text-white/60 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- PUSHER + ECHO (only if pusher is configured) --}}
    <?php if (config('broadcasting.default') === 'pusher'): ?>
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.0/echo.iife.js"></script>
    <?php endif; ?>

    <script>
        const matchId = <?php echo json_encode($match->id); ?>;
        const myId = <?php echo json_encode(Auth::id()); ?>;
        const chatArea = document.getElementById('chat-area');
        const typingEl = document.getElementById('typing-indicator');
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const otherPhoto = <?php echo json_encode($otherPhoto); ?>;

        // ═══════════════════════════════════════
        // PUSHER REAL-TIME
        // ═══════════════════════════════════════
        <?php if (config('broadcasting.default') === 'pusher'): ?>
            <?php
            $pKey = config('broadcasting.connections.pusher.key');
            $pCluster = config('broadcasting.connections.pusher.options.cluster', 'eu');
            ?>
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '<?php echo $pKey; ?>',
                cluster: '<?php echo $pCluster; ?>',
                forceTLS: true,
                disableStats: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                }
            });

            const channel = Echo.join('chat.' + matchId);

            channel.here(users => {
                setOnlineStatus(users.some(u => u.id !== myId));
            });
            channel.joining(user => {
                if (user.id !== myId) setOnlineStatus(true);
            });
            channel.leaving(user => {
                if (user.id !== myId) setOnlineStatus(false);
            });

            channel.listen('.MessageSent', (data) => {
                if (data.senderId === myId) return;
                typingEl.classList.add('hidden');
                const div = document.createElement('div');
                div.className = 'flex items-end gap-2 max-w-[80%] msg-in new-msg-glow';
                let content = '';
                if (data.message) content += '<p class="text-[13px] leading-relaxed text-white/80 break-words">' + esc(data.message) + '</p>';
                if (data.attachments && data.attachments.length) {
                    content += '<div class="flex flex-wrap gap-1.5' + (data.message ? ' mt-2' : '') + '">';
                    data.attachments.forEach(a => {
                        content += '<img src="' + a.url + '" class="w-32 h-32 object-cover rounded-xl" loading="lazy">';
                    });
                    content += '</div>';
                }
                div.innerHTML = '<img src="' + (data.senderPhoto || otherPhoto) + '" class="w-6 h-6 rounded-full object-cover flex-shrink-0" alt="">' +
                    '<div class="flex flex-col items-start"><div class="bubble-received px-3.5 py-2.5">' + content + '</div>' +
                    '<span class="text-[9px] text-white/15 mt-1 px-1" style="font-family:monospace">' + data.time + '</span></div>';
                chatArea.insertBefore(div, typingEl);
                chatArea.scrollTop = chatArea.scrollHeight;
                fetch('/messages/' + matchId, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                }).catch(() => {});
            });

            let typingTimeout;
            channel.listen('.UserTyping', (data) => {
                if (data.userId === myId) return;
                typingEl.classList.remove('hidden');
                chatArea.scrollTop = chatArea.scrollHeight;
                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => typingEl.classList.add('hidden'), 2500);
            });

            let lastTypingSent = 0;
            document.getElementById('msg-input').addEventListener('input', () => {
                const now = Date.now();
                if (now - lastTypingSent > 1500) {
                    lastTypingSent = now;
                    fetch('/typing/' + matchId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    }).catch(() => {});
                }
            });
        <?php endif; ?>

        // ═══════════════════════════════════════
        // HELPERS
        // ═══════════════════════════════════════
        function setOnlineStatus(online) {
            statusDot.className = 'w-1.5 h-1.5 rounded-full flex-shrink-0 ' + (online ? 'bg-green-400' : 'bg-white/15');
            statusText.textContent = online ? 'En ligne' : 'Hors ligne';
            if (online) statusDot.style.boxShadow = '0 0 6px rgba(74,222,128,0.5)';
            else statusDot.style.boxShadow = '';
        }

        function esc(text) {
            if (!text) return '';
            return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        // ═══════════════════════════════════════
        // FORM SUBMIT
        // ═══════════════════════════════════════
        document.getElementById('msg-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('msg-input');
            const text = input.value.trim();
            const attachInput = document.getElementById('attach-input');
            if (!text && attachInput.files.length === 0) return;
            const now = new Date();
            const time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            let contentHtml = '';
            if (text) contentHtml += '<p class="text-[13px] leading-relaxed text-white break-words">' + esc(text) + '</p>';
            const files = Array.from(attachInput.files);
            if (files.length > 0) {
                let imgHtml = '<div class="flex flex-wrap gap-1.5' + (text ? ' mt-2' : '') + '">';
                files.forEach(f => {
                    imgHtml += '<img src="' + URL.createObjectURL(f) + '" class="w-32 h-32 object-cover rounded-xl" alt="">';
                });
                imgHtml += '</div>';
                contentHtml += imgHtml;
            }
            if (contentHtml) {
                const div = document.createElement('div');
                div.className = 'flex items-end gap-2 max-w-[80%] ml-auto flex-row-reverse msg-in';
                div.innerHTML = '<div class="flex flex-col items-end"><div class="bubble-sent px-3.5 py-2.5">' + contentHtml + '</div><span class="text-[9px] text-white/15 mt-1 px-1" style="font-family:monospace">' + time + '</span></div>';
                chatArea.insertBefore(div, typingEl);
                chatArea.scrollTop = chatArea.scrollHeight;
            }
            const fd = new FormData(this);
            input.value = '';
            document.getElementById('attach-preview').classList.add('hidden');
            document.getElementById('attach-preview').innerHTML = '';
            fetch(this.action, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }).catch(err => console.error('Send error:', err));
            attachInput.value = '';
        });

        // ═══════════════════════════════════════
        // SCROLL + MENU + ATTACHMENTS + EMOJIS
        // ═══════════════════════════════════════
        chatArea.scrollTop = chatArea.scrollHeight;

        document.getElementById('menu-toggle').addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('chat-menu').classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            const m = document.getElementById('chat-menu');
            if (!m.contains(e.target)) m.classList.add('hidden');
        });

        const aBtn = document.getElementById('attach-btn'),
            aInput = document.getElementById('attach-input'),
            aPrev = document.getElementById('attach-preview');
        aBtn.onclick = () => aInput.click();
        aInput.onchange = () => {
            aPrev.innerHTML = '';
            const files = Array.from(aInput.files);
            if (!files.length) {
                aPrev.classList.add('hidden');
                return;
            }
            aPrev.classList.remove('hidden');
            files.forEach((f) => {
                const wrap = document.createElement('div');
                wrap.className = 'relative flex-shrink-0';
                const r = new FileReader();
                r.onload = e => {
                    wrap.innerHTML = '<img src="' + e.target.result + '" class="w-16 h-16 object-cover rounded-xl"><button type="button" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] leading-none shadow-lg" onclick="this.parentElement.remove(); checkPreview();">×</button>';
                    aPrev.appendChild(wrap);
                };
                r.readAsDataURL(f);
            });
        };
        window.checkPreview = () => {
            if (!aPrev.children.length) {
                aPrev.classList.add('hidden');
                aInput.value = '';
            }
        };

        const emojis = ['😊', '😂', '😍', '😎', '👍', '🎉', '❤️', '😢', '🤔', '😜', '🥳', '🤩', '💯', '🔥', '🙌', '😘', '🥰', '💕', '👋', '✨', '💪', '🎶', '😭', '🥺', '😏', '🤗', '😤', '🤣', '🫶'];
        const picker = document.getElementById('emoji-picker').querySelector('.emoji-grid');
        const msgInput = document.getElementById('msg-input');
        emojis.forEach(em => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = em;
            b.className = 'text-lg p-1 rounded-lg hover:bg-white/10 active:scale-110 transition';
            b.onclick = () => {
                msgInput.value += em;
                msgInput.focus();
            };
            picker.appendChild(b);
        });
        document.getElementById('emoji-toggle').onclick = (e) => {
            e.stopPropagation();
            document.getElementById('emoji-picker').classList.toggle('hidden');
        };
        document.addEventListener('click', (e) => {
            const ep = document.getElementById('emoji-picker');
            if (!ep.contains(e.target)) ep.classList.add('hidden');
        });

        // ═══════════════════════════════════════
        // POLL ONLINE STATUS
        // ═══════════════════════════════════════
        const otherUserId = <?php echo json_encode($other->id); ?>;

        async function pollOnlineStatus() {
            try {
                const res = await fetch('/user/' + otherUserId + '/status');
                const data = await res.json();
                setOnlineStatus(data.online);
                if (!data.online && data.last_seen) {
                    statusText.textContent = data.last_seen;
                }
            } catch (e) {}
        }

        pollOnlineStatus();
        setInterval(pollOnlineStatus, 10000);

        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', () => {
                setTimeout(() => chatArea.scrollTop = chatArea.scrollHeight, 100);
            });
        }
    </script>
</body>

</html>