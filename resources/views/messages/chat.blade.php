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

        /* ═══ SURFACES ═══ */
        .cc-surface {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(24px);
        }

        /* ═══ BUBBLES ═══ */
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

        /* ═══ ANIMATIONS ═══ */
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

        /* ═══ SCROLLBAR ═══ */
        .chat-scroll::-webkit-scrollbar {
            width: 2px;
        }

        .chat-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 2px;
        }

        /* ═══ EMOJI GRID ═══ */
        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }

        /* ═══ INPUT FOCUS ═══ */
        .chat-input:focus {
            border-color: rgba(255, 94, 108, 0.4);
            box-shadow: 0 0 0 3px rgba(255, 94, 108, 0.08);
        }

        /* ═══ IMAGE LIGHTBOX ═══ */
        .img-preview {
            cursor: zoom-in;
            transition: transform 0.2s;
        }

        .img-preview:hover {
            transform: scale(1.03);
        }

        /* ═══ SAFE AREAS ═══ */
        .safe-top { padding-top: max(env(safe-area-inset-top, 12px), 12px); }

        .safe-bottom {
            padding-bottom: max(env(safe-area-inset-bottom, 0px), 4px);
        }

        /* ═══ MENU ═══ */
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

        {{-- ═══════════════════════════════ --}}
        {{-- HEADER --}}
        {{-- ═══════════════════════════════ --}}
        <header class="flex items-center gap-3 px-4 pb-2.5 cc-surface border-b border-white/5 flex-shrink-0" style="padding-top: max(env(safe-area-inset-top, 12px), 12px);">
            {{-- Back --}}
            <a href="{{ route('matches') }}" class="p-2 -ml-1 rounded-xl hover:bg-white/5 active:scale-95 transition">
                <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            {{-- Avatar --}}
            <div class="w-10 h-10 rounded-full p-[2px] flex-shrink-0" style="background: linear-gradient(135deg, #ff5e6c, #ffc145);">
                <div class="w-full h-full rounded-full overflow-hidden">
                    <img src="{{ $otherPhoto }}" class="w-full h-full object-cover" alt="{{ e($other->name) }}">
                </div>
            </div>

            {{-- Name + Status --}}
            <div class="flex-1 min-w-0">
                <h1 class="font-semibold text-sm truncate leading-tight">{{ e($other->name) }}</h1>
                <p id="online-status" class="text-[11px] text-white/35 flex items-center gap-1.5 mt-0.5">
                    <span id="status-dot" class="w-1.5 h-1.5 rounded-full bg-white/15 flex-shrink-0"></span>
                    <span id="status-text">Hors ligne</span>
                </p>
            </div>

            {{-- Menu --}}
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

        {{-- ═══════════════════════════════ --}}
        {{-- MESSAGES --}}
        {{-- ═══════════════════════════════ --}}
        <main id="chat-area" class="flex-1 overflow-y-auto chat-scroll px-4 py-4 space-y-2.5">

            @if($messages->isEmpty())
            <div class="flex flex-col items-center justify-center h-full text-center px-6">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background: rgba(255,94,108,0.08); border: 1px solid rgba(255,94,108,0.1);">
                    <span class="text-3xl">👋</span>
                </div>
                <p class="text-sm font-medium text-white/50 mb-1">Commence la conversation !</p>
                <p class="text-[11px] text-white/25">Envoie un message à {{ e($other->name) }}</p>
            </div>
            @endif

            @php $myId = Auth::id(); $prevSender = null; @endphp

            @foreach($messages as $msg)
            @php
            $isMe = $msg->sender_id == $myId;
            $sameSender = $prevSender === $msg->sender_id;
            $prevSender = $msg->sender_id;
            @endphp

            <div class="flex items-end gap-2 max-w-[80%] msg-in {{ $isMe ? 'ml-auto flex-row-reverse' : '' }}" style="margin-top: {{ $sameSender ? '2px' : '10px' }};">
                {{-- Avatar (only for received, only if different sender) --}}
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

            {{-- Typing indicator --}}
            <div id="typing-indicator" class="hidden flex items-end gap-2 max-w-[80%] mt-2">
                <img src="{{ $otherPhoto }}" class="w-6 h-6 rounded-full object-cover flex-shrink-0" alt="">
                <div class="bubble-received px-4 py-3 flex items-center gap-1.5">
                    <span class="typing-dot w-1.5 h-1.5 bg-white/40 rounded-full"></span>
                    <span class="typing-dot w-1.5 h-1.5 bg-white/40 rounded-full"></span>
                    <span class="typing-dot w-1.5 h-1.5 bg-white/40 rounded-full"></span>
                </div>
            </div>
        </main>

        {{-- ═══════════════════════════════ --}}
        {{-- INPUT --}}
        {{-- ═══════════════════════════════ --}}
        <footer class="flex-shrink-0 cc-surface border-t border-white/5 safe-bottom">
            <form id="msg-form" method="POST" action="{{ route('messages.send', $match->id) }}" enctype="multipart/form-data">
                @csrf

                {{-- Attachment preview --}}
                <div id="attach-preview" class="hidden flex gap-2 overflow-x-auto px-4 pt-3 pb-1">
                </div>

                <div class="flex items-end gap-2 px-3 py-2.5">
                    {{-- Attach --}}
                    <button type="button" id="attach-btn" class="p-2 text-white/25 hover:text-[#ff5e6c] active:scale-90 transition rounded-xl hover:bg-white/5 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                    </button>
                    <input type="file" name="attachment[]" id="attach-input" class="hidden" accept="image/*" multiple>

                    {{-- Text input --}}
                    <div class="flex-1 relative">
                        <input type="text" name="message" id="msg-input" placeholder="Message..." autocomplete="off"
                            class="chat-input w-full pl-4 pr-10 py-2.5 bg-white/[0.04] rounded-2xl text-[13px] text-white placeholder-white/20 outline-none border border-white/[0.06] transition">
                        {{-- Emoji toggle --}}
                        <button type="button" id="emoji-toggle" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/20 hover:text-[#ffc145] active:scale-90 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        {{-- Emoji picker --}}
                        <div id="emoji-picker" class="absolute bottom-full mb-2 right-0 w-64 p-3 rounded-2xl hidden z-50" style="background: rgba(20,14,50,0.97); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(40px); box-shadow: 0 -10px 40px rgba(0,0,0,0.4);">
                            <div class="emoji-grid"></div>
                        </div>
                    </div>

                    {{-- Send --}}
                    <button type="submit" id="send-btn" class="p-2.5 rounded-2xl text-white flex-shrink-0 active:scale-90 transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 4px 16px rgba(255,94,108,0.25);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
            </form>
        </footer>
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- ECHO (conditionnel) --}}
    {{-- ═══════════════════════════════ --}}
    <?php if (config('broadcasting.default') !== 'log'): ?>
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.0/echo.iife.js"></script>
    <?php endif; ?>

    <script>
        // ═══════════════════════════════════════
        // VARS
        // ═══════════════════════════════════════
        const matchId = <?php echo json_encode($match->id); ?>;
        const myId = <?php echo json_encode(Auth::id()); ?>;
        const chatArea = document.getElementById('chat-area');
        const typingEl = document.getElementById('typing-indicator');
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const otherPhoto = <?php echo json_encode($otherPhoto); ?>;

        // ═══════════════════════════════════════
        // ECHO / REAL-TIME
        // ═══════════════════════════════════════
        <?php if (config('broadcasting.default') !== 'log'): ?>
            <?php
            $rKey = config('broadcasting.connections.reverb.key', 'campuscrush-key');
            $rHost = config('reverb.servers.reverb.hostname', 'localhost');
            $rPort = config('reverb.servers.reverb.port', 8080);
            ?>
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '<?php echo $rKey; ?>',
                cluster: 'mt1',
                wsHost: '<?php echo $rHost; ?>',
                wsPort: <?php echo $rPort; ?>,
                wssPort: <?php echo $rPort; ?>,
                forceTLS: false,
                enabledTransports: ['ws'],
                disableStats: true,
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
                });
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
    return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

        // ═══════════════════════════════════════
        // FORM SUBMIT (instant preview)
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
        // SCROLL TO BOTTOM
        // ═══════════════════════════════════════
        chatArea.scrollTop = chatArea.scrollHeight;

        // ═══════════════════════════════════════
        // MENU
        // ═══════════════════════════════════════
        document.getElementById('menu-toggle').addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('chat-menu').classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('chat-menu');
            if (!menu.contains(e.target)) menu.classList.add('hidden');
        });

        // ═══════════════════════════════════════
        // ATTACHMENTS
        // ═══════════════════════════════════════
        const aBtn = document.getElementById('attach-btn');
        const aInput = document.getElementById('attach-input');
        const aPrev = document.getElementById('attach-preview');

        aBtn.onclick = () => aInput.click();
        aInput.onchange = () => {
            aPrev.innerHTML = '';
            const files = Array.from(aInput.files);
            if (!files.length) {
                aPrev.classList.add('hidden');
                return;
            }
            aPrev.classList.remove('hidden');
            files.forEach((f, i) => {
                const wrap = document.createElement('div');
                wrap.className = 'relative flex-shrink-0';
                const r = new FileReader();
                r.onload = e => {
                    wrap.innerHTML = '<img src="' + e.target.result + '" class="w-16 h-16 object-cover rounded-xl">' +
                        '<button type="button" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] leading-none shadow-lg" onclick="this.parentElement.remove(); checkPreview();">×</button>';
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

        // ═══════════════════════════════════════
        // EMOJI PICKER
        // ═══════════════════════════════════════
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
        // KEYBOARD RESIZE (mobile fix)
        // ═══════════════════════════════════════
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', () => {
                setTimeout(() => chatArea.scrollTop = chatArea.scrollHeight, 100);
            });
        }
    </script>
</body>

</html>