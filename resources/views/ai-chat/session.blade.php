<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.pwa-meta')
    <title>{{ $bot['name'] }} — Campus Crush IA</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        html { height: 100%; }
        body { height: 100%; overflow: hidden; background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); }
        .cc-surface { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(24px); }
        .bubble-sent { background: linear-gradient(135deg, #ff5e6c 0%, #ff8a5c 100%); border-radius: 20px 20px 6px 20px; }
        .bubble-received { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px 20px 20px 6px; }
        @keyframes msgIn { from { opacity: 0; transform: translateY(10px) scale(0.97); } to { opacity: 1; transform: none; } }
        .msg-in { animation: msgIn 0.25s cubic-bezier(0.22,1,0.36,1) both; }
        @keyframes typingDot { 0%,60%,100% { transform: translateY(0); opacity: 0.4; } 30% { transform: translateY(-4px); opacity: 1; } }
        .typing-dot { animation: typingDot 1.4s ease-in-out infinite; }
        .typing-dot:nth-child(2) { animation-delay: 0.15s; }
        .typing-dot:nth-child(3) { animation-delay: 0.3s; }
        .chat-scroll::-webkit-scrollbar { width: 2px; }
        .chat-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); border-radius: 2px; }
        .chat-input:focus { border-color: rgba(255,94,108,0.4); box-shadow: 0 0 0 3px rgba(255,94,108,0.08); }
        .safe-bottom { padding-bottom: max(env(safe-area-inset-bottom, 0px), 4px); }
    </style>
</head>
<body class="h-full text-white">
<div class="h-full w-full flex flex-col max-w-md mx-auto">

    {{-- Header --}}
    <header class="flex items-center gap-3 px-4 pb-2.5 cc-surface border-b border-white/5 flex-shrink-0" style="padding-top: max(env(safe-area-inset-top, 12px), 12px);">
        <a href="{{ route('ai.index') }}" class="p-2 -ml-1 rounded-xl hover:bg-white/5 active:scale-95 transition">
            <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>

        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-xl" style="background: {{ $bot['color'] }}15; border: 2px solid {{ $bot['color'] }}30;">
            {{ $bot['avatar'] }}
        </div>

        <div class="flex-1 min-w-0">
            <h1 class="font-semibold text-sm truncate">{{ $bot['name'] }}</h1>
            <p class="text-[11px] text-white/35 flex items-center gap-1.5 mt-0.5">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 flex-shrink-0" style="box-shadow: 0 0 6px rgba(74,222,128,0.5);"></span>
                <span>En ligne · IA</span>
            </p>
        </div>

        {{-- Reset --}}
        <form method="POST" action="{{ route('ai.reset', $session->id) }}" class="flex-shrink-0">
            @csrf
            <button type="submit" class="p-2 rounded-xl hover:bg-white/5 active:scale-95 transition" onclick="return confirm('Réinitialiser la conversation ?')" title="Nouvelle conversation">
                <svg class="w-5 h-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
            </button>
        </form>
    </header>

    {{-- Messages --}}
    <main id="chat-area" class="flex-1 overflow-y-auto chat-scroll px-4 py-4 space-y-2.5">
        @if($messages->isEmpty())
        <div class="flex flex-col items-center justify-center h-full text-center px-6">
            <div class="text-4xl mb-3">{{ $bot['avatar'] }}</div>
            <p class="text-sm font-medium text-white/50 mb-1">{{ $bot['name'] }}</p>
            <p class="text-[11px] text-white/25">{{ $bot['description'] }}</p>
        </div>
        @endif

        @foreach($messages as $msg)
        <div class="flex items-end gap-2 max-w-[85%] msg-in {{ $msg->role === 'user' ? 'ml-auto flex-row-reverse' : '' }}">
            @if($msg->role === 'assistant')
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs flex-shrink-0" style="background: {{ $bot['color'] }}20;">{{ $bot['avatar'] }}</div>
            @endif
            <div class="{{ $msg->role === 'user' ? 'bubble-sent' : 'bubble-received' }} px-3.5 py-2.5 max-w-full">
                <p class="text-[13px] leading-relaxed {{ $msg->role === 'user' ? 'text-white' : 'text-white/80' }} break-words whitespace-pre-line">{{ $msg->content }}</p>
            </div>
        </div>
        @endforeach

        {{-- Typing indicator --}}
        <div id="typing" class="hidden flex items-end gap-2 max-w-[80%]">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs flex-shrink-0" style="background: {{ $bot['color'] }}20;">{{ $bot['avatar'] }}</div>
            <div class="bubble-received px-4 py-3 flex items-center gap-1.5">
                <span class="typing-dot w-1.5 h-1.5 bg-white/40 rounded-full"></span>
                <span class="typing-dot w-1.5 h-1.5 bg-white/40 rounded-full"></span>
                <span class="typing-dot w-1.5 h-1.5 bg-white/40 rounded-full"></span>
            </div>
        </div>
    </main>

    {{-- Input --}}
    <footer class="flex-shrink-0 cc-surface border-t border-white/5 safe-bottom">
        <div class="flex items-end gap-2 px-3 py-2.5">
            <input type="text" id="msg-input" placeholder="Écris un message..." autocomplete="off" maxlength="500"
                class="chat-input flex-1 pl-4 pr-4 py-2.5 bg-white/[0.04] rounded-2xl text-[13px] text-white placeholder-white/20 outline-none border border-white/[0.06] transition">
            <button id="send-btn" onclick="sendMessage()" class="p-2.5 rounded-2xl text-white flex-shrink-0 active:scale-90 transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </div>
    </footer>
</div>

<script>
const sessionId = {{ $session->id }};
const chatArea = document.getElementById('chat-area');
const typingEl = document.getElementById('typing');
const input = document.getElementById('msg-input');
const sendBtn = document.getElementById('send-btn');
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const botAvatar = @json($bot['avatar']);
const botColor = @json($bot['color']);
let sending = false;

chatArea.scrollTop = chatArea.scrollHeight;

input.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });

async function sendMessage() {
    const text = input.value.trim();
    if (!text || sending) return;
    sending = true;
    input.value = '';

    // Afficher le message utilisateur
    appendMsg('user', text);

    // Afficher typing
    typingEl.classList.remove('hidden');
    chatArea.scrollTop = chatArea.scrollHeight;
    sendBtn.style.opacity = '0.5';
    sendBtn.style.pointerEvents = 'none';

    try {
        const res = await fetch('/ai/chat/' + sessionId + '/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ message: text }),
        });
        const data = await res.json();

        typingEl.classList.add('hidden');
        appendMsg('assistant', data.reply);

        if (data.limited) {
            input.disabled = true;
            input.placeholder = 'Limite atteinte pour aujourd\'hui';
        }
    } catch(e) {
        typingEl.classList.add('hidden');
        appendMsg('assistant', 'Erreur de connexion. Réessaie 🙏');
    }

    sending = false;
    sendBtn.style.opacity = '1';
    sendBtn.style.pointerEvents = 'auto';
    input.focus();
}

function appendMsg(role, text) {
    const div = document.createElement('div');
    div.className = 'flex items-end gap-2 max-w-[85%] msg-in ' + (role === 'user' ? 'ml-auto flex-row-reverse' : '');

    let avatar = '';
    if (role === 'assistant') {
        avatar = '<div class="w-6 h-6 rounded-full flex items-center justify-center text-xs flex-shrink-0" style="background:' + botColor + '20;">' + botAvatar + '</div>';
    }

    const bubbleClass = role === 'user' ? 'bubble-sent' : 'bubble-received';
    const textColor = role === 'user' ? 'text-white' : 'text-white/80';

    div.innerHTML = avatar + '<div class="' + bubbleClass + ' px-3.5 py-2.5 max-w-full"><p class="text-[13px] leading-relaxed ' + textColor + ' break-words whitespace-pre-line">' + escHtml(text) + '</p></div>';

    chatArea.insertBefore(div, typingEl);
    chatArea.scrollTop = chatArea.scrollHeight;
}

function escHtml(t) { return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', () => setTimeout(() => chatArea.scrollTop = chatArea.scrollHeight, 100));
}
</script>
</body>
</html>
