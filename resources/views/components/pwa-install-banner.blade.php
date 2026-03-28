{{-- PWA Install Banner - Include dans les pages principales (swipe, matches, etc.) --}}
<div id="pwa-install-banner" class="hidden fixed bottom-20 left-4 right-4 z-50 max-w-md mx-auto">
    <div class="rounded-2xl p-4 flex items-center gap-4" style="background: rgba(26,17,69,0.95); border: 1px solid rgba(255,94,108,0.2); backdrop-filter: blur(20px); box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
        <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
            <img src="/images/icons/icon-192x192.png" class="w-full h-full object-cover" alt="Campus Crush" onerror="this.parentElement.innerHTML='🔥'">
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-white">Installer Campus Crush</p>
            <p class="text-[11px] text-white/40">Accès rapide depuis l'écran d'accueil</p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button onclick="document.getElementById('pwa-install-banner').classList.add('hidden')" class="p-2 text-white/20 hover:text-white/50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <button onclick="installPWA()" class="px-4 py-2 rounded-xl text-xs font-semibold text-white" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                Installer
            </button>
        </div>
    </div>
</div>
