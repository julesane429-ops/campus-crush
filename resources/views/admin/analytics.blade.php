@extends('admin.layout')

@section('admin-content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl md:text-2xl font-bold">Analytics</h1>
    <span class="text-xs text-white/30 bg-white/5 px-3 py-1.5 rounded-full">30 derniers jours</span>
</div>

{{-- ═══ KPI CARDS ═══ --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @php
    $kpiCards = [
        ['label' => 'Utilisateurs', 'value' => number_format($kpis['total_users']), 'sub' => '+' . $kpis['users_this_month'] . ' ce mois', 'icon' => '👥', 'color' => 'ff5e6c'],
        ['label' => 'Matchs total', 'value' => number_format($kpis['total_matches']), 'sub' => '+' . $kpis['matches_this_week'] . ' cette semaine', 'icon' => '💕', 'color' => 'a855f7'],
        ['label' => 'Revenus total', 'value' => number_format($kpis['total_revenue']) . ' F', 'sub' => number_format($kpis['revenue_this_month']) . ' F ce mois', 'icon' => '💰', 'color' => '22c55e'],
        ['label' => 'Taux de match', 'value' => $kpis['match_rate'] . '%', 'sub' => $kpis['paying_users'] . ' utilisateurs payants', 'icon' => '📊', 'color' => 'ffc145'],
        ['label' => 'Femmes', 'value' => number_format($kpis['women_count']), 'sub' => 'profils créés', 'icon' => '♀️', 'color' => 'ec4899'],
        ['label' => 'Hommes', 'value' => number_format($kpis['men_count']), 'sub' => 'profils créés', 'icon' => '♂️', 'color' => '3b82f6'],
        ['label' => 'Boosts actifs', 'value' => $kpis['boosted_now'], 'sub' => 'en ce moment', 'icon' => '🚀', 'color' => 'ffc145'],
        ['label' => 'Parrainages', 'value' => $kpis['referrals_total'], 'sub' => $kpis['referrals_rewarded'] . ' récompensés', 'icon' => '🎁', 'color' => 'a855f7'],
    ];
    @endphp

    @foreach($kpiCards as $card)
    <div class="admin-card rounded-2xl p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-lg">{{ $card['icon'] }}</span>
        </div>
        <p class="text-xl md:text-2xl font-bold cc-mono" style="color: #{{ $card['color'] }}">{{ $card['value'] }}</p>
        <p class="text-[10px] text-white/30 mt-1">{{ $card['label'] }}</p>
        <p class="text-[9px] text-white/20 mt-0.5">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- ═══ GRAPHIQUES ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- Inscriptions --}}
    <div class="admin-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold flex items-center gap-2">
                <span>👥</span> Inscriptions / jour
            </h2>
            <span class="text-xs text-white/25 cc-mono">+{{ array_sum($regData) }} total</span>
        </div>
        <div style="position:relative; height:180px;">
            <canvas id="chart-registrations"></canvas>
        </div>
    </div>

    {{-- Matchs --}}
    <div class="admin-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold flex items-center gap-2">
                <span>💕</span> Matchs / jour
            </h2>
            <span class="text-xs text-white/25 cc-mono">{{ array_sum($matchData) }} total</span>
        </div>
        <div style="position:relative; height:180px;">
            <canvas id="chart-matches"></canvas>
        </div>
    </div>

    {{-- Revenus --}}
    <div class="admin-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold flex items-center gap-2">
                <span>💰</span> Revenus / jour (FCFA)
            </h2>
            <span class="text-xs text-white/25 cc-mono">{{ number_format(array_sum($revenueData)) }} F total</span>
        </div>
        <div style="position:relative; height:180px;">
            <canvas id="chart-revenue"></canvas>
        </div>
    </div>

    {{-- Répartition UFR --}}
    <div class="admin-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold flex items-center gap-2">
                <span>🎓</span> Répartition par UFR
            </h2>
        </div>
        <div style="position:relative; height:180px;">
            <canvas id="chart-ufr"></canvas>
        </div>
    </div>

</div>

{{-- ═══ RÉPARTITION PAR UNIVERSITÉ ═══ --}}
<div class="admin-card rounded-2xl p-5 mb-4">
    <h2 class="text-sm font-semibold mb-4 flex items-center gap-2">
        <span>🏫</span> Répartition par université
    </h2>
    <div class="space-y-3">
        @php $maxUni = $byUniversity->max('total') ?: 1; @endphp
        @foreach($byUniversity as $uni)
        <div class="flex items-center gap-3">
            <span class="text-xs text-white/50 w-24 truncate flex-shrink-0">{{ $uni->university ?: 'N/A' }}</span>
            <div class="flex-1 bg-white/5 rounded-full h-2 overflow-hidden">
                <div class="h-full rounded-full" style="width: {{ round(($uni->total / $maxUni) * 100) }}%; background: linear-gradient(90deg, #ff5e6c, #a855f7);"></div>
            </div>
            <span class="text-xs text-white/40 cc-mono w-8 text-right flex-shrink-0">{{ $uni->total }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ═══ RATIO HOMMES / FEMMES ═══ --}}
<div class="admin-card rounded-2xl p-5">
    <h2 class="text-sm font-semibold mb-4 flex items-center gap-2">
        <span>⚖️</span> Ratio Hommes / Femmes
    </h2>
    @php
        $total = $kpis['men_count'] + $kpis['women_count'];
        $menPct = $total > 0 ? round(($kpis['men_count'] / $total) * 100) : 50;
        $womenPct = 100 - $menPct;
    @endphp
    <div class="flex items-center gap-3 mb-3">
        <span class="text-xs text-blue-400 font-semibold w-16">♂ {{ $menPct }}%</span>
        <div class="flex-1 h-3 rounded-full overflow-hidden flex">
            <div class="h-full bg-blue-500 transition-all" style="width: {{ $menPct }}%"></div>
            <div class="h-full bg-pink-500 transition-all" style="width: {{ $womenPct }}%"></div>
        </div>
        <span class="text-xs text-pink-400 font-semibold w-16 text-right">{{ $womenPct }}% ♀</span>
    </div>
    <div class="flex justify-between text-[10px] text-white/25">
        <span>{{ number_format($kpis['men_count']) }} hommes</span>
        <span>{{ number_format($kpis['women_count']) }} femmes</span>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const labels   = @json($labels);
const regData  = @json($regData);
const matchData= @json($matchData);
const revData  = @json($revenueData);
const ufrLabels= @json($byUfr->pluck('ufr'));
const ufrData  = @json($byUfr->pluck('total'));

// Defaults communs
Chart.defaults.color = 'rgba(255,255,255,0.35)';
Chart.defaults.font.family = 'Sora, sans-serif';
Chart.defaults.font.size   = 10;

const gridColor = 'rgba(255,255,255,0.05)';

function lineChart(id, label, data, color, fillColor) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label,
                data,
                borderColor: color,
                backgroundColor: fillColor,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 4,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { maxTicksLimit: 8 } },
                y: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } },
            }
        }
    });
}

lineChart('chart-registrations', 'Inscriptions', regData,
    '#ff5e6c', 'rgba(255,94,108,0.12)');

lineChart('chart-matches', 'Matchs', matchData,
    '#a855f7', 'rgba(168,85,247,0.12)');

lineChart('chart-revenue', 'Revenus (FCFA)', revData,
    '#22c55e', 'rgba(34,197,94,0.12)');

// Donut UFR
const ctxUfr = document.getElementById('chart-ufr');
if (ctxUfr) {
    new Chart(ctxUfr, {
        type: 'doughnut',
        data: {
            labels: ufrLabels,
            datasets: [{
                data: ufrData,
                backgroundColor: [
                    'rgba(255,94,108,0.8)',
                    'rgba(168,85,247,0.8)',
                    'rgba(255,193,69,0.8)',
                    'rgba(34,197,94,0.8)',
                    'rgba(59,130,246,0.8)',
                    'rgba(236,72,153,0.8)',
                ],
                borderColor: 'rgba(12,10,26,0.8)',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 10, padding: 10, font: { size: 10 } }
                }
            },
            cutout: '65%',
        }
    });
}
</script>

@endsection