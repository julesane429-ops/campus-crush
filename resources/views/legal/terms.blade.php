@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
<div class="relative z-10 w-full max-w-2xl text-white px-5 py-8">

    <div class="flex items-center gap-4 mb-8 cc-fade-up">
        <a href="{{ url()->previous() }}" class="p-2 rounded-xl cc-surface hover:bg-white/10 transition">
            <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold">Conditions d'utilisation</h1>
    </div>

    <div class="cc-surface-raised rounded-3xl p-6 sm:p-8 cc-fade-up" style="animation-delay:0.1s">
        <p class="text-[11px] text-white/30 mb-6">Dernière mise à jour : 28 mars 2026</p>

        <div class="space-y-6 text-sm text-white/60 leading-relaxed">

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">1. Acceptation des conditions</h2>
                <p>En créant un compte sur Campus Crush, vous acceptez les présentes conditions d'utilisation dans leur intégralité. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser l'application.</p>
                <p class="mt-2">Campus Crush est une application de rencontres destinée exclusivement aux étudiants des universités sénégalaises.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">2. Éligibilité</h2>
                <p>Pour utiliser Campus Crush, vous devez :</p>
                <p class="mt-2">• Être âgé(e) d'au moins 18 ans</p>
                <p>• Être inscrit(e) dans une université ou un établissement d'enseignement supérieur au Sénégal</p>
                <p>• Créer un seul compte par personne</p>
                <p>• Fournir des informations exactes et à jour</p>
                <p class="mt-2">Campus Crush se réserve le droit de vérifier l'éligibilité des utilisateurs et de suspendre tout compte ne respectant pas ces critères.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">3. Compte utilisateur</h2>
                <p>Vous êtes responsable de la confidentialité de vos identifiants de connexion. Toute activité effectuée depuis votre compte est de votre responsabilité.</p>
                <p class="mt-2">Vous vous engagez à :</p>
                <p class="mt-1">• Ne pas partager votre compte avec un tiers</p>
                <p>• Nous informer immédiatement de tout accès non autorisé</p>
                <p>• Ne pas créer de faux profil ni usurper l'identité d'autrui</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">4. Comportement des utilisateurs</h2>
                <p>En utilisant Campus Crush, vous vous engagez à ne pas :</p>
                <p class="mt-2">• Harceler, menacer, intimider ou faire du chantage à d'autres utilisateurs</p>
                <p>• Publier du contenu à caractère sexuellement explicite, violent, haineux ou discriminatoire</p>
                <p>• Envoyer des messages non sollicités à caractère commercial (spam)</p>
                <p>• Utiliser l'application à des fins frauduleuses ou illégales</p>
                <p>• Collecter les données personnelles d'autres utilisateurs</p>
                <p>• Diffuser de fausses informations ou des contenus trompeurs</p>
                <p>• Solliciter de l'argent ou des biens matériels auprès d'autres utilisateurs</p>
                <p>• Publier des photos qui ne sont pas les vôtres</p>
                <p class="mt-2">Tout manquement à ces règles peut entraîner la suspension ou la suppression définitive de votre compte, sans préavis ni remboursement.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">5. Contenu utilisateur</h2>
                <p>Vous conservez la propriété de tout contenu que vous publiez sur Campus Crush (photos, textes, messages). Cependant, en publiant du contenu, vous accordez à Campus Crush une licence non exclusive, gratuite et mondiale pour afficher ce contenu dans le cadre du fonctionnement de l'application.</p>
                <p class="mt-2">Campus Crush se réserve le droit de supprimer tout contenu jugé inapproprié, offensant ou contraire aux présentes conditions, sans notification préalable.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">6. Abonnement et paiement</h2>
                <p>Campus Crush propose un essai gratuit de 30 jours pour tout nouvel utilisateur. À l'issue de cette période, un abonnement mensuel de 1 000 FCFA est requis pour continuer à utiliser les fonctionnalités principales (swipe, matchs, messages).</p>
                <p class="mt-2">Le paiement s'effectue via Orange Money, Wave ou Free Money. L'abonnement est valable pour une période de 30 jours à compter du paiement.</p>
                <p class="mt-2">Les paiements effectués ne sont pas remboursables, sauf dans les cas prévus par la loi sénégalaise applicable.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">7. Sécurité et signalements</h2>
                <p>Campus Crush met en place des mesures pour assurer la sécurité de ses utilisateurs :</p>
                <p class="mt-2">• Système de signalement pour les comportements inappropriés</p>
                <p>• Possibilité de bloquer un utilisateur</p>
                <p>• Modération des contenus signalés par l'équipe d'administration</p>
                <p class="mt-2">Si vous êtes victime de harcèlement, de menaces ou de tout comportement illégal, nous vous encourageons à signaler l'utilisateur via l'application ET à contacter les autorités compétentes.</p>
                <p class="mt-2 text-yellow-400/70">Campus Crush ne peut être tenu responsable des agissements des utilisateurs en dehors de la plateforme. Nous recommandons la prudence lors des rencontres en personne.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">8. Limitation de responsabilité</h2>
                <p>Campus Crush est fourni « en l'état ». Nous ne garantissons pas :</p>
                <p class="mt-2">• La véracité des informations fournies par les autres utilisateurs</p>
                <p>• La disponibilité ininterrompue du service</p>
                <p>• La compatibilité ou la réussite des matchs</p>
                <p class="mt-2">Campus Crush décline toute responsabilité pour les dommages directs ou indirects résultant de l'utilisation de l'application, y compris mais sans s'y limiter : les rencontres physiques entre utilisateurs, les pertes financières liées à des arnaques entre utilisateurs, et les dommages émotionnels.</p>
                <p class="mt-2">Vous utilisez l'application à vos propres risques et vous êtes seul(e) responsable de vos interactions avec les autres utilisateurs.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">9. Propriété intellectuelle</h2>
                <p>Le nom « Campus Crush », le logo, le design et le code source de l'application sont la propriété exclusive de Campus Crush. Toute reproduction, distribution ou utilisation non autorisée est interdite.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">10. Suppression de compte</h2>
                <p>Vous pouvez supprimer votre compte à tout moment depuis les paramètres de l'application. La suppression entraîne :</p>
                <p class="mt-2">• La suppression de votre profil et de vos photos</p>
                <p>• La suppression de vos matchs et conversations</p>
                <p>• L'impossibilité de récupérer vos données</p>
                <p class="mt-2">Campus Crush peut également supprimer votre compte en cas de violation des présentes conditions.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">11. Modifications des conditions</h2>
                <p>Campus Crush se réserve le droit de modifier les présentes conditions à tout moment. Les utilisateurs seront informés des modifications via l'application. L'utilisation continue de l'application après modification vaut acceptation des nouvelles conditions.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">12. Conseils de sécurité</h2>
                <div class="p-4 rounded-2xl bg-[#ff5e6c]/5 border border-[#ff5e6c]/10">
                    <p class="text-white/70">Pour votre sécurité lors de rencontres en personne :</p>
                    <p class="mt-2">• Prévenez toujours un ami ou un proche de vos rendez-vous</p>
                    <p>• Rencontrez-vous dans un lieu public pour la première fois</p>
                    <p>• Ne partagez jamais vos informations bancaires ou mots de passe</p>
                    <p>• Méfiez-vous des demandes d'argent</p>
                    <p>• Faites confiance à votre instinct : si quelque chose vous semble suspect, signalez-le</p>
                </div>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">13. Droit applicable</h2>
                <p>Les présentes conditions sont régies par le droit sénégalais. Tout litige sera soumis à la compétence des tribunaux de Saint-Louis, Sénégal.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white/90 mb-3">14. Contact</h2>
                <p>Pour toute question relative aux présentes conditions :</p>
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
