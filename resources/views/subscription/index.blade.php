@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
    <div class="relative z-10 w-full max-w-md text-white px-5 py-8">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-8 cc-fade-up">
            <a href="{{ route('profile.show') }}" class="p-2 rounded-xl cc-surface hover:bg-white/10 transition">
                <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl font-bold">Abonnement</h1>
        </div>

        {{-- Mode indicator --}}
        @if(!$paydenyaConfigured)
        <div class="mb-4 px-4 py-2 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 text-xs text-center cc-fade-up">
            🧪 Mode simulation — Les paiements sont validés automatiquement
        </div>
        @endif

        {{-- Current Status --}}
        <div class="cc-surface-raised rounded-3xl p-6 mb-6 cc-fade-up" style="animation-delay: 0.1s">
            <div class="text-center">
                @if($subscription->isActive())
                @if($subscription->isTrial())
                <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-4" style="background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2);">
                    <span class="text-3xl">🎁</span>
                </div>
                <h2 class="text-xl font-bold mb-1">Période d'essai gratuite</h2>
                @php $daysLeft = $subscription->daysRemaining(); @endphp
                <p class="text-white/40 text-sm mb-4">
                    Il vous reste <span class="font-bold text-blue-400 cc-mono">{{ $daysLeft }}</span> jours
                </p>
                @php
                $totalDays = (int) $subscription->starts_at->diffInDays($subscription->trial_ends_at);
                if ($totalDays < 1) $totalDays=30;
                    $progress=max(0, min(100, 100 - ($daysLeft / $totalDays * 100)));
                    @endphp
                    <div class="w-full bg-white/5 rounded-full h-2 mb-2">
                    <?php $progressStyle = "width: {$progress}%; background: linear-gradient(90deg, #3b82f6, #ff5e6c);"; ?>
                    <div class="h-2 rounded-full" style="<?php echo $progressStyle; ?>"></div>
            </div>
            @if($totalDays > 30)
            <p class="text-[10px] text-green-400/50 mt-1">🎁 +{{ $totalDays - 30 }} jours bonus (parrainage/streak)</p>
            @endif
            @else
            <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-4" style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2);">
                <span class="text-3xl">✨</span>
            </div>
            <h2 class="text-xl font-bold mb-1 cc-gradient-text">Abonnement actif</h2>
            @php $daysLeft = $subscription->daysRemaining(); @endphp
            <p class="text-white/40 text-sm mb-2">
                <span class="font-bold text-green-400 cc-mono">{{ $daysLeft }}</span> jours restants
            </p>
            <p class="text-[10px] text-white/20">Expire le {{ $subscription->ends_at->format('d/m/Y') }}</p>
            @endif
            @else
            <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-4" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);">
                <span class="text-3xl">⏰</span>
            </div>
            <h2 class="text-xl font-bold mb-1 text-red-400">Abonnement expiré</h2>
            <p class="text-white/40 text-sm">Renouvelez pour continuer à matcher</p>
            @endif
        </div>
    </div>

    {{-- Plan --}}
    <div class="cc-surface rounded-3xl p-6 mb-6 cc-fade-up" style="animation-delay: 0.2s">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold">Campus Crush Premium</h3>
            <span class="cc-mono text-2xl font-bold cc-gradient-text">1 000 F</span>
        </div>
        <p class="text-xs text-white/30 mb-4">par mois · FCFA</p>

        <div class="space-y-3 text-sm">
            @foreach([
            'Swiper sans limite',
            'Voir qui t\'a liké',
            'Messages illimités',
            'Filtres avancés (université, UFR)',
            'Pas de publicités',
            ] as $feature)
            <div class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" style="background: rgba(34,197,94,0.1);">✓</span>
                <span class="text-white/60">{{ $feature }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Payment Form --}}
    @php $showPayForm = !$subscription->isActive() || $subscription->isTrial() || $subscription->daysRemaining() <= 5; @endphp
        @if($showPayForm)
        <div class="cc-surface-raised rounded-3xl p-6 mb-6 cc-fade-up" style="animation-delay: 0.3s">
        <h3 class="font-semibold mb-4">
            {{ $subscription->isActive() ? 'Renouveler' : 'S\'abonner' }}
        </h3>

        <form method="POST" action="{{ route('subscription.pay') }}">
            @csrf

            {{-- Payment Method --}}
            <p class="text-xs text-white/30 uppercase tracking-wider mb-3">Moyen de paiement</p>
            <div class="grid grid-cols-3 gap-3 mb-5">
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="orange_money" class="hidden peer" required>
                    <div class="peer-checked:border-[#ff5e6c] peer-checked:bg-[#ff5e6c]/5 border border-white/10 rounded-2xl p-4 text-center transition hover:bg-white/5">
                        <span class="text-2xl block mb-1">🟠</span>
                        <span class="text-[10px] text-white/50">Orange Money</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="wave" class="hidden peer">
                    <div class="peer-checked:border-[#ff5e6c] peer-checked:bg-[#ff5e6c]/5 border border-white/10 rounded-2xl p-4 text-center transition hover:bg-white/5">
                        <span class="text-2xl block mb-1">🔵</span>
                        <span class="text-[10px] text-white/50">Wave</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="free_money" class="hidden peer">
                    <div class="peer-checked:border-[#ff5e6c] peer-checked:bg-[#ff5e6c]/5 border border-white/10 rounded-2xl p-4 text-center transition hover:bg-white/5">
                        <span class="text-2xl block mb-1">🟢</span>
                        <span class="text-[10px] text-white/50">Free Money</span>
                    </div>
                </label>
            </div>

            {{-- Phone --}}
            <p class="text-xs text-white/30 uppercase tracking-wider mb-2">Numéro de téléphone</p>
            <input type="tel" name="phone_number" placeholder="77 123 45 67" required
                pattern="(77|78|76|70|75)[0-9]{7}"
                class="w-full px-5 py-4 rounded-2xl bg-white/[0.04] border border-white/10 text-white placeholder-white/30 outline-none focus:border-[#ff5e6c] transition mb-5">

            @error('phone_number')
            <p class="text-red-400 text-xs mb-3">{{ $message }}</p>
            @enderror

            {{-- Guide paiement --}}
            <div class="rounded-2xl p-4 mb-5" style="background: rgba(59,130,246,0.06); border: 1px solid rgba(59,130,246,0.15);">
                <p class="text-xs font-semibold text-blue-400 mb-3">📋 Comment payer en 3 étapes :</p>
                <div class="space-y-2.5">
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold" style="background:rgba(59,130,246,0.15); color:#60a5fa;">1</span>
                        <p class="text-[11px] text-white/45 leading-relaxed">Choisis Orange Money, Wave ou Free Money ci-dessus et entre ton numéro</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold" style="background:rgba(59,130,246,0.15); color:#60a5fa;">2</span>
                        <p class="text-[11px] text-white/45 leading-relaxed">Tu seras redirigé vers PayDunya — <strong class="text-white/60">clique sur le logo de ton opérateur</strong> puis remplis ton nom, email et numéro</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold" style="background:rgba(59,130,246,0.15); color:#60a5fa;">3</span>
                        <p class="text-[11px] text-white/45 leading-relaxed"><strong class="text-white/60">Confirme sur ton téléphone</strong> — tu recevras un pop-up ou un code USSD à valider. L'abonnement s'active automatiquement ✅</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-4 rounded-2xl font-semibold text-white text-base transition hover:-translate-y-0.5 active:scale-[0.98]"
                style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                @if($paydenyaConfigured)
                Payer 1 000 FCFA via PayDunya
                @else
                Payer 1 000 FCFA (simulation)
                @endif
            </button>
        </form>
</div>
@endif

{{-- Payment History --}}
@if($payments->count() > 0)
<div class="cc-surface rounded-3xl p-6 cc-fade-up" style="animation-delay: 0.4s">
    <h3 class="font-semibold mb-4 text-sm">Historique des paiements</h3>
    <div class="space-y-3">
        @foreach($payments as $p)
        <div class="flex items-center justify-between text-sm py-2 border-b border-white/5 last:border-0">
            <div>
                <p class="text-white/60 text-xs">
                    @if($p->payment_method === 'orange_money') 🟠 Orange Money
                    @elseif($p->payment_method === 'wave') 🔵 Wave
                    @else 🟢 Free Money
                    @endif
                </p>
                <p class="text-[10px] text-white/20 cc-mono">{{ $p->created_at->format('d/m/Y') }}</p>
            </div>
            <div class="text-right">
                <p class="cc-mono font-bold text-white/70">{{ number_format($p->amount) }} F</p>
                <span class="text-[10px] {{ $p->status === 'completed' ? 'text-green-400' : ($p->status === 'pending' ? 'text-yellow-400' : 'text-red-400') }}">
                    {{ $p->status === 'completed' ? '✓ Payé' : ($p->status === 'pending' ? '⏳ En attente' : '✗ Échoué') }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@include('components.bottom-nav')
 @auth
    @include('components.ai-chat-fab')
    @endauth
</div>
</div>
@endsection