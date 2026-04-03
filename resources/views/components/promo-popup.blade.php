{{-- resources/views/components/promo-popup.blade.php --}}
{{-- Inclure dans home.blade.php avec : @include('components.promo-popup') --}}

<div id="promo-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 99999;
    background: rgba(12,10,26,0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    align-items: center;
    justify-content: center;
    padding: 16px;
    font-family: 'Sora', sans-serif;
">
    <div id="promo-card" style="
        width: 100%; max-width: 360px;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.10);
        background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 50%, #0f1a3a 100%);
        position: relative;
        animation: promoIn 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
    ">
        {{-- Orbs décoratifs --}}
        <div style="position:absolute; width:180px; height:180px; border-radius:50%; background:#ff5e6c; filter:blur(80px); opacity:0.07; top:-60px; right:-40px; pointer-events:none;"></div>
        <div style="position:absolute; width:140px; height:140px; border-radius:50%; background:#a855f7; filter:blur(70px); opacity:0.07; bottom:-40px; left:-30px; pointer-events:none;"></div>

        {{-- Bouton fermer --}}
        <button onclick="closePromo()" style="
            position: absolute; top: 14px; right: 14px;
            width: 30px; height: 30px; border-radius: 50%;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.10);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,0.40); font-size: 14px; line-height: 1;
            transition: background 0.2s;
        " onmouseenter="this.style.background='rgba(255,255,255,0.14)'" onmouseleave="this.style.background='rgba(255,255,255,0.07)'">✕</button>

        <div style="padding: 30px 24px 26px;">

            {{-- Titre --}}
            <div style="text-align: center; margin-bottom: 18px;">
                <div style="display:inline-flex; align-items:center; gap:6px; margin-bottom:6px;">
                    <span style="font-size:20px;">💝</span>
                    <span style="
                        font-size: 13px; font-weight: 700; letter-spacing: 0.08em;
                        background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145);
                        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
                    ">OFFRE SPÉCIALE ÉTUDIANTES</span>
                </div>
                <p style="font-size: 11px; color: rgba(255,255,255,0.30); margin: 0; letter-spacing: 0.04em;">
                    Limitée aux 50 premières inscrites ✨
                </p>
            </div>

            {{-- Avantage 1 : 3 mois gratuits --}}
            <div style="
                background: rgba(255,94,108,0.08);
                border: 1px solid rgba(255,94,108,0.18);
                border-radius: 16px; padding: 16px 18px;
                margin-bottom: 12px;
                display: flex; align-items: center; gap: 14px;
            ">
                <span style="font-size: 28px; flex-shrink: 0;">🎁</span>
                <div>
                    <p style="font-size: 18px; font-weight: 700; color: #fff; margin: 0 0 3px;">3 mois gratuits</p>
                    <p style="font-size: 12px; color: rgba(255,255,255,0.38); margin: 0;">Accès premium complet offert</p>
                </div>
            </div>

            {{-- Avantage 2 : Campus Queen --}}
            <div style="
                background: rgba(255,193,69,0.07);
                border: 1px solid rgba(255,193,69,0.18);
                border-radius: 16px; padding: 16px 18px;
                margin-bottom: 20px;
                display: flex; align-items: center; gap: 14px;
            ">
                <span style="font-size: 28px; flex-shrink: 0;">⭐</span>
                <div>
                    <p style="font-size: 18px; font-weight: 700; color: #fff; margin: 0 0 3px;">
                        Badge <span style="background:linear-gradient(135deg,#ffc145,#ff8a5c); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">"Campus Queen"</span>
                    </p>
                    <p style="font-size: 12px; color: rgba(255,255,255,0.38); margin: 0;">Visible sur ton profil</p>
                </div>
            </div>

            {{-- Barre de progression (places restantes) --}}
            @php
                // Nombre de femmes inscrites (hors compte test aminata@)
                $femalesCount = \App\Models\Profile::where('gender', 'femme')->count();
                $taken   = min($femalesCount, 50);
                $left    = max(0, 50 - $taken);
                $pct     = round(($taken / 50) * 100);
                $offerActive = $left > 0;
            @endphp

            @if($offerActive)
            <div style="margin-bottom: 18px;">
                <div style="background: rgba(255,255,255,0.07); border-radius: 10px; height: 6px; overflow: hidden;">
                    <div style="
                        width: {{ $pct }}%; height: 100%;
                        background: linear-gradient(90deg, #ff5e6c, #ffc145);
                        border-radius: 10px;
                        transition: width 1s ease;
                    "></div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 6px;">
                    <span style="font-size: 11px; color: rgba(255,255,255,0.30);">{{ $taken }} places prises</span>
                    <span style="font-size: 11px; color: #ff8a5c; font-weight: 600;">{{ $left }} restante{{ $left > 1 ? 's' : '' }}</span>
                </div>
            </div>

            {{-- CTA --}}
            <a href="{{ route('register') }}" style="
                display: block; width: 100%;
                padding: 14px;
                border-radius: 14px;
                border: none;
                font-size: 14px; font-weight: 700;
                color: #fff; text-align: center; text-decoration: none;
                background: linear-gradient(135deg, #ff5e6c, #ff8a5c);
                box-shadow: 0 8px 30px rgba(255,94,108,0.28);
                letter-spacing: 0.02em;
                transition: transform 0.15s, box-shadow 0.15s;
            "
            onmouseenter="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 12px 36px rgba(255,94,108,0.38)'"
            onmouseleave="this.style.transform=''; this.style.boxShadow='0 8px 30px rgba(255,94,108,0.28)'">
                J'en profite maintenant →
            </a>
            @else
            {{-- Offre expirée --}}
            <div style="text-align:center; padding: 12px 0 4px;">
                <p style="font-size: 13px; color: rgba(255,255,255,0.35); margin: 0;">L'offre est terminée — les 50 places sont prises 🎉</p>
                <a href="{{ route('register') }}" style="
                    display:inline-block; margin-top:14px; padding:12px 28px;
                    border-radius:14px; border:1px solid rgba(255,255,255,0.10);
                    font-size:13px; font-weight:600; color:rgba(255,255,255,0.70);
                    text-decoration:none; background:rgba(255,255,255,0.05);
                ">S'inscrire quand même</a>
            </div>
            @endif

            {{-- Lien "ne plus afficher" --}}
            <p onclick="closePromo(true)" style="
                text-align: center; font-size: 11px;
                color: rgba(255,255,255,0.20);
                margin-top: 14px; margin-bottom: 0;
                cursor: pointer;
                transition: color 0.2s;
            "
            onmouseenter="this.style.color='rgba(255,255,255,0.40)'"
            onmouseleave="this.style.color='rgba(255,255,255,0.20)'"
            >Ne plus afficher</p>
        </div>
    </div>
</div>

<style>
@keyframes promoIn {
    from { opacity: 0; transform: scale(0.88) translateY(20px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<script>
(function () {
    const STORAGE_KEY = 'cc_promo_dismissed';

    // N'afficher que si l'utilisateur n'a pas cliqué "ne plus afficher"
    if (localStorage.getItem(STORAGE_KEY)) return;

    // Afficher avec un léger délai pour laisser la page se charger
    setTimeout(function () {
        const overlay = document.getElementById('promo-overlay');
        if (overlay) overlay.style.display = 'flex';
    }, 800);

    // Fermer en cliquant sur l'overlay (hors la carte)
    document.getElementById('promo-overlay').addEventListener('click', function (e) {
        if (e.target === this) closePromo();
    });
})();

function closePromo(permanent) {
    const overlay = document.getElementById('promo-overlay');
    if (!overlay) return;

    // Animation de sortie
    const card = document.getElementById('promo-card');
    if (card) {
        card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        card.style.opacity    = '0';
        card.style.transform  = 'scale(0.92) translateY(10px)';
    }
    overlay.style.transition = 'opacity 0.3s ease';
    overlay.style.opacity    = '0';

    setTimeout(function () { overlay.style.display = 'none'; }, 300);

    // Mémoriser le choix si "ne plus afficher"
    if (permanent) {
        localStorage.setItem('cc_promo_dismissed', '1');
    }
}
</script>