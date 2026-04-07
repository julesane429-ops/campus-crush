{{-- resources/views/components/features-popup.blade.php --}}
{{--
    Popup d'annonce des nouvelles fonctionnalités.
    Affiché une fois par version (clé localStorage : cc_features_v{VERSION}).
    Pour afficher à nouveau lors d'une prochaine feature, incrémente VERSION.
--}}

@php $VERSION = '5'; @endphp

<div id="features-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 99999;
    background: rgba(12,10,26,0.90);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    align-items: center;
    justify-content: center;
    padding: 16px;
    font-family: 'Sora', sans-serif;
">
    <div id="features-card" style="
        width: 100%; max-width: 380px;
        border-radius: 28px;
        border: 1px solid rgba(255,255,255,0.09);
        background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 55%, #0f1a3a 100%);
        position: relative;
        overflow: hidden;
        animation: featIn 0.45s cubic-bezier(0.22,1,0.36,1) both;
    ">
        {{-- Orbs déco --}}
        <div style="position:absolute;width:180px;height:180px;border-radius:50%;background:#ff5e6c;filter:blur(90px);opacity:0.07;top:-60px;right:-40px;pointer-events:none;"></div>
        <div style="position:absolute;width:160px;height:160px;border-radius:50%;background:#a855f7;filter:blur(80px);opacity:0.06;bottom:-40px;left:-30px;pointer-events:none;"></div>

        {{-- Bouton fermer --}}
        <button onclick="closeFeatures()" style="
            position:absolute; top:14px; right:14px; z-index:2;
            width:30px; height:30px; border-radius:50%;
            background:rgba(255,255,255,0.07);
            border:1px solid rgba(255,255,255,0.10);
            cursor:pointer; color:rgba(255,255,255,0.38); font-size:14px;
            display:flex; align-items:center; justify-content:center;
            transition:background 0.2s;
        " onmouseenter="this.style.background='rgba(255,255,255,0.14)'"
            onmouseleave="this.style.background='rgba(255,255,255,0.07)'">✕</button>

        {{-- Slides --}}
        <div id="features-slides" style="overflow:hidden;">
            <div style="display:flex; transition:transform 0.4s cubic-bezier(0.22,1,0.36,1);" id="slides-track">

                {{-- ══ SLIDE 1 : Parrainage ══ --}}
                <div class="feat-slide" style="min-width:100%; padding:32px 24px 24px;">
                    <div style="text-align:center; margin-bottom:20px;">
                        <div style="font-size:48px; margin-bottom:12px;">🎁</div>
                        <div style="font-size:11px; font-weight:700; letter-spacing:0.10em; margin-bottom:6px;
                            background:linear-gradient(135deg,#a855f7,#ff5e6c);
                            -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                            NOUVEAUTÉ — PARRAINAGE
                        </div>
                        <h2 style="font-size:20px; font-weight:800; color:#fff; margin-bottom:8px; line-height:1.3;">
                            Invite tes amis,<br>gagne du premium
                        </h2>
                        <p style="font-size:13px; color:rgba(255,255,255,0.45); line-height:1.6;">
                            Partage ton lien unique à tes ami(e)s. Pour chaque ami qui
                            s'inscrit et crée son profil, tu reçois automatiquement
                            <span style="color:#fff; font-weight:600;">7 jours de premium offerts</span>.
                        </p>
                    </div>

                    <div style="background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.18); border-radius:16px; padding:14px 16px; margin-bottom:20px;">
                        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-size:16px; flex-shrink:0;">🔗</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">Partage ton lien sur WhatsApp, Instagram ou par SMS</p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-size:16px; flex-shrink:0;">✅</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">Ton ami(e) s'inscrit et crée son profil</p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start;">
                            <span style="font-size:16px; flex-shrink:0;">🎉</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">Tu reçois <strong style="color:#a855f7;">+7 jours premium</strong> automatiquement</p>
                        </div>
                    </div>

                    <a href="{{ route('referral.index') }}" onclick="closeFeatures()" style="
                        display:block; width:100%; padding:13px; border-radius:14px;
                        text-align:center; text-decoration:none;
                        font-size:13px; font-weight:700; color:#fff;
                        background:linear-gradient(135deg,#a855f7,#ff5e6c);
                        box-shadow:0 6px 24px rgba(168,85,247,0.25);
                        margin-bottom:10px;
                    ">Accéder au parrainage →</a>
                </div>

                {{-- ══ SLIDE 2 : Boost ══ --}}
                <div class="feat-slide" style="min-width:100%; padding:32px 24px 24px;">
                    <div style="text-align:center; margin-bottom:20px;">
                        <div style="font-size:48px; margin-bottom:12px;">🚀</div>
                        <div style="font-size:11px; font-weight:700; letter-spacing:0.10em; margin-bottom:6px;
                            background:linear-gradient(135deg,#ffc145,#ff8a5c);
                            -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                            NOUVEAUTÉ — BOOST PROFIL
                        </div>
                        <h2 style="font-size:20px; font-weight:800; color:#fff; margin-bottom:8px; line-height:1.3;">
                            Passe en tête<br>du swipe pendant 24h
                        </h2>
                        <p style="font-size:13px; color:rgba(255,255,255,0.45); line-height:1.6;">
                            Ton profil est vu en <span style="color:#fff; font-weight:600;">priorité par tous les utilisateurs</span>
                            pendant 24 heures. Jusqu'à 10× plus de vues garanties.
                        </p>
                    </div>

                    <div style="background:rgba(255,193,69,0.07); border:1px solid rgba(255,193,69,0.18); border-radius:16px; padding:14px 16px; margin-bottom:20px;">
                        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-size:16px; flex-shrink:0;">👑</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">Ton profil apparaît avant tous les autres dans le swipe</p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-size:16px; flex-shrink:0;">🏅</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">Un badge <strong style="color:#ffc145;">🚀 Boosté</strong> visible sur ta carte</p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start;">
                            <span style="font-size:16px; flex-shrink:0;">💰</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">Seulement <strong style="color:#ffc145;">500 FCFA</strong> pour 24h — paiement mobile money</p>
                        </div>
                    </div>

                    <a href="{{ route('boost.index') }}" onclick="closeFeatures()" style="
                        display:block; width:100%; padding:13px; border-radius:14px;
                        text-align:center; text-decoration:none;
                        font-size:13px; font-weight:700; color:#fff;
                        background:linear-gradient(135deg,#ffc145,#ff8a5c);
                        box-shadow:0 6px 24px rgba(255,193,69,0.25);
                        margin-bottom:10px;
                    ">Booster mon profil — 500 FCFA →</a>
                </div>

                {{-- ══ SLIDE 3 : Guide paiement PayDunya ══ --}}
                <div class="feat-slide" style="min-width:100%; padding:32px 24px 24px;">
                    <div style="text-align:center; margin-bottom:20px;">
                        <div style="font-size:48px; margin-bottom:12px;">💳</div>
                        <div style="font-size:11px; font-weight:700; letter-spacing:0.10em; margin-bottom:6px;
                            background:linear-gradient(135deg,#ff5e6c,#ff8a5c);
                            -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                            GUIDE — COMMENT PAYER
                        </div>
                        <h2 style="font-size:20px; font-weight:800; color:#fff; margin-bottom:8px; line-height:1.3;">
                            Paiement simple<br>en 3 étapes
                        </h2>
                        <p style="font-size:13px; color:rgba(255,255,255,0.45); line-height:1.6;">
                            On utilise <strong style="color:#fff;">PayDunya</strong> — la solution de paiement mobile la plus sécurisée au Sénégal.
                        </p>
                    </div>

                    <div style="space-y:0; margin-bottom:20px;">
                        @foreach([
                        ['1', '🟠', 'Choisis ton moyen de paiement', 'Orange Money, Wave ou Free Money — entre ton numéro de téléphone'],
                        ['2', '📱', 'Confirme sur ton téléphone', 'Tu reçois une notification ou un code USSD à valider directement sur ton téléphone'],
                        ['3', '✅', 'Accès immédiat', 'Dès la confirmation, ton abonnement ou boost est activé automatiquement'],
                        ] as [$num, $icon, $title, $desc])
                        <div style="display:flex; gap:12px; align-items:flex-start; padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.05);">
                            <div style="width:28px; height:28px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; background:rgba(255,94,108,0.15); color:#ff5e6c;">{{ $num }}</div>
                            <div>
                                <div style="display:flex; align-items:center; gap:6px; margin-bottom:3px;">
                                    <span style="font-size:14px;">{{ $icon }}</span>
                                    <p style="font-size:12px; font-weight:700; color:rgba(255,255,255,0.80);">{{ $title }}</p>
                                </div>
                                <p style="font-size:11px; color:rgba(255,255,255,0.40); line-height:1.5;">{{ $desc }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div style="background:rgba(34,197,94,0.07); border:1px solid rgba(34,197,94,0.18); border-radius:12px; padding:10px 14px; margin-bottom:20px; display:flex; gap:10px; align-items:center;">
                        <span style="font-size:18px; flex-shrink:0;">🔒</span>
                        <p style="font-size:11px; color:rgba(255,255,255,0.45); line-height:1.5;">
                            Tes données de paiement ne transitent pas par Campus Crush — tout est géré par PayDunya de manière sécurisée.
                        </p>
                    </div>

                    <button onclick="closeFeatures(true)" style="
                        width:100%; padding:13px; border-radius:14px; border:none; cursor:pointer;
                        font-size:13px; font-weight:700; color:#fff;
                        background:linear-gradient(135deg,#ff5e6c,#ff8a5c);
                        box-shadow:0 6px 24px rgba(255,94,108,0.25);
                        font-family:inherit;
                        margin-bottom:10px;
                    ">J'ai compris, on y va ! 🚀</button>
                </div>

                {{-- ══ SLIDE 4 : Profil public partageable ══ --}}
                <div class="feat-slide" style="min-width:100%; padding:32px 24px 24px;">
                    <div style="text-align:center; margin-bottom:20px;">
                        <div style="font-size:48px; margin-bottom:12px;">🔗</div>
                        <div style="font-size:11px; font-weight:700; letter-spacing:0.10em; margin-bottom:6px;
                            background:linear-gradient(135deg,#3b82f6,#a855f7);
                            -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                            NOUVEAUTÉ — PROFIL PARTAGEABLE
                        </div>
                        <h2 style="font-size:20px; font-weight:800; color:#fff; margin-bottom:8px; line-height:1.3;">
                            Ton profil,<br>visible partout
                        </h2>
                        <p style="font-size:13px; color:rgba(255,255,255,0.45); line-height:1.6;">
                            Chaque profil a désormais une <span style="color:#fff; font-weight:600;">page publique unique</span>.
                            Partage ton lien sur WhatsApp ou Instagram pour attirer plus de matchs.
                        </p>
                    </div>

                    <div style="background:rgba(59,130,246,0.07); border:1px solid rgba(59,130,246,0.18); border-radius:16px; padding:14px 16px; margin-bottom:20px;">
                        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-size:16px; flex-shrink:0;">🌐</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">
                                Ton lien unique : <strong style="color:rgba(255,255,255,0.75);">campuscrush.sn/u/ton-prenom</strong>
                            </p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-size:16px; flex-shrink:0;">💬</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">
                                Partage-le sur WhatsApp, Instagram ou par SMS — même sans compte
                            </p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start;">
                            <span style="font-size:16px; flex-shrink:0;">💘</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">
                                Les visiteurs voient ton profil et peuvent s'inscrire pour te contacter
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('profile.show') }}" onclick="closeFeatures(true)" style="
                        display:block; width:100%; padding:13px; border-radius:14px;
                        text-align:center; text-decoration:none;
                        font-size:13px; font-weight:700; color:#fff;
                        background:linear-gradient(135deg,#3b82f6,#a855f7);
                        box-shadow:0 6px 24px rgba(59,130,246,0.25);
                        margin-bottom:10px;
                    ">Voir mon profil public →</a>
                </div>
                {{-- ══ SLIDE 5 : Crush Anonyme ══ --}}
                <div class="feat-slide" style="min-width:100%; padding:32px 24px 24px;">
                    <div style="text-align:center; margin-bottom:20px;">
                        <div style="font-size:48px; margin-bottom:12px;">👀</div>
                        <div style="font-size:11px; font-weight:700; letter-spacing:0.10em; margin-bottom:6px;
            background:linear-gradient(135deg,#ff5e6c,#ffc145);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                            NOUVEAUTÉ — CRUSH ANONYME
                        </div>
                        <h2 style="font-size:20px; font-weight:800; color:#fff; margin-bottom:8px; line-height:1.3;">
                            Dis-lui que tu l'aimes<br>sans te dévoiler
                        </h2>
                        <p style="font-size:13px; color:rgba(255,255,255,0.45); line-height:1.6;">
                            Envoie un crush anonyme à quelqu'un via son <span style="color:#fff; font-weight:600;">email ou numéro</span>.
                            La personne reçoit "Quelqu'un de ton université a un crush sur toi" <strong style="color:#fff;">sans savoir que c'est toi</strong> 👀
                        </p>
                    </div>

                    <div style="background:rgba(255,94,108,0.07); border:1px solid rgba(255,94,108,0.18); border-radius:16px; padding:14px 16px; margin-bottom:20px;">
                        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-size:16px; flex-shrink:0;">💌</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">Entre l'email ou le numéro de ton crush — il/elle reçoit un message mystérieux</p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-size:16px; flex-shrink:0;">🤫</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">Ton identité reste secrète — seul un indice (université) est donné</p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
                            <span style="font-size:16px; flex-shrink:0;">👁</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">La personne peut choisir de <strong style="color:#ff8a8a;">révéler</strong> qui l'a crushé</p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start;">
                            <span style="font-size:16px; flex-shrink:0;">🔥</span>
                            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;">Pas encore inscrit(e) ? Le crush l'attend à l'inscription !</p>
                        </div>
                    </div>

                    <a href="{{ route('crush.index') }}" onclick="closeFeatures(true)" style="
        display:block; width:100%; padding:13px; border-radius:14px;
        text-align:center; text-decoration:none;
        font-size:13px; font-weight:700; color:#fff;
        background:linear-gradient(135deg,#ff5e6c,#ffc145);
        box-shadow:0 6px 24px rgba(255,94,108,0.25);
        margin-bottom:10px;
    ">Envoyer un crush anonyme 💘</a>
                </div>

                {{-- ══ SLIDE : IA Campus Crush ══ --}}
<div class="feat-slide" style="min-width:100%; padding:32px 24px 24px;">
    <div style="text-align:center; margin-bottom:20px;">
        <div style="font-size:48px; margin-bottom:12px;">🤖</div>
        <div style="font-size:11px; font-weight:700; letter-spacing:0.10em; margin-bottom:6px;
            background:linear-gradient(135deg,#a855f7,#ff5e6c);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
            NOUVEAUTÉ — IA CAMPUS CRUSH
        </div>
        <h2 style="font-size:20px; font-weight:800; color:#fff; margin-bottom:8px; line-height:1.3;">
            4 assistants IA<br>pour t'aider à matcher
        </h2>
        <p style="font-size:13px; color:rgba(255,255,255,0.45); line-height:1.6;">
            Discute avec une IA, améliore ton profil avec le Coach,
            et entraîne-toi à draguer — le tout pour <strong style="color:#fff;">500 FCFA</strong> (paiement unique).
        </p>
    </div>
    <div style="background:rgba(168,85,247,0.07); border:1px solid rgba(168,85,247,0.18); border-radius:16px; padding:14px 16px; margin-bottom:20px;">
        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
            <span style="font-size:16px; flex-shrink:0;">👩🏾</span>
            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;"><strong style="color:#ff8a8a;">AI Match</strong> — Discute avec Aïda ou Moussa comme un vrai match</p>
        </div>
        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
            <span style="font-size:16px; flex-shrink:0;">🎯</span>
            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;"><strong style="color:#ffc145;">Coach Profil</strong> — Analyse et améliore ton profil</p>
        </div>
        <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:10px;">
            <span style="font-size:16px; flex-shrink:0;">💬</span>
            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;"><strong style="color:#a855f7;">Entraînement</strong> — Pratique tes conversations + feedback</p>
        </div>
        <div style="display:flex; gap:12px; align-items:flex-start;">
            <span style="font-size:16px; flex-shrink:0;">🤖</span>
            <p style="font-size:12px; color:rgba(255,255,255,0.55); line-height:1.5;"><strong style="color:#60a5fa;">Support 24/7</strong> — Aide gratuite sur l'app</p>
        </div>
    </div>
    <a href="/ai" onclick="closeFeatures(true)" style="
        display:block; width:100%; padding:13px; border-radius:14px;
        text-align:center; text-decoration:none;
        font-size:13px; font-weight:700; color:#fff;
        background:linear-gradient(135deg,#a855f7,#ff5e6c);
        box-shadow:0 6px 24px rgba(168,85,247,0.25);
        margin-bottom:10px;
    ">Découvrir l'IA Campus Crush →</a>
</div>
            </div>{{-- /slides-track --}}
        </div>

        {{-- Navigation bas --}}
        <div style="padding:0 24px 24px; display:flex; align-items:center; justify-content:space-between;">
            {{-- Dots --}}
            <div style="display:flex; gap:6px;" id="feat-dots">
                <div class="feat-dot active" onclick="goToSlide(0)" style="width:20px; height:6px; border-radius:3px; cursor:pointer; transition:all 0.3s; background:linear-gradient(135deg,#ff5e6c,#a855f7);"></div>
                <div class="feat-dot" onclick="goToSlide(1)" style="width:6px; height:6px; border-radius:3px; cursor:pointer; transition:all 0.3s; background:rgba(255,255,255,0.20);"></div>
                <div class="feat-dot" onclick="goToSlide(2)" style="width:6px; height:6px; border-radius:3px; cursor:pointer; transition:all 0.3s; background:rgba(255,255,255,0.20);"></div>
                <div class="feat-dot" onclick="goToSlide(3)" style="width:6px; height:6px; border-radius:3px; cursor:pointer; transition:all 0.3s; background:rgba(255,255,255,0.20);"></div>
                <div class="feat-dot" onclick="goToSlide(4)" style="width:6px; height:6px; border-radius:3px; cursor:pointer; transition:all 0.3s; background:rgba(255,255,255,0.20);"></div>
                <div class="feat-dot" onclick="goToSlide(5)" style="width:6px; height:6px; border-radius:3px; cursor:pointer; transition:all 0.3s; background:rgba(255,255,255,0.20);"></div>
            </div>

            {{-- Boutons nav --}}
            <div style="display:flex; gap:8px;">
                <button id="feat-prev" onclick="prevSlide()" style="
                    display:none; padding:8px 14px; border-radius:10px; border:none; cursor:pointer;
                    background:rgba(255,255,255,0.07); color:rgba(255,255,255,0.50);
                    font-size:12px; font-weight:600; font-family:inherit; transition:background 0.2s;
                " onmouseenter="this.style.background='rgba(255,255,255,0.12)'"
                    onmouseleave="this.style.background='rgba(255,255,255,0.07)'">← Retour</button>

                <button id="feat-next" onclick="nextSlide()" style="
                    padding:8px 18px; border-radius:10px; border:none; cursor:pointer;
                    background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.70);
                    font-size:12px; font-weight:600; font-family:inherit; transition:background 0.2s;
                " onmouseenter="this.style.background='rgba(255,255,255,0.14)'"
                    onmouseleave="this.style.background='rgba(255,255,255,0.08)'">Suivant →</button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes featIn {
        from {
            opacity: 0;
            transform: scale(0.88) translateY(20px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>

<script>
    (function() {
        const VERSION = @json($VERSION);
        const STORAGE_KEY = 'cc_features_v' + VERSION;

        // Afficher seulement si pas encore vu pour cette version
        if (!localStorage.getItem(STORAGE_KEY)) {
            setTimeout(() => {
                const overlay = document.getElementById('features-overlay');
                if (overlay) overlay.style.display = 'flex';
            }, 1200); // délai pour laisser la page charger
        }

        // Fermer en cliquant sur l'overlay (hors carte)
        const overlay = document.getElementById('features-overlay');
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) closeFeatures();
            });
        }
    })();

    let currentSlide = 0;
    const totalSlides = 6;

    function updateSlideUI() {
        const track = document.getElementById('slides-track');
        if (track) track.style.transform = `translateX(-${currentSlide * 100}%)`;

        // Dots
        document.querySelectorAll('.feat-dot').forEach((dot, i) => {
            if (i === currentSlide) {
                dot.style.width = '20px';
                dot.style.background = 'linear-gradient(135deg,#ff5e6c,#a855f7)';
            } else {
                dot.style.width = '6px';
                dot.style.background = 'rgba(255,255,255,0.20)';
            }
        });

        // Boutons
        const prev = document.getElementById('feat-prev');
        const next = document.getElementById('feat-next');
        if (prev) prev.style.display = currentSlide > 0 ? 'block' : 'none';
        if (next) {
            if (currentSlide === totalSlides - 1) {
                next.textContent = 'Terminer ✓';
                next.onclick = () => closeFeatures(true);
            } else {
                next.textContent = 'Suivant →';
                next.onclick = nextSlide;
            }
        }
    }

    function goToSlide(index) {
        currentSlide = index;
        updateSlideUI();
    }

    function nextSlide() {
        if (currentSlide < totalSlides - 1) {
            currentSlide++;
            updateSlideUI();
        }
    }

    function prevSlide() {
        if (currentSlide > 0) {
            currentSlide--;
            updateSlideUI();
        }
    }

    function closeFeatures(permanent = false) {
        const overlay = document.getElementById('features-overlay');
        const card = document.getElementById('features-card');

        if (card) {
            card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.92) translateY(10px)';
        }
        if (overlay) {
            overlay.style.transition = 'opacity 0.3s ease';
            overlay.style.opacity = '0';
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }

        // Mémoriser que l'utilisateur a vu cette version
        const VERSION = @json($VERSION);
        localStorage.setItem('cc_features_v' + VERSION, '1');
    }
</script>