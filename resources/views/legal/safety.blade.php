@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
<div class="relative z-10 w-full max-w-2xl text-white px-5 py-8">

    <div class="flex items-center gap-4 mb-8 cc-fade-up">
        <a href="{{ url()->previous() }}" class="p-2 rounded-xl cc-surface hover:bg-white/10 transition">
            <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold">Conseils de sécurité</h1>
    </div>

    <div class="cc-surface-raised rounded-3xl p-6 sm:p-8 cc-fade-up" style="animation-delay:0.1s">
        <div class="text-center mb-8">
            <span class="text-5xl">🛡️</span>
            <h2 class="text-xl font-bold mt-4 cc-gradient-text">Ta sécurité, notre priorité</h2>
            <p class="text-white/40 text-sm mt-2">Lis ces conseils avant de rencontrer quelqu'un</p>
        </div>

        <div class="space-y-6 text-sm text-white/60 leading-relaxed">

            <div class="p-4 rounded-2xl bg-green-500/5 border border-green-500/10">
                <h3 class="font-bold text-green-400 mb-2">✅ AVANT la rencontre</h3>
                <p>• Discute suffisamment par message avant de te déplacer</p>
                <p>• Fais un appel vidéo pour vérifier que la personne est bien celle sur les photos</p>
                <p>• Vérifie le profil : est-il complet ? Les infos sont-elles cohérentes ?</p>
                <p>• Méfie-toi si la personne évite les appels vidéo ou les questions simples</p>
            </div>

            <div class="p-4 rounded-2xl bg-blue-500/5 border border-blue-500/10">
                <h3 class="font-bold text-blue-400 mb-2">📍 PENDANT la rencontre</h3>
                <p>• Rencontre-toi dans un lieu PUBLIC (café, restaurant, campus)</p>
                <p>• Préviens un(e) ami(e) ou un proche : dis-lui où tu vas et avec qui</p>
                <p>• Partage ta localisation en temps réel avec un ami (WhatsApp, Google Maps)</p>
                <p>• Utilise ton propre moyen de transport (ne monte pas dans sa voiture la première fois)</p>
                <p>• Garde ton téléphone chargé</p>
            </div>

            <div class="p-4 rounded-2xl bg-red-500/5 border border-red-500/10">
                <h3 class="font-bold text-red-400 mb-2">🚫 NE FAIS JAMAIS</h3>
                <p>• Ne partage jamais tes informations bancaires, mots de passe ou codes de paiement</p>
                <p>• N'envoie jamais d'argent à quelqu'un que tu n'as pas rencontré en personne</p>
                <p>• Ne partage pas ton adresse exacte avant d'avoir établi une confiance</p>
                <p>• N'envoie pas de photos intimes — elles pourraient être utilisées contre toi</p>
                <p>• Ne te rends pas chez la personne pour un premier rendez-vous</p>
            </div>

            <div class="p-4 rounded-2xl bg-yellow-500/5 border border-yellow-500/10">
                <h3 class="font-bold text-yellow-400 mb-2">⚠️ SIGNAUX D'ALERTE</h3>
                <p>Signale et bloque immédiatement si la personne :</p>
                <p class="mt-1">• Te demande de l'argent ou un transfert Wave/OM</p>
                <p>• Te menace ou te fait du chantage</p>
                <p>• Refuse de faire un appel vidéo ou de se montrer</p>
                <p>• Te pousse à quitter Campus Crush pour une autre plateforme très rapidement</p>
                <p>• Te met mal à l'aise de quelque manière que ce soit</p>
            </div>

            <div class="p-4 rounded-2xl bg-[#ff5e6c]/5 border border-[#ff5e6c]/10">
                <h3 class="font-bold text-[#ff5e6c] mb-2">🆘 EN CAS DE PROBLÈME</h3>
                <p>• <span class="text-white/70">Signale l'utilisateur</span> dans l'app (menu ⋮ dans le chat)</p>
                <p>• <span class="text-white/70">Bloque la personne</span> pour qu'elle ne puisse plus te contacter</p>
                <p>• <span class="text-white/70">Contacte la police</span> : Commissariat le plus proche ou appel au 17</p>
                <p>• <span class="text-white/70">Contacte-nous</span> : contact@campuscrush.sn</p>
                <p class="mt-2">N'hésite jamais à signaler un comportement suspect. C'est anonyme et ça protège toute la communauté.</p>
            </div>
        </div>
    </div>

    <div class="mt-6 text-center cc-fade-up" style="animation-delay:0.2s">
        <a href="{{ url()->previous() }}" class="text-xs text-white/30 hover:text-white/60 transition">← Retour</a>
    </div>
</div>
</div>
@endsection
