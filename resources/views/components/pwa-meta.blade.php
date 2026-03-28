{{-- PWA Meta Tags - Include dans tous les layouts --}}
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#ff5e6c">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Campus Crush">
<link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="Campus Crush">
<meta name="msapplication-TileColor" content="#0c0a1a">
<meta name="msapplication-TileImage" content="/images/icons/icon-144x144.png">

{{-- Service Worker --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('🔥 SW registered'))
            .catch(err => console.log('SW error:', err));
    });
}
</script>

{{-- Install PWA Banner --}}
<script>
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;

    // Montrer le bouton d'installation après 5 secondes
    setTimeout(() => {
        if (!document.getElementById('pwa-install-banner')) return;
        document.getElementById('pwa-install-banner').classList.remove('hidden');
    }, 5000);
});

function installPWA() {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then((choice) => {
        if (choice.outcome === 'accepted') {
            console.log('🎉 PWA installée');
        }
        deferredPrompt = null;
        document.getElementById('pwa-install-banner')?.classList.add('hidden');
    });
}
</script>
