@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex items-center justify-center">
<div class="relative z-10 w-full max-w-sm text-white px-5 py-8 text-center">

    <div class="cc-surface-raised rounded-3xl p-8 cc-fade-up">
        <div class="text-5xl mb-4">🎉</div>
        <h1 class="text-2xl font-bold cc-gradient-text mb-2">Paiement confirmé !</h1>
        <p class="text-white/40 text-sm mb-6">Votre abonnement est actif pour 30 jours</p>

        @if($lastPayment)
        <div class="cc-surface rounded-2xl p-4 mb-6 text-left text-sm">
            <div class="flex justify-between mb-2">
                <span class="text-white/30">Montant</span>
                <span class="font-bold cc-mono">{{ number_format($lastPayment->amount) }} FCFA</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-white/30">Méthode</span>
                <span class="text-white/60">
                    @if($lastPayment->payment_method === 'orange_money') 🟠 Orange Money
                    @elseif($lastPayment->payment_method === 'wave') 🔵 Wave
                    @else 🟢 Free Money
                    @endif
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-white/30">Référence</span>
                <span class="text-white/40 cc-mono text-xs">{{ $lastPayment->transaction_id }}</span>
            </div>
        </div>
        @endif

        <a href="{{ route('swipe') }}" class="block w-full py-4 rounded-2xl font-semibold text-white transition hover:-translate-y-0.5"
           style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
            Commencer à matcher 🔥
        </a>
    </div>
</div>
</div>
@endsection
