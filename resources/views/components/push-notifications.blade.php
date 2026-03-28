{{-- 
    Push Notification Component
    Include in layouts AFTER pwa-meta.blade.php
    
    Usage: @include('components.push-notifications')
--}}

<script>
(function() {
    const VAPID_PUBLIC_KEY = '{{ config("webpush.vapid.public_key") }}';
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

    // Ne rien faire si pas de support ou pas de clé VAPID
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !VAPID_PUBLIC_KEY) {
        return;
    }

    // Attendre que le SW soit prêt
    navigator.serviceWorker.ready.then(async (registration) => {

        // Vérifier si déjà abonné
        const existingSub = await registration.pushManager.getSubscription();
        if (existingSub) {
            // Déjà abonné, envoyer au serveur au cas où
            sendSubToServer(existingSub);
            return;
        }

        // Demander la permission après un délai (pas au premier chargement)
        const alreadyAsked = localStorage.getItem('cc_push_asked');
        if (alreadyAsked) return;

        // Attendre 30 secondes avant de demander
        setTimeout(() => {
            showPushPrompt(registration);
        }, 30000);
    });

    // Afficher la demande custom (pas le popup navigateur directement)
    function showPushPrompt(registration) {
        // Ne pas montrer si la permission est déjà refusée
        if (Notification.permission === 'denied') return;
        if (Notification.permission === 'granted') {
            subscribePush(registration);
            return;
        }

        const banner = document.createElement('div');
        banner.id = 'push-prompt';
        banner.innerHTML = `
            <div style="position:fixed; bottom:80px; left:16px; right:16px; max-width:400px; margin:auto; z-index:9999; border-radius:20px; padding:20px; background:rgba(26,17,69,0.97); border:1px solid rgba(255,94,108,0.2); backdrop-filter:blur(20px); box-shadow:0 20px 60px rgba(0,0,0,0.5);">
                <div style="display:flex; align-items:flex-start; gap:14px;">
                    <span style="font-size:28px; flex-shrink:0;">🔔</span>
                    <div style="flex:1;">
                        <p style="font-size:14px; font-weight:600; color:#f0eef5; margin:0 0 4px 0;">Activer les notifications ?</p>
                        <p style="font-size:11px; color:rgba(255,255,255,0.4); margin:0; line-height:1.5;">Reçois une alerte quand tu as un nouveau match ou message, même quand l'app est fermée.</p>
                    </div>
                    <button onclick="document.getElementById('push-prompt').remove(); localStorage.setItem('cc_push_asked','1');" style="background:none; border:none; color:rgba(255,255,255,0.2); cursor:pointer; font-size:18px; padding:0;">✕</button>
                </div>
                <div style="display:flex; gap:10px; margin-top:16px;">
                    <button id="push-later-btn" style="flex:1; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.1); background:transparent; color:rgba(255,255,255,0.4); font-size:12px; font-weight:500; cursor:pointer; font-family:Sora,sans-serif;">Plus tard</button>
                    <button id="push-accept-btn" style="flex:1; padding:12px; border-radius:14px; border:none; background:linear-gradient(135deg,#ff5e6c,#ff8a5c); color:white; font-size:12px; font-weight:600; cursor:pointer; font-family:Sora,sans-serif; box-shadow:0 4px 20px rgba(255,94,108,0.3);">Activer 🔔</button>
                </div>
            </div>
        `;
        document.body.appendChild(banner);

        document.getElementById('push-later-btn').onclick = () => {
            banner.remove();
            localStorage.setItem('cc_push_asked', '1');
        };

        document.getElementById('push-accept-btn').onclick = async () => {
            banner.remove();
            localStorage.setItem('cc_push_asked', '1');
            await subscribePush(registration);
        };
    }

    async function subscribePush(registration) {
        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') return;

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
            });

            await sendSubToServer(subscription);
        } catch (err) {
            console.error('Push subscription error:', err);
        }
    }

    async function sendSubToServer(subscription) {
        try {
            const data = subscription.toJSON();
            await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    endpoint: data.endpoint,
                    keys: {
                        p256dh: data.keys.p256dh,
                        auth: data.keys.auth,
                    },
                    contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
                }),
            });
        } catch (err) {
            console.error('Push registration error:', err);
        }
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Exposer globalement pour un bouton manuel
    window.enablePushNotifications = async function() {
        const registration = await navigator.serviceWorker.ready;
        await subscribePush(registration);
    };
})();
</script>
