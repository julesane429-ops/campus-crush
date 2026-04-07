{{--
    Bouton flottant IA — affiché sur toutes les pages sauf les pages ai-chat
    À inclure dans layouts/app.blade.php dans le bloc @auth
--}}
@php
    $currentRoute = request()->route()?->getName() ?? '';
    $hideOnRoutes = ['ai.index', 'ai.session', 'ai.unlock'];
@endphp

@auth
@if(!in_array($currentRoute, $hideOnRoutes))
<div id="ai-fab-wrapper" class="fixed bottom-[88px] right-4 z-50">

    {{-- Tooltip --}}
    <div id="ai-fab-tooltip"
         class="absolute bottom-full right-0 mb-2 px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap pointer-events-none opacity-0 transition-opacity duration-200"
         style="background: rgba(20,15,40,0.95); border: 1px solid rgba(255,94,108,0.3); color: #f0eef5;">
        Chat IA 🤖
    </div>

    {{-- Bouton --}}
    <a href="{{ route('ai.index') }}"
       id="ai-fab-btn"
       aria-label="Ouvrir le chat IA"
       class="relative flex items-center justify-center w-12 h-12 rounded-2xl shadow-lg transition-all duration-300 active:scale-90"
       style="background: linear-gradient(135deg, #ff5e6c 0%, #a855f7 100%);
              box-shadow: 0 4px 20px rgba(255,94,108,0.35), 0 2px 8px rgba(0,0,0,0.4);">

        {{-- Icône étincelle IA --}}
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"
                  fill="white" fill-opacity="0.95"/>
            <path d="M19 15L19.75 17.25L22 18L19.75 18.75L19 21L18.25 18.75L16 18L18.25 17.25L19 15Z"
                  fill="white" fill-opacity="0.7"/>
            <path d="M5 4L5.5 5.5L7 6L5.5 6.5L5 8L4.5 6.5L3 6L4.5 5.5L5 4Z"
                  fill="white" fill-opacity="0.6"/>
        </svg>

        {{-- Point de notification pulsé (optionnel — retire si tu veux pas) --}}
        <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full border-2"
              style="background: #ffc145; border-color: #0c0a1a;
                     animation: ai-fab-ping 2.5s cubic-bezier(0, 0, 0.2, 1) infinite;">
        </span>
    </a>
</div>

<style>
@keyframes ai-fab-ping {
    0%   { transform: scale(1); opacity: 1; }
    60%  { transform: scale(1.5); opacity: 0; }
    100% { transform: scale(1); opacity: 0; }
}
</style>

<script>
(function () {
    const btn     = document.getElementById('ai-fab-btn');
    const tooltip = document.getElementById('ai-fab-tooltip');
    if (!btn || !tooltip) return;

    btn.addEventListener('mouseenter', () => tooltip.style.opacity = '1');
    btn.addEventListener('mouseleave', () => tooltip.style.opacity = '0');

    // Légère animation d'entrée au chargement
    const wrapper = document.getElementById('ai-fab-wrapper');
    wrapper.style.opacity = '0';
    wrapper.style.transform = 'translateY(16px)';
    setTimeout(() => {
        wrapper.style.transition = 'opacity 0.4s ease, transform 0.4s cubic-bezier(0.22,1,0.36,1)';
        wrapper.style.opacity = '1';
        wrapper.style.transform = 'translateY(0)';
    }, 600);
})();
</script>
@endif
@endauth