@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
<div class="relative z-10 w-full max-w-md text-white px-5 py-8">

    <div class="flex items-center gap-4 mb-8 cc-fade-up">
        <a href="{{ route('profile.show') }}" class="p-2 rounded-xl cc-surface hover:bg-white/10 transition">
            <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold">Paramètres</h1>
    </div>

    <div class="space-y-2">

        {{-- Abonnement --}}
        <a href="{{ route('subscription.index') }}"
           class="cc-surface rounded-2xl p-4 flex items-center gap-4 hover:bg-white/[0.06] transition cc-slide-in">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,193,69,0.1);">
                <span>✨</span>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium">Abonnement</h3>
                <p class="text-[11px] text-white/30">
                    @php $sub = auth()->user()->subscription; @endphp
                    @if($sub && $sub->isActive())
                        @if($sub->isTrial())
                            Essai gratuit · {{ $sub->daysRemaining() }}j restants
                        @else
                            Actif · {{ $sub->daysRemaining() }}j restants
                        @endif
                    @else
                        Expiré - Renouveler
                    @endif
                </p>
            </div>
            <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
        </a>

        {{-- Modifier profil --}}
        <a href="{{ route('profile.edit') }}"
           class="cc-surface rounded-2xl p-4 flex items-center gap-4 hover:bg-white/[0.06] transition cc-slide-in" style="animation-delay: 0.06s">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,94,108,0.1);">
                <span>✏️</span>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium">Modifier le profil</h3>
                <p class="text-[11px] text-white/30">Photo, bio, centres d'intérêt</p>
            </div>
            <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="/terms" class="cc-surface rounded-2xl p-4 flex items-center gap-4 hover:bg-white/[0.06] transition">
    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(59,130,246,0.1);">📜</div>
    <div class="flex-1">
        <h3 class="text-sm font-medium">Conditions d'utilisation</h3>
        <p class="text-[11px] text-white/30">Règles de la plateforme</p>
    </div>
    <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
</a>

<a href="/privacy" class="cc-surface rounded-2xl p-4 flex items-center gap-4 hover:bg-white/[0.06] transition">
    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(168,85,247,0.1);">🔒</div>
    <div class="flex-1">
        <h3 class="text-sm font-medium">Politique de confidentialité</h3>
        <p class="text-[11px] text-white/30">Protection de vos données</p>
    </div>
    <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
</a>

<a href="/safety" class="cc-surface rounded-2xl p-4 flex items-center gap-4 hover:bg-white/[0.06] transition">
    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(34,197,94,0.1);">🛡️</div>
    <div class="flex-1">
        <h3 class="text-sm font-medium">Conseils de sécurité</h3>
        <p class="text-[11px] text-white/30">Protège-toi lors des rencontres</p>
    </div>
    <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
</a>

        {{-- Confidentialité --}}
        <div class="cc-surface rounded-2xl p-4 flex items-center gap-4 cc-slide-in" style="animation-delay: 0.12s">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(168,85,247,0.1);">
                <span>🔒</span>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium">Confidentialité</h3>
                <p class="text-[11px] text-white/30">Vos données restent privées</p>
            </div>
        </div>

        {{-- Admin (visible uniquement pour les admins) --}}
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}"
           class="cc-surface rounded-2xl p-4 flex items-center gap-4 hover:bg-white/[0.06] transition cc-slide-in" style="animation-delay: 0.18s">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,193,69,0.1);">
                <span>👑</span>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-[#ffc145]">Panel Admin</h3>
                <p class="text-[11px] text-white/30">Gérer l'application</p>
            </div>
            <svg class="w-4 h-4 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        @endif

        {{-- À propos --}}
        <div class="cc-surface rounded-2xl p-4 flex items-center gap-4 cc-slide-in" style="animation-delay: 0.18s">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(34,197,94,0.1);">
                <span>ℹ️</span>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium">À propos</h3>
                <p class="text-[11px] text-white/30">Campus Crush v2.0</p>
            </div>
        </div>

        {{-- Déconnexion --}}
        <form method="POST" action="{{ route('logout') }}" class="cc-slide-in" style="animation-delay: 0.24s">
            @csrf
            <button type="submit" class="w-full cc-surface rounded-2xl p-4 flex items-center gap-4 hover:bg-red-500/5 transition text-left">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(239,68,68,0.1);">
                    <span>🚪</span>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-red-400/80">Déconnexion</h3>
                    <p class="text-[11px] text-white/20">Se déconnecter</p>
                </div>
            </button>
        </form>
    </div>

    @include('components.bottom-nav')
</div>
</div>
@endsection
