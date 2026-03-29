<!doctype html>
<html lang="fr" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>Campus Crush - Chat</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Sora', sans-serif;
            box-sizing: border-box;
        }

        body {
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
            box-shadow: 0 4px 20px rgba(255, 94, 108, 0.15);
        }

        .bubble-received {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px 20px 20px 6px;
        }

        @keyframes msgIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .msg-in {
            animation: msgIn 0.3s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .chat-scroll::-webkit-scrollbar {
            width: 3px;
        }

        .chat-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.06);
            border-radius: 3px;
        }

        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 2px;
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

        @keyframes newMsgFlash {
            0% {
                background: rgba(255, 94, 108, 0.1);
            }

            100% {
                background: transparent;
            }
        }

        .new-msg-flash {
            animation: newMsgFlash 1s ease-out;
        }
    </style>
</head>

@php
$other = Auth::id() == $match->user1_id ? $match->user2 : $match->user1;
$otherProfile = $other->profile;
@endphp

<body class="h-full text-white">
    <div class="h-full w-full flex flex-col max-w-md mx-auto overflow-hidden">

        {{-- Header --}}
        <header class="flex items-center gap-3 px-4 py-3 cc-surface border-b border-white/5 flex-shrink-0">
            <a href="{{ route('matches') }}" class="p-1.5 rounded-xl hover:bg-white/5 transition">
                <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div class="w-10 h-10 rounded-full p-[2px] flex-shrink-0" style="background: linear-gradient(135deg, #ff5e6c, #ffc145);">
                <div class="w-full h-full rounded-full overflow-hidden">
                    <img src="{{ $otherProfile?->photo_url ?? asset('storage/profiles/default-avatar.png') }}" class="w-full h-full object-cover" alt="">
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <h1 class="font-semibold text-sm truncate">{{ e($other->name) }}</h1>
                <p id="online-status" class="text-[11px] text-white/40 flex items-center gap-1.5">
                    <span id="status-dot" class="w-1.5 h-1.5 rounded-full bg-white/20"></span>
                    <span id="status-text">Hors ligne</span>
                </p>
            </div>

            {{-- Menu --}}
            <div class="relative">
                <button onclick="document.getElementById('chat-menu').classList.toggle('hidden')" class="p-2 rounded-xl hover:bg-white/5 transition">
                    <svg class="w-5 h-5 text-white/40" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="5" r="1.5" />
                        <circle cx="12" cy="12" r="1.5" />
                        <circle cx="12" cy="19" r="1.5" />
                    </svg>
                </button>
                <div id="chat-menu" class="hidden absolute right-0 mt-2 w-44 rounded-2xl overflow-hidden z-50" style="background: rgba(26,17,69,0.95); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(40px);">
                    <form method="POST" action="{{ route('messages.block', $match->id) }}">@csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-xs hover:bg-white/5 transition" onclick="return confirm('Bloquer ?')">🚫 Bloquer</button>
                    </form>
                    <form method="POST" action="{{ route('messages.report', $match->id) }}">@csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-xs hover:bg-white/5 transition" onclick="return confirm('Signaler ?')">⚠️ Signaler</button>
                    </form>
                    <form method="POST" action="{{ route('messages.delete', $match->id) }}">@csrf @method('DELETE')
                        <button type="submit" class="w-full text-left px-4 py-3 text-xs text-red-400 hover:bg-white/5 transition" onclick="return confirm('Supprimer ?')">🗑️ Supprimer</button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Messages --}}
        <main id="chat-area" class="flex-1 overflow-y-auto chat-scroll px-4 py-4 space-y-3">
            @if($messages->isEmpty())
            <div class="flex flex-col items-center justify-center h-full text-center">
                <div class="text-4xl mb-3">👋</div>
                <p class="text-sm text-white/30">Envoie le premier message !</p>
            </div>
            @endif

            @php $myId = Auth::id(); $msgIndex = 0; @endphp

            @foreach($messages as $msg)
            @php $msgIndex++; @endphp
            <div class="flex items-end gap-2 max-w-[82%] msg-in @if($msg->sender_id == $myId) ml-auto flex-row-reverse @endif"
                style="animation-delay: 0s">

                @if($msg->sender_id != $myId)
                <img src="{{ $msg->sender->profile?->photo_url ?? asset('storage/profiles/default-avatar.png') }}"
                    class="w-6 h-6 rounded-full object-cover flex-shrink-0" alt="">
                @endif

                <div>
                    <div class="{{ $msg->sender_id == $myId ? 'bubble-sent' : 'bubble-received' }} px-4 py-2.5">
                        @if($msg->message)
                        <p class="text-[13px] leading-relaxed {{ $msg->sender_id == $myId ? 'text-white' : 'text-white/80' }}">{{ e($msg->message) }}</p>
                        @endif
                        @if($msg->attachments && $msg->attachments->count())
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach($msg->attachments as $file)
                            <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank">
                                <img src="{{ asset('storage/'.$file->file_path) }}" class="w-20 h-20 object-cover rounded-xl" alt="">
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <span class="text-[10px] text-white/20 mt-1 block @if($msg->sender_id == $myId) text-right @endif" style="font-family:monospace">
                        {{ $msg->created_at->format('H:i') }}
                        @if($msg->sender_id == $myId && $msg->read_at)<span class="text-blue-400 ml-1">✓✓</span>@endif
                    </span>
                </div>
            </div>
            @endforeach

            {{-- Typing indicator --}}
            <div id="typing-indicator" class="hidden flex items-end gap-2 max-w-[82%]">
                <img src="{{ $otherProfile?->photo_url ?? asset('storage/profiles/default-avatar.png') }}" class="w-6 h-6 rounded-full object-cover" alt="">
                <div class="bubble-received px-4 py-3 flex items-center gap-1">
                    <span class="typing-dot w-1.5 h-1.5 bg-white/50 rounded-full"></span>
                    <span class="typing-dot w-1.5 h-1.5 bg-white/50 rounded-full"></span>
                    <span class="typing-dot w-1.5 h-1.5 bg-white/50 rounded-full"></span>
                </div>
            </div>
        </main>

        {{-- Input --}}
        <footer class="px-3 py-3 cc-surface border-t border-white/5 flex-shrink-0">
            <form id="msg-form" method="POST" action="{{ route('messages.send', $match->id) }}" enctype="multipart/form-data">
                @csrf
                <div id="attach-preview" class="flex gap-2 overflow-x-auto hidden mb-2"></div>
                <div class="flex items-end gap-2">
                    <button type="button" id="attach-btn" class="p-2.5 text-white/30 hover:text-[#ff5e6c] transition rounded-xl hover:bg-white/5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                    </button>
                    <input type="file" name="attachment[]" id="attach-input" class="hidden" accept="image/*" multiple>

                    <div class="flex-1 relative">
                        <input type="text" name="message" id="msg-input" placeholder="Message..." autocomplete="off"
                            class="w-full pl-4 pr-10 py-3 bg-white/5 rounded-2xl text-sm text-white placeholder-white/30 outline-none border border-white/5 focus:border-[#ff5e6c]/50 transition">
                        <button type="button" id="emoji-toggle" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/25 hover:text-[#ffc145] transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div id="emoji-picker" class="absolute bottom-full mb-2 right-0 w-60 p-3 rounded-2xl hidden z-50" style="background: rgba(26,17,69,0.95); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(40px);">
                            <div class="emoji-grid"></div>
                        </div>
                    </div>

                    <button type="submit" id="send-btn" class="p-3 rounded-2xl text-white shadow-lg transition hover:scale-105 active:scale-95" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 4px 20px rgba(255,94,108,0.3);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
            </form>
        </footer>
    </div>

    {{-- Pusher + Echo CDN --}}
    @if(config('broadcasting.default') !== 'log')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.0/echo.iife.js"></script>
@endif

    <script>
        // ═══════════════════════════════════════════
        // SETUP ECHO / REVERB
        // ═══════════════════════════════════════════
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
        const matchId = @json($match -> id);
        const myId = @json(Auth::id());
        const chatArea = document.getElementById('chat-area');
        const typingEl = document.getElementById('typing-indicator');
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // ═══════════════════════════════════════════
        // PRESENCE CHANNEL - Online status + Typing
        // ═══════════════════════════════════════════
        const channel = Echo.join(`chat.${matchId}`);

        channel.here(users => {
            const otherOnline = users.some(u => u.id !== myId);
            setOnlineStatus(otherOnline);
        });

        channel.joining(user => {
            if (user.id !== myId) setOnlineStatus(true);
        });

        channel.leaving(user => {
            if (user.id !== myId) setOnlineStatus(false);
        });

        // ═══════════════════════════════════════════
        // REAL-TIME MESSAGES
        // ═══════════════════════════════════════════
        channel.listen('.MessageSent', (data) => {
            if (data.senderId === myId) return; // ignore own messages

            // Hide typing
            typingEl.classList.add('hidden');

            // Create message bubble
            const div = document.createElement('div');
            div.className = 'flex items-end gap-2 max-w-[82%] msg-in new-msg-flash';
            div.innerHTML = `
        <img src="${data.senderPhoto}" class="w-6 h-6 rounded-full object-cover flex-shrink-0" alt="">
        <div>
            <div class="bubble-received px-4 py-2.5">
                ${data.message ? `<p class="text-[13px] leading-relaxed text-white/80">${escapeHtml(data.message)}</p>` : ''}
                ${data.attachments && data.attachments.length ? `<div class="flex flex-wrap gap-1.5 mt-2">${data.attachments.map(a => `<a href="${a.url}" target="_blank"><img src="${a.url}" class="w-20 h-20 object-cover rounded-xl" alt=""></a>`).join('')}</div>` : ''}
            </div>
            <span class="text-[10px] text-white/20 mt-1 block" style="font-family:monospace">${data.time}</span>
        </div>
    `;

            // Insert before typing indicator
            chatArea.insertBefore(div, typingEl);
            chatArea.scrollTop = chatArea.scrollHeight;

            // Mark as read via fetch
            fetch(`/messages/${matchId}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        });

        // ═══════════════════════════════════════════
        // TYPING INDICATOR
        // ═══════════════════════════════════════════
        let typingTimeout;
        channel.listen('.UserTyping', (data) => {
            if (data.userId === myId) return;
            typingEl.classList.remove('hidden');
            chatArea.scrollTop = chatArea.scrollHeight;
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => typingEl.classList.add('hidden'), 2500);
        });

        // Send typing event on input
        let lastTypingSent = 0;
        document.getElementById('msg-input').addEventListener('input', () => {
            const now = Date.now();
            if (now - lastTypingSent > 1500) {
                lastTypingSent = now;
                fetch(`/typing/${matchId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                }).catch(() => {});
            }
        });

        // ═══════════════════════════════════════════
        // HELPERS
        // ═══════════════════════════════════════════
        function setOnlineStatus(online) {
            statusDot.className = `w-1.5 h-1.5 rounded-full ${online ? 'bg-green-400 animate-pulse' : 'bg-white/20'}`;
            statusText.textContent = online ? 'En ligne' : 'Hors ligne';
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }

        // ═══════════════════════════════════════════
        // FORM SUBMIT (AJAX for smooth UX)
        // ═══════════════════════════════════════════
        document.getElementById('msg-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('msg-input');
            const text = input.value.trim();
            const attachInput = document.getElementById('attach-input');

            if (!text && attachInput.files.length === 0) return;

            // Optimistic UI: add message immediately
            if (text) {
                const div = document.createElement('div');
                div.className = 'flex items-end gap-2 max-w-[82%] ml-auto flex-row-reverse msg-in';
                const now = new Date();
                const time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
                div.innerHTML = `
            <div>
                <div class="bubble-sent px-4 py-2.5">
                    <p class="text-[13px] leading-relaxed text-white">${escapeHtml(text)}</p>
                </div>
                <span class="text-[10px] text-white/20 mt-1 block text-right" style="font-family:monospace">${time}</span>
            </div>
        `;
                chatArea.insertBefore(div, typingEl);
                chatArea.scrollTop = chatArea.scrollHeight;
            }

            // Send to server
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

            // Reset file input
            attachInput.value = '';
        });

        // ═══════════════════════════════════════════
        // SCROLL, MENU, ATTACHMENTS, EMOJIS
        // ═══════════════════════════════════════════
        chatArea.scrollTop = chatArea.scrollHeight;

        window.addEventListener('click', e => {
            const m = document.getElementById('chat-menu');
            if (!m.contains(e.target) && !e.target.closest('[onclick]')) m.classList.add('hidden');
        });

        const aBtn = document.getElementById('attach-btn'),
            aInput = document.getElementById('attach-input'),
            aPrev = document.getElementById('attach-preview');
        aBtn.onclick = () => aInput.click();
        aInput.onchange = () => {
            aPrev.innerHTML = '';
            const files = Array.from(aInput.files);
            if (files.length) aPrev.classList.remove('hidden');
            else {
                aPrev.classList.add('hidden');
                return;
            }
            files.forEach(f => {
                const r = new FileReader();
                r.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-14 h-14 object-cover rounded-xl flex-shrink-0';
                    aPrev.appendChild(img);
                };
                r.readAsDataURL(f);
            });
        };

        const emojis = ['😊', '😂', '😍', '😎', '👍', '🎉', '❤️', '😢', '🤔', '😜', '🥳', '🤩', '💯', '🔥', '🙌', '😘', '🥰', '💕', '👋', '✨', '💪', '🎶'];
        const picker = document.getElementById('emoji-picker').querySelector('.emoji-grid');
        const msgInput = document.getElementById('msg-input');
        emojis.forEach(em => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = em;
            b.className = 'text-xl p-1.5 rounded-lg hover:bg-white/10 transition';
            b.onclick = () => {
                msgInput.value += em;
                msgInput.focus();
            };
            picker.appendChild(b);
        });
        document.getElementById('emoji-toggle').onclick = e => {
            e.stopPropagation();
            document.getElementById('emoji-picker').classList.toggle('hidden');
        };
        document.addEventListener('click', e => {
            if (!document.getElementById('emoji-picker').contains(e.target)) document.getElementById('emoji-picker').classList.add('hidden');
        });
    </script>
    @include('components.pwa-install-banner')
</body>

</html>