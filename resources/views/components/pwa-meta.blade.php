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

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- THEME SYSTEM — injecté ici pour couvrir toutes les pages  --}}
{{-- ═══════════════════════════════════════════════════════════ --}}

{{-- 1. Init immédiat (avant le paint) → évite le flash --}}
<script>
(function () {
    var t = localStorage.getItem('cc_theme') || 'dark';
    if (t === 'light') document.documentElement.classList.add('light');
})();
</script>

{{-- 2. Styles du mode clair (placé dans <head> mais la spécificité --}}
{{--    "html.light .classe" bat ".classe" seule → gagne sur les  --}}
{{--    <style> inline des pages autonomes)                        --}}
<style id="cc-theme-overrides">

/* ══════════════════════════════════════════════════
   VARIABLES MODE CLAIR
══════════════════════════════════════════════════ */
html.light {
    --cc-bg:            #fff5f7;
    --cc-surface:       rgba(255,255,255,0.82);
    --cc-surface-hover: rgba(255,94,108,0.07);
    --cc-border:        rgba(26,17,69,0.08);
    --cc-text:          #1a1145;
    --cc-text-muted:    rgba(26,17,69,0.45);
    --cc-accent-glow:   rgba(255,94,108,0.2);
    --cc-gradient-3:    linear-gradient(160deg, #fff5f7 0%, #fdedf2 40%, #f4f0ff 100%);
}

/* ══════════════════════════════════════════════════
   BODY & ARRIÈRE-PLAN
══════════════════════════════════════════════════ */
html.light body {
    background: linear-gradient(160deg, #fff5f7 0%, #fdedf2 40%, #f4f0ff 100%) !important;
    color: #1a1145 !important;
}
html.light .cc-bg-main {
    background: linear-gradient(160deg, #fff5f7 0%, #fdedf2 40%, #f4f0ff 100%) !important;
}
html.light .cc-bg-noise::before { opacity: 0.015; }

/* ══════════════════════════════════════════════════
   SURFACES & CARTES
══════════════════════════════════════════════════ */
html.light .cc-surface {
    background: rgba(255,255,255,0.82) !important;
    border-color: rgba(26,17,69,0.07) !important;
    box-shadow: 0 2px 20px rgba(255,94,108,0.06) !important;
}
html.light .cc-surface-raised {
    background: rgba(255,255,255,0.96) !important;
    border-color: rgba(26,17,69,0.06) !important;
    box-shadow: 0 6px 28px rgba(0,0,0,0.07), 0 1px 0 rgba(255,94,108,0.08) !important;
}
/* Orbes décoratifs */
html.light .orb,
html.light .cc-orb { opacity: 0.05 !important; }

/* ══════════════════════════════════════════════════
   NAVIGATION BAS
══════════════════════════════════════════════════ */
html.light #cc-bottom-nav {
    background: rgba(255,255,255,0.94) !important;
    border-color: rgba(26,17,69,0.06) !important;
    box-shadow: 0 -4px 24px rgba(255,94,108,0.06), 0 4px 20px rgba(0,0,0,0.04) !important;
}

/* ══════════════════════════════════════════════════
   TEXTE — opacity variants Tailwind
   On exclut les overlays photo et les boutons gradient
══════════════════════════════════════════════════ */
html.light .text-white                                   { color: #1a1145 !important; }
html.light .text-white\/80                               { color: rgba(26,17,69,0.82) !important; }
html.light .text-white\/70                               { color: rgba(26,17,69,0.70) !important; }
html.light .text-white\/60                               { color: rgba(26,17,69,0.60) !important; }
html.light .text-white\/50                               { color: rgba(26,17,69,0.50) !important; }
html.light .text-white\/40                               { color: rgba(26,17,69,0.40) !important; }
html.light .text-white\/35                               { color: rgba(26,17,69,0.35) !important; }
html.light .text-white\/30                               { color: rgba(26,17,69,0.30) !important; }
html.light .text-white\/25                               { color: rgba(26,17,69,0.25) !important; }
html.light .text-white\/20                               { color: rgba(26,17,69,0.20) !important; }
html.light .text-white\/15                               { color: rgba(26,17,69,0.15) !important; }
html.light .text-white\/10                               { color: rgba(26,17,69,0.10) !important; }

/* Boutons gradient et badges → texte reste blanc */
html.light .cc-btn-primary .text-white,
html.light button[style*="linear-gradient"] .text-white,
html.light a[style*="linear-gradient"] .text-white,
html.light .cc-badge .text-white                         { color: #ffffff !important; }

/* Texte dans les overlays photo des cartes swipe/likes → reste blanc */
html.light .swipe-card .text-white,
html.light .swipe-card .text-white\/80,
html.light .swipe-card .text-white\/50,
html.light .swipe-card .text-white\/40,
html.light .swipe-card .text-white\/30,
html.light .liker-card .absolute .text-white,
html.light .liker-card .absolute .text-white\/50,
html.light .liker-card .absolute .text-white\/40,
html.light .liker-card .absolute .text-white\/20       { color: #ffffff !important; }

/* Popup match → overlay sombre, texte blanc */
html.light #match-popup .text-white,
html.light #match-popup .text-white\/40,
html.light #match-popup .text-white\/70,
html.light #match-popup .text-white\/35,
html.light #match-popup .text-white\/80                { color: #ffffff !important; }

/* Modal filter, filter modal overlay */
html.light #filter-modal .text-white,
html.light #filter-modal .text-white\/40               { color: #1a1145 !important; }

/* Bulles de chat reçues */
html.light .bubble-received {
    background: rgba(26,17,69,0.06) !important;
    border-color: rgba(26,17,69,0.09) !important;
}
html.light .bubble-received .text-white\/80            { color: rgba(26,17,69,0.80) !important; }

/* ══════════════════════════════════════════════════
   FONDS BLANCS SEMI-TRANSPARENTS
══════════════════════════════════════════════════ */
html.light .bg-white\/5  { background-color: rgba(26,17,69,0.04) !important; }
html.light .bg-white\/8  { background-color: rgba(26,17,69,0.05) !important; }
html.light .bg-white\/10 { background-color: rgba(26,17,69,0.07) !important; }
html.light .bg-white\/15 { background-color: rgba(26,17,69,0.09) !important; }
html.light .bg-white\/20 { background-color: rgba(26,17,69,0.11) !important; }

/* ══════════════════════════════════════════════════
   BORDURES
══════════════════════════════════════════════════ */
html.light .border-white\/5  { border-color: rgba(26,17,69,0.05) !important; }
html.light .border-white\/8  { border-color: rgba(26,17,69,0.08) !important; }
html.light .border-white\/10 { border-color: rgba(26,17,69,0.10) !important; }
html.light .border-white\/15 { border-color: rgba(26,17,69,0.12) !important; }
html.light .border-white\/20 { border-color: rgba(26,17,69,0.15) !important; }

/* ══════════════════════════════════════════════════
   INPUTS & SELECTS
══════════════════════════════════════════════════ */
html.light .cc-input,
html.light input[type="text"],
html.light input[type="email"],
html.light input[type="password"],
html.light textarea,
html.light select {
    background: rgba(26,17,69,0.04) !important;
    border-color: rgba(26,17,69,0.10) !important;
    color: #1a1145 !important;
}
html.light .cc-input::placeholder,
html.light input::placeholder,
html.light textarea::placeholder {
    color: rgba(26,17,69,0.35) !important;
}
html.light .cc-input:focus,
html.light input:focus,
html.light textarea:focus {
    border-color: #ff5e6c !important;
    box-shadow: 0 0 0 4px rgba(255,94,108,0.12), 0 4px 20px rgba(0,0,0,0.05) !important;
    background: rgba(255,255,255,0.9) !important;
}
html.light select option { background: #ffffff; color: #1a1145; }

/* ══════════════════════════════════════════════════
   TAGS & CHIPS
══════════════════════════════════════════════════ */
html.light .cc-tag {
    background: rgba(26,17,69,0.05) !important;
    border-color: rgba(26,17,69,0.10) !important;
    color: rgba(26,17,69,0.70) !important;
}
html.light .cc-tag.active,
html.light .cc-tag:hover {
    background: #ff5e6c !important;
    border-color: #ff5e6c !important;
    color: white !important;
}

/* ══════════════════════════════════════════════════
   SCROLLBAR
══════════════════════════════════════════════════ */
html.light ::-webkit-scrollbar-thumb {
    background: rgba(26,17,69,0.12) !important;
}

/* ══════════════════════════════════════════════════
   BOUTON THEME TOGGLE
══════════════════════════════════════════════════ */
#cc-theme-toggle {
    position: fixed;
    bottom: 148px;
    right: 16px;
    z-index: 9999;
    width: 40px;
    height: 40px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s cubic-bezier(0.22,1,0.36,1),
                background 0.35s ease,
                box-shadow 0.35s ease,
                opacity 0.3s ease;
    -webkit-tap-highlight-color: transparent;
    outline: none;
    /* Dark mode style */
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    backdrop-filter: blur(20px);
    opacity: 0;  /* fade-in via JS */
}
#cc-theme-toggle:active { transform: scale(0.88); }
#cc-theme-toggle:hover  { transform: scale(1.08); }

html.light #cc-theme-toggle {
    background: rgba(255,255,255,0.92) !important;
    border-color: rgba(255,94,108,0.15) !important;
    box-shadow: 0 4px 20px rgba(255,94,108,0.12), 0 1px 8px rgba(0,0,0,0.06) !important;
}

#cc-theme-toggle .cc-icon-sun,
#cc-theme-toggle .cc-icon-moon { transition: opacity 0.25s, transform 0.35s cubic-bezier(0.34,1.56,0.64,1); position: absolute; }
#cc-theme-toggle .cc-icon-sun  { opacity: 0; transform: scale(0.4) rotate(90deg); }
#cc-theme-toggle .cc-icon-moon { opacity: 1; transform: scale(1) rotate(0deg); }

html.light #cc-theme-toggle .cc-icon-sun  { opacity: 1;  transform: scale(1) rotate(0deg); }
html.light #cc-theme-toggle .cc-icon-moon { opacity: 0;  transform: scale(0.4) rotate(-90deg); }

/* Spinner transition ring */
@keyframes cc-toggle-spin {
    from { transform: scale(0.7) rotate(-180deg); opacity: 0; }
    to   { transform: scale(1)   rotate(0deg);    opacity: 1; }
}
#cc-theme-toggle.cc-toggling { animation: cc-toggle-spin 0.35s cubic-bezier(0.22,1,0.36,1) both; }

</style>

{{-- 3. Injection du bouton + logique toggle --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Créer le bouton ── */
    var btn = document.createElement('button');
    btn.id = 'cc-theme-toggle';
    btn.setAttribute('aria-label', 'Changer le thème');
    btn.title = 'Mode clair / sombre';
    btn.innerHTML = `
        <svg class="cc-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="rgba(255,255,255,0.85)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
        <svg class="cc-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="#ff5e6c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="5"/>
            <line x1="12" y1="1"  x2="12" y2="3"/>
            <line x1="12" y1="21" x2="12" y2="23"/>
            <line x1="4.22"  y1="4.22"  x2="5.64"  y2="5.64"/>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
            <line x1="1"  y1="12" x2="3"  y2="12"/>
            <line x1="21" y1="12" x2="23" y2="12"/>
            <line x1="4.22"  y1="19.78" x2="5.64"  y2="18.36"/>
            <line x1="18.36" y1="5.64"  x2="19.78" y2="4.22"/>
        </svg>`;
    document.body.appendChild(btn);

    /* Fade-in différé */
    setTimeout(function () { btn.style.opacity = '1'; }, 400);

    /* ── Toggle ── */
    btn.addEventListener('click', function () {
        btn.classList.add('cc-toggling');
        setTimeout(function () { btn.classList.remove('cc-toggling'); }, 380);

        var isLight = document.documentElement.classList.toggle('light');
        localStorage.setItem('cc_theme', isLight ? 'light' : 'dark');

        /* Mettre à jour le meta theme-color */
        var metaTheme = document.querySelector('meta[name="theme-color"]');
        if (metaTheme) metaTheme.content = isLight ? '#fdedf2' : '#ff5e6c';

        /* Appliquer les corrections sur les éléments à style inline */
        _patchInlineStyles(isLight);
    });

    /* Appliquer au chargement si light */
    if (document.documentElement.classList.contains('light')) {
        _patchInlineStyles(true);
    }

    /* ── Correction des éléments avec inline styles ── */
    function _patchInlineStyles(isLight) {
        /* Containers principaux : fond sombre codé en dur */
        var darkBgPatterns = [
            'rgba(12,10,26',
            'rgba(10,8,22',
            'rgba(26,17,69',
            'rgba(0,0,0,0.92)',
            'rgba(20,15,40'
        ];

        /* Header sticky + panels intérieurs des pages autonomes */
        document.querySelectorAll('header, .sticky, [class*="cc-surface"]').forEach(function(el) {
            var s = el.getAttribute('style') || '';
            var isDarkBg = darkBgPatterns.some(function(p){ return s.includes(p); });
            if (!isDarkBg) return;
            if (isLight) {
                el.dataset.origStyle = s;
                el.style.background = 'rgba(255,255,255,0.94)';
                el.style.borderColor = 'rgba(26,17,69,0.07)';
                el.style.boxShadow   = '0 2px 16px rgba(255,94,108,0.07)';
            } else if (el.dataset.origStyle !== undefined) {
                el.setAttribute('style', el.dataset.origStyle);
                delete el.dataset.origStyle;
            }
        });

        /* Blocs verre : background rgba(255,255,255,0.04) */
        document.querySelectorAll('[style*="rgba(255,255,255,0.04)"]').forEach(function(el) {
            if (isLight) {
                el.dataset.origBg = el.style.background || '';
                el.style.background = 'rgba(255,255,255,0.82)';
            } else if (el.dataset.origBg !== undefined) {
                el.style.background = el.dataset.origBg;
                delete el.dataset.origBg;
            }
        });

        /* Match popup + overlays modaux → gardent leur fond sombre */
        /* (déjà exclus par CSS, rien à patcher ici) */

        /* Couleur de fond du nav bottom si inline */
        var nav = document.getElementById('cc-bottom-nav');
        if (nav) {
            nav.style.background = isLight
                ? 'rgba(255,255,255,0.94)'
                : '';
        }
    }
});
</script>

{{-- Service Worker --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const authPath = ['/login', '/register', '/forgot-password', '/reset-password'].some(path => location.pathname.startsWith(path));

        if (authPath && 'caches' in window) {
            caches.keys()
                .then(keys => Promise.all(keys.filter(key => key.startsWith('cc-pages')).map(key => caches.delete(key))))
                .catch(() => {});
        }

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
    setTimeout(() => {
        if (!document.getElementById('pwa-install-banner')) return;
        document.getElementById('pwa-install-banner').classList.remove('hidden');
    }, 5000);
});
function installPWA() {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then((choice) => {
        if (choice.outcome === 'accepted') console.log('🎉 PWA installée');
        deferredPrompt = null;
        document.getElementById('pwa-install-banner')?.classList.add('hidden');
    });
}
</script>
