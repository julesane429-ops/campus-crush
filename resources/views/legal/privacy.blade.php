@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
<div class="relative z-10 w-full max-w-2xl text-white px-5 py-8">

    <div class="flex items-center gap-4 mb-8 cc-fade-up">
        <a href="{{ url()->previous() }}" class="p-2 rounded-xl cc-surface hover:bg-white/10 transition">
            <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold">Politique de confidentialité</h1>
    </div>

    <div class="cc-surface-raised rounded-3xl p-6 sm:p-8 cc-fade-up" style="animation-delay:0.1s">
        <p class="text-[11px] text-white/30 mb-6">Dernière mise à jour : 28 mars 2026</p>

        <div class="space-y-6 text-sm text-white/60 leading-relaxed">

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">1. Introduction</h2>
                <p>Campus Crush s'engage à protéger la vie privée de ses utilisateurs. La présente politique de confidentialité décrit les données personnelles que nous collectons, comment nous les utilisons et les mesures que nous prenons pour les protéger.</p>
                <p class="mt-2">En utilisant Campus Crush, vous consentez à la collecte et au traitement de vos données tel que décrit ci-dessous.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">2. Données collectées</h2>
                <p class="font-medium text-white/70 mt-2">Données fournies par vous :</p>
                <p class="mt-1">• Nom et prénom</p>
                <p>• Adresse email</p>
                <p>• Âge et genre</p>
                <p>• Université, UFR, niveau et promotion</p>
                <p>• Photo de profil</p>
                <p>• Biographie et centres d'intérêt</p>
                <p>• Numéro de téléphone (pour le paiement uniquement)</p>

                <p class="font-medium text-white/70 mt-4">Données collectées automatiquement :</p>
                <p class="mt-1">• Activité sur l'application (swipes, matchs, messages)</p>
                <p>• Date et heure de connexion</p>
                <p>• Données techniques (type d'appareil, navigateur, adresse IP)</p>
                <p>• Données d'abonnement push (si activées)</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">3. Utilisation des données</h2>
                <p>Vos données sont utilisées pour :</p>
                <p class="mt-2">• Créer et gérer votre compte</p>
                <p>• Vous proposer des profils compatibles</p>
                <p>• Faciliter les matchs et les conversations</p>
                <p>• Traiter les paiements d'abonnement</p>
                <p>• Envoyer des notifications (matchs, messages)</p>
                <p>• Assurer la sécurité de la plateforme (modération, signalements)</p>
                <p>• Améliorer nos services et corriger les bugs</p>
                <p class="mt-2 text-green-400/70">Nous ne vendons JAMAIS vos données personnelles à des tiers.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">4. Partage des données</h2>
                <p>Vos données peuvent être partagées avec :</p>
                <p class="mt-2">• <span class="text-white/70">Les autres utilisateurs</span> : votre profil public (nom, âge, photo, bio, université) est visible par les autres utilisateurs. Vos messages ne sont visibles que par votre correspondant.</p>
                <p class="mt-2">• <span class="text-white/70">Prestataires de paiement</span> : PayDunya traite vos informations de paiement (numéro de téléphone) pour le traitement des abonnements. Nous ne stockons pas vos codes secrets de paiement mobile.</p>
                <p class="mt-2">• <span class="text-white/70">Hébergement</span> : nos données sont hébergées sur Render.com (serveur) et Supabase (base de données), conformément à leurs politiques de sécurité respectives.</p>
                <p class="mt-2">• <span class="text-white/70">Autorités</span> : nous pouvons divulguer vos données si la loi l'exige ou pour protéger la sécurité de nos utilisateurs.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">5. Sécurité des données</h2>
                <p>Nous mettons en place les mesures suivantes pour protéger vos données :</p>
                <p class="mt-2">• Mots de passe chiffrés (hachage bcrypt)</p>
                <p>• Connexion HTTPS chiffrée (SSL/TLS)</p>
                <p>• Protection contre les attaques XSS et CSRF</p>
                <p>• Accès à la base de données restreint et sécurisé</p>
                <p class="mt-2">Malgré ces mesures, aucun système n'est infaillible. Nous vous recommandons d'utiliser un mot de passe fort et unique pour votre compte Campus Crush.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">6. Conservation des données</h2>
                <p>Vos données sont conservées tant que votre compte est actif. En cas de suppression de compte :</p>
                <p class="mt-2">• Votre profil, photos, matchs et messages sont supprimés immédiatement</p>
                <p>• Les données de paiement sont conservées 12 mois pour des raisons comptables</p>
                <p>• Les signalements vous concernant peuvent être conservés pour la sécurité de la plateforme</p>
                <p class="mt-2">Les comptes inactifs depuis plus de 12 mois pourront être supprimés automatiquement.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">7. Vos droits</h2>
                <p>Conformément à la loi sénégalaise sur la protection des données personnelles (loi n° 2008-12 du 25 janvier 2008), vous disposez des droits suivants :</p>
                <p class="mt-2">• <span class="text-white/70">Droit d'accès</span> : vous pouvez consulter toutes les données que nous détenons sur vous</p>
                <p>• <span class="text-white/70">Droit de rectification</span> : vous pouvez modifier vos informations depuis votre profil</p>
                <p>• <span class="text-white/70">Droit de suppression</span> : vous pouvez supprimer votre compte à tout moment</p>
                <p>• <span class="text-white/70">Droit d'opposition</span> : vous pouvez vous opposer au traitement de vos données pour certaines finalités</p>
                <p class="mt-2">Pour exercer vos droits, contactez-nous à : contact@campuscrush.sn</p>
                <p class="mt-2">Vous pouvez également saisir la Commission des Données Personnelles (CDP) du Sénégal : <span class="text-white/70">www.cdp.sn</span></p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">8. Cookies et stockage local</h2>
                <p>Campus Crush utilise :</p>
                <p class="mt-2">• <span class="text-white/70">Cookies de session</span> : pour maintenir votre connexion (nécessaire au fonctionnement)</p>
                <p>• <span class="text-white/70">Stockage local</span> : pour mémoriser vos préférences (onboarding vu, thème)</p>
                <p class="mt-2">Nous n'utilisons pas de cookies publicitaires ni de trackers tiers.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">9. Mineurs</h2>
                <p>Campus Crush est strictement interdit aux personnes de moins de 18 ans. Si nous découvrons qu'un mineur a créé un compte, celui-ci sera immédiatement supprimé.</p>
                <p class="mt-2">Si vous êtes parent et pensez que votre enfant mineur utilise Campus Crush, contactez-nous immédiatement à contact@campuscrush.sn.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">10. Notifications push</h2>
                <p>Si vous activez les notifications push, nous stockons un identifiant technique (endpoint) lié à votre appareil. Cet identifiant ne contient aucune donnée personnelle et sert uniquement à vous envoyer des alertes (matchs, messages).</p>
                <p class="mt-2">Vous pouvez désactiver les notifications à tout moment depuis les paramètres de l'application.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">11. Modifications</h2>
                <p>Cette politique de confidentialité peut être mise à jour. La date de dernière modification est indiquée en haut de cette page. Nous vous encourageons à la consulter régulièrement.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">12. Contact</h2>
                <p>Pour toute question relative à la protection de vos données :</p>
                <p class="mt-2">📧 Email : contact@campuscrush.sn</p>
                <p>📍 Sénégal</p>
            </section>

        </div>
    </div>

    <div class="mt-6 text-center cc-fade-up" style="animation-delay:0.2s">
        <a href="{{ url()->previous() }}" class="text-xs text-white/30 hover:text-white/60 transition">← Retour</a>
    </div>
</div>
</div>
@endsection
