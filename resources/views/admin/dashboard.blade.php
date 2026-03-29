@extends('admin.layout')

@section('admin-content')
<h1 class="text-2xl font-bold mb-8">Dashboard</h1>

{{-- Stats Grid --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @php
        $cards = [
            ['label' => 'Utilisateurs', 'value' => $stats['total_users'], 'icon' => '👥', 'color' => 'ff5e6c'],
            ['label' => 'Actifs aujourd\'hui', 'value' => $stats['active_today'], 'icon' => '🟢', 'color' => '22c55e'],
            ['label' => 'Matchs', 'value' => $stats['total_matches'], 'icon' => '💕', 'color' => 'a855f7'],
            ['label' => 'Messages', 'value' => $stats['total_messages'], 'icon' => '💬', 'color' => '3b82f6'],
            ['label' => 'Signalements', 'value' => $stats['pending_reports'], 'icon' => '⚠️', 'color' => 'ef4444'],
            ['label' => 'Bannis', 'value' => $stats['banned_users'], 'icon' => '🚫', 'color' => 'ef4444'],
            ['label' => 'Abonnements actifs', 'value' => $stats['active_subscriptions'], 'icon' => '✨', 'color' => 'ffc145'],
            ['label' => 'Revenus ce mois', 'value' => number_format($stats['revenue_month']) . ' F', 'icon' => '💰', 'color' => '22c55e'],
        ];
    @endphp

    @foreach($cards as $card)
    <div class="admin-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-2xl">{{ $card['icon'] }}</span>
            <span class="text-[10px] text-white/20 uppercase tracking-wider">{{ $card['label'] }}</span>
        </div>
        <p class="text-2xl font-bold cc-mono" style="color: #{{ $card['color'] }}">{{ $card['value'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Derniers inscrits --}}
    <div class="admin-card rounded-2xl p-6">
        <h2 class="font-semibold mb-4 flex items-center gap-2">
            <span>👥</span> Derniers inscrits
        </h2>
        <div class="space-y-3">
            @foreach($recentUsers as $u)
            <div class="flex items-center gap-3 text-sm">
                <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-xs overflow-hidden">
                    @if($u->profile?->photo)
                    <img src="{{ $u->profile->photo_url }}" class="w-full h-full object-cover">
                    @else
                    {{ substr($u->name, 0, 1) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="truncate font-medium text-white/80">{{ $u->name }}</p>
                    <p class="text-[10px] text-white/30">{{ $u->email }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($u->is_banned)
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-red-500/10 text-red-400">banni</span>
                    @endif
                    @if($u->subscription?->isActive())
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-green-500/10 text-green-400">
                        {{ $u->subscription->isTrial() ? 'essai' : 'payé' }}
                    </span>
                    @endif
                    <span class="text-[10px] text-white/20 cc-mono">{{ $u->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
        </div>
        <a href="{{ route('admin.users') }}" class="block mt-4 text-center text-xs text-[#ff5e6c] hover:underline">Voir tous →</a>
    </div>

    {{-- Signalements en attente --}}
    <div class="admin-card rounded-2xl p-6">
        <h2 class="font-semibold mb-4 flex items-center gap-2">
            <span>⚠️</span> Signalements récents
        </h2>
        @if($pendingReports->count() > 0)
        <div class="space-y-3">
            @foreach($pendingReports as $report)
            <div class="flex items-start gap-3 text-sm p-3 rounded-xl bg-white/[0.02]">
                <div class="flex-1">
                    <p class="text-white/70">
                        <span class="font-medium">{{ $report->reporter->name }}</span>
                        <span class="text-white/30">a signalé</span>
                        <span class="font-medium">{{ $report->reportedUser->name }}</span>
                    </p>
                    <p class="text-xs text-white/30 mt-1">{{ Str::limit($report->reason, 80) }}</p>
                    <p class="text-[10px] text-white/20 mt-1 cc-mono">{{ $report->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-center text-white/20 py-8 text-sm">Aucun signalement en attente 🎉</p>
        @endif
        <a href="{{ route('admin.reports') }}" class="block mt-4 text-center text-xs text-[#ff5e6c] hover:underline">Voir tous →</a>
    </div>
</div>

{{-- Revenu total --}}
<div class="mt-6 admin-card rounded-2xl p-6 text-center">
    <p class="text-white/30 text-sm mb-2">Revenu total</p>
    <p class="text-4xl font-bold cc-mono cc-gradient-text">{{ number_format($stats['total_revenue']) }} FCFA</p>
</div>
@endsection
