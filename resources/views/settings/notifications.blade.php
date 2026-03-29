@extends('layouts.app')

@section('content')
@include('components.push-notifications')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
<div class="relative z-10 w-full max-w-md text-white px-5 py-8">

    <div class="flex items-center gap-4 mb-8 cc-fade-up">
        <a href="{{ route('settings') }}" class="p-2 rounded-xl cc-surface hover:bg-white/10 transition">
            <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold">Notifications</h1>
    </div>

    {{-- Push Notifications --}}
    <div class="cc-surface-raised rounded-3xl p-6 mb-4 cc-fade-up" style="animation-delay:0.1s">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <span class="text-xl">🔔</span>
                <div>
                    <h3 class="font-semibold text-sm">Notifications push</h3>
                    <p class="text-[11px] text-white/30">Reçois des alertes sur ton appareil</p>
                </div>
            </div>
            <div id="push-status" class="flex items-center gap-2">
                <span id="push-status-text" class="text-[10px] text-white/30">Vérification...</span>
            </div>
        </div>

        <div id="push-enabled" class="hidden">
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-green-500/10 border border-green-500/20 mb-4">
                <span class="text-green-400 text-xs">✓</span>
                <span class="text-xs text-green-400">Notifications activées</span>
            </div>
            <button onclick="disablePush()" class="w-full py-3 rounded-xl text-xs font-medium text-white/40 border border-white/10 hover:bg-white/5 transition">
                Désactiver les notifications
            </button>
        </div>

        <div id="push-disabled" class="hidden">
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 border border-white/5 mb-4">
                <span class="text-white/30 text-xs">○</span>
                <span class="text-xs text-white/30">Notifications désactivées</span>
            </div>
            <button onclick="if(typeof enablePushNotifications==='function') enablePushNotifications(); else alert('Recharge la page et réessaie.');" class="w-full py-3 rounded-xl text-xs font-semibold text-white transition hover:-translate-y-0.5" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                Activer les notifications 🔔
            </button>
        </div>

        <div id="push-denied" class="hidden">
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-red-500/10 border border-red-500/20 mb-3">
                <span class="text-red-400 text-xs">✕</span>
                <span class="text-xs text-red-400">Bloquées par le navigateur</span>
            </div>
            <p class="text-[11px] text-white/30 leading-relaxed">
                Tu as bloqué les notifications. Pour les réactiver, clique sur le cadenas 🔒 à gauche de la barre d'adresse → Notifications → Autoriser.
            </p>
        </div>

        <div id="push-unsupported" class="hidden">
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-yellow-500/10 border border-yellow-500/20">
                <span class="text-yellow-400 text-xs">⚠️</span>
                <span class="text-xs text-yellow-400">Non supporté par ton navigateur</span>
            </div>
        </div>
    </div>

    {{-- Types de notifications --}}
    <div class="cc-surface rounded-3xl p-6 cc-fade-up" style="animation-delay:0.2s">
        <h3 class="font-semibold text-sm mb-4">Tu seras notifié(e) pour :</h3>
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(255,94,108,0.1);">💕</span>
                <div class="flex-1">
                    <p class="text-sm text-white/70">Nouveaux matchs</p>
                    <p class="text-[10px] text-white/30">Quand quelqu'un te like aussi</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,0.1);">💬</span>
                <div class="flex-1">
                    <p class="text-sm text-white/70">Nouveaux messages</p>
                    <p class="text-[10px] text-white/30">Quand tu reçois un message</p>
                </div>
            </div>
        </div>
    </div>

    @include('components.bottom-nav')
</div>
</div>

@push('scripts')
<script>
async function checkPushStatus() {
    const statusText = document.getElementById('push-status-text');

    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        document.getElementById('push-unsupported').classList.remove('hidden');
        statusText.textContent = 'Non supporté';
        return;
    }

    if (Notification.permission === 'denied') {
        document.getElementById('push-denied').classList.remove('hidden');
        statusText.textContent = 'Bloquées';
        return;
    }

    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();

    if (subscription) {
        document.getElementById('push-enabled').classList.remove('hidden');
        statusText.innerHTML = '<span class="w-2 h-2 bg-green-400 rounded-full inline-block"></span>';
    } else {
        document.getElementById('push-disabled').classList.remove('hidden');
        statusText.innerHTML = '<span class="w-2 h-2 bg-white/20 rounded-full inline-block"></span>';
    }
}

async function disablePush() {
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();
    if (subscription) {
        const endpoint = subscription.endpoint;
        await subscription.unsubscribe();
        await fetch('/push/unsubscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ endpoint }),
        });
    }
    location.reload();
}

checkPushStatus();
</script>
@endpush
@endsection
