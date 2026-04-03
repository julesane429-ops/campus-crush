{{-- resources/views/components/promo-popup.blade.php --}}
{{-- Inclure dans home.blade.php juste avant </body> avec : @include('components.promo-popup') --}}

@php
    // On exclut les profils seedés (faux profils) qui ont une photo générée
    // par le seeder (pattern avatars/F* ou avatars/H*) pour ne compter
    // que les vraies inscriptions réelles.
    $femalesCount = \App\Models\Profile::where('gender', 'femme')
        ->where(function($q) {
            $q->whereNull('photo')
              ->orWhere('photo', 'NOT LIKE', 'avatars/F%');
        })->count();

    $malesCount = \App\Models\Profile::where('gender', 'homme')
        ->where(function($q) {
            $q->whereNull('photo')
              ->orWhere('photo', 'NOT LIKE', 'avatars/H%');
        })->count();

    $queenLimit = 100;
    $kingLimit  = 50;

    $queenTaken = min($femalesCount, $queenLimit);
    $queenLeft  = max(0, $queenLimit - $queenTaken);
    $queenPct   = round(($queenTaken / $queenLimit) * 100);

    $kingTaken  = min($malesCount, $kingLimit);
    $kingLeft   = max(0, $kingLimit - $kingTaken);
    $kingPct    = round(($kingTaken / $kingLimit) * 100);

    // N'afficher le popup que si au moins une offre est encore active
    $showPopup  = $queenLeft > 0 || $kingLeft > 0;
@endphp

@if($showPopup)
<div id="promo-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 99999;
    background: rgba(12,10,26,0.88);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    align-items: center;
    justify-content: center;
    padding: 16px;
    font-family: 'Sora', sans-serif;
">
    <div id="promo-card" style="
        width: 100%;
        max-width: 680px;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.09);
        background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 50%, #0f1a3a 100%);
        position: relative;
        animation: promoIn 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
    ">
        {{-- Orbs décoratifs --}}
        <div style="position:absolute;width:200px;height:200px;border-radius:50%;background:#ff5e6c;filter:blur(90px);opacity:0.06;top:-60px;right:-50px;pointer-events:none;"></div>
        <div style="position:absolute;width:200px;height:200px;border-radius:50%;background:#639922;filter:blur(90px);opacity:0.06;bottom:-60px;left:-50px;pointer-events:none;"></div>

        {{-- Bouton fermer --}}
        <button onclick="closePromo()" style="
            position:absolute; top:14px; right:14px;
            width:30px; height:30px; border-radius:50%;
            background:rgba(255,255,255,0.07);
            border:1px solid rgba(255,255,255,0.10);
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            color:rgba(255,255,255,0.38); font-size:14px; line-height:1;
            transition:background 0.2s;
        " onmouseenter="this.style.background='rgba(255,255,255,0.14)'"
           onmouseleave="this.style.background='rgba(255,255,255,0.07)'">✕</button>

        <div style="padding: 28px 20px 22px;">

            {{-- Titre --}}
            <div style="text-align:center; margin-bottom:20px;">
                <div style="
                    font-size:12px; font-weight:700; letter-spacing:0.10em;
                    background:linear-gradient(135deg,#ff5e6c,#ff8a5c,#ffc145);
                    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
                    margin-bottom:5px;
                ">🎓 OFFRES SPÉCIALES LANCEMENT</div>
                <p style="font-size:11px; color:rgba(255,255,255,0.28); margin:0;">
                    Places limitées · Profites-en avant tout le monde ✨
                </p>
            </div>

            {{-- Grille 2 colonnes (passe en 1 colonne sous 480px via media query) --}}
            <div class="promo-grid">

                {{-- ══ CAMPUS QUEEN ══ --}}
                @if($queenLeft > 0)
                <div style="
                    border-radius:18px;
                    border:1px solid rgba(255,94,108,0.22);
                    background:rgba(255,94,108,0.07);
                    padding:18px 16px;
                ">
                    <div style="text-align:center; margin-bottom:16px;">
                        <div style="font-size:26px; margin-bottom:5px;">👑</div>
                        <div style="font-size:12px; font-weight:700; letter-spacing:0.07em; color:#ff8a5c;">CAMPUS QUEEN</div>
                        <div style="font-size:10px; color:rgba(255,255,255,0.28); margin-top:3px;">Étudiantes · {{ $queenLimit }} places</div>
                    </div>

                    <div style="background:rgba(255,255,255,0.05); border-radius:11px; padding:11px 13px; margin-bottom:8px; display:flex; align-items:center; gap:10px;">
                        <span style="font-size:18px; flex-shrink:0;">🎁</span>
                        <div>
                            <p style="font-size:14px; font-weight:700; color:#fff; margin:0 0 2px;">3 mois gratuits</p>
                            <p style="font-size:11px; color:rgba(255,255,255,0.32); margin:0;">Accès premium complet</p>
                        </div>
                    </div>

                    <div style="background:rgba(255,255,255,0.05); border-radius:11px; padding:11px 13px; margin-bottom:14px; display:flex; align-items:center; gap:10px;">
                        <span style="font-size:18px; flex-shrink:0;">⭐</span>
                        <div>
                            <p style="font-size:14px; font-weight:700; color:#fff; margin:0 0 2px;">Badge <span style="background:linear-gradient(135deg,#ffc145,#ff8a5c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">"Campus Queen"</span></p>
                            <p style="font-size:11px; color:rgba(255,255,255,0.32); margin:0;">Visible sur ton profil</p>
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <div style="background:rgba(255,255,255,0.07); border-radius:8px; height:5px; overflow:hidden;">
                            <div style="width:{{ $queenPct }}%; height:100%; background:linear-gradient(90deg,#ff5e6c,#ffc145); border-radius:8px;"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-top:5px;">
                            <span style="font-size:10px; color:rgba(255,255,255,0.28);">{{ $queenTaken }} prises</span>
                            <span style="font-size:10px; color:#ff8a5c; font-weight:600;">{{ $queenLeft }} restante{{ $queenLeft > 1 ? 's' : '' }}</span>
                        </div>
                    </div>

                    <a href="{{ route('register') }}" style="
                        display:block; width:100%; box-sizing:border-box;
                        padding:12px; border-radius:13px; border:none;
                        font-size:13px; font-weight:700; color:#fff;
                        text-align:center; text-decoration:none;
                        background:linear-gradient(135deg,#ff5e6c,#ff8a5c);
                        box-shadow:0 6px 24px rgba(255,94,108,0.25);
                        transition:transform 0.15s;
                    " onmouseenter="this.style.transform='translateY(-1px)'"
                       onmouseleave="this.style.transform=''">J'en profite →</a>
                </div>
                @else
                <div style="border-radius:18px; border:1px solid rgba(255,94,108,0.12); background:rgba(255,94,108,0.04); padding:18px 16px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; gap:8px;">
                    <span style="font-size:26px;">👑</span>
                    <p style="font-size:13px; font-weight:700; color:rgba(255,255,255,0.40); margin:0;">Campus Queen</p>
                    <p style="font-size:11px; color:rgba(255,255,255,0.22); margin:0;">Les {{ $queenLimit }} places sont prises 🎉</p>
                </div>
                @endif

                {{-- ══ CAMPUS KING ══ --}}
                @if($kingLeft > 0)
                <div style="
                    border-radius:18px;
                    border:1px solid rgba(99,153,34,0.25);
                    background:rgba(99,153,34,0.07);
                    padding:18px 16px;
                ">
                    <div style="text-align:center; margin-bottom:16px;">
                        <div style="font-size:26px; margin-bottom:5px;">🏆</div>
                        <div style="font-size:12px; font-weight:700; letter-spacing:0.07em; color:#8fc740;">CAMPUS KING</div>
                        <div style="font-size:10px; color:rgba(255,255,255,0.28); margin-top:3px;">Étudiants · {{ $kingLimit }} places</div>
                    </div>

                    <div style="background:rgba(255,255,255,0.05); border-radius:11px; padding:11px 13px; margin-bottom:8px; display:flex; align-items:center; gap:10px;">
                        <span style="font-size:18px; flex-shrink:0;">🎁</span>
                        <div>
                            <p style="font-size:14px; font-weight:700; color:#fff; margin:0 0 2px;">1 mois gratuit</p>
                            <p style="font-size:11px; color:rgba(255,255,255,0.32); margin:0;">Accès premium complet</p>
                        </div>
                    </div>

                    <div style="background:rgba(255,255,255,0.05); border-radius:11px; padding:11px 13px; margin-bottom:14px; display:flex; align-items:center; gap:10px;">
                        <span style="font-size:18px; flex-shrink:0;">🏅</span>
                        <div>
                            <p style="font-size:14px; font-weight:700; color:#fff; margin:0 0 2px;">Badge <span style="background:linear-gradient(135deg,#8fc740,#ffc145);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">"Campus King"</span></p>
                            <p style="font-size:11px; color:rgba(255,255,255,0.32); margin:0;">Visible sur ton profil</p>
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <div style="background:rgba(255,255,255,0.07); border-radius:8px; height:5px; overflow:hidden;">
                            <div style="width:{{ $kingPct }}%; height:100%; background:linear-gradient(90deg,#639922,#8fc740); border-radius:8px;"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-top:5px;">
                            <span style="font-size:10px; color:rgba(255,255,255,0.28);">{{ $kingTaken }} pris</span>
                            <span style="font-size:10px; color:#8fc740; font-weight:600;">{{ $kingLeft }} restant{{ $kingLeft > 1 ? 's' : '' }}</span>
                        </div>
                    </div>

                    <a href="{{ route('register') }}" style="
                        display:block; width:100%; box-sizing:border-box;
                        padding:12px; border-radius:13px; border:none;
                        font-size:13px; font-weight:700; color:#fff;
                        text-align:center; text-decoration:none;
                        background:linear-gradient(135deg,#639922,#8fc740);
                        box-shadow:0 6px 24px rgba(99,153,34,0.22);
                        transition:transform 0.15s;
                    " onmouseenter="this.style.transform='translateY(-1px)'"
                       onmouseleave="this.style.transform=''">J'en profite →</a>
                </div>
                @else
                <div style="border-radius:18px; border:1px solid rgba(99,153,34,0.12); background:rgba(99,153,34,0.04); padding:18px 16px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; gap:8px;">
                    <span style="font-size:26px;">🏆</span>
                    <p style="font-size:13px; font-weight:700; color:rgba(255,255,255,0.40); margin:0;">Campus King</p>
                    <p style="font-size:11px; color:rgba(255,255,255,0.22); margin:0;">Les {{ $kingLimit }} places sont prises 🎉</p>
                </div>
                @endif

            </div>{{-- /grid --}}

            <p onclick="closePromo(true)" style="
                text-align:center; font-size:11px;
                color:rgba(255,255,255,0.18);
                margin:16px 0 0; cursor:pointer;
                transition:color 0.2s;
            " onmouseenter="this.style.color='rgba(255,255,255,0.38)'"
               onmouseleave="this.style.color='rgba(255,255,255,0.18)'"
            >Ne plus afficher</p>
        </div>
    </div>
</div>

<style>
@keyframes promoIn {
    from { opacity:0; transform:scale(0.88) translateY(20px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
.promo-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 4px;
}
@media (max-width: 480px) {
    .promo-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {
    const STORAGE_KEY = 'cc_promo_v2_dismissed';
    if (localStorage.getItem(STORAGE_KEY)) return;

    setTimeout(function () {
        const overlay = document.getElementById('promo-overlay');
        if (overlay) overlay.style.display = 'flex';
    }, 800);

    document.getElementById('promo-overlay').addEventListener('click', function (e) {
        if (e.target === this) closePromo();
    });
})();

function closePromo(permanent) {
    const overlay = document.getElementById('promo-overlay');
    if (!overlay) return;
    const card = document.getElementById('promo-card');
    if (card) {
        card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        card.style.opacity    = '0';
        card.style.transform  = 'scale(0.92) translateY(10px)';
    }
    overlay.style.transition = 'opacity 0.3s ease';
    overlay.style.opacity    = '0';
    setTimeout(function () { overlay.style.display = 'none'; }, 300);
    if (permanent) localStorage.setItem('cc_promo_v2_dismissed', '1');
}
</script>
@endif