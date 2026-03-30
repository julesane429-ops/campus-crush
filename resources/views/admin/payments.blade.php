@extends('admin.layout')

@section('admin-content')
<div class="flex items-center justify-between mb-5">
    <h1 class="text-xl md:text-2xl font-bold">Paiements</h1>
    <span class="text-xs text-white/25 cc-mono">{{ $payments->total() }} total</span>
</div>

{{-- Mobile: Card layout --}}
<div class="md:hidden space-y-3">
    @forelse($payments as $p)
    <div class="admin-card rounded-2xl p-4">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-xs flex-shrink-0">
                    {{ substr($p->user->name ?? '?', 0, 1) }}
                </div>
                <div>
                    <p class="text-xs font-medium text-white/70">{{ $p->user->name ?? 'Supprimé' }}</p>
                    <p class="text-[10px] text-white/25">{{ $p->user->email ?? '' }}</p>
                </div>
            </div>
            <span class="text-sm font-bold cc-mono {{ $p->status === 'completed' ? 'text-green-400' : ($p->status === 'failed' ? 'text-red-400' : 'text-yellow-400') }}">
                {{ number_format($p->amount) }} F
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $p->status === 'completed' ? 'bg-green-500/10 text-green-400' : ($p->status === 'failed' ? 'bg-red-500/10 text-red-400' : 'bg-yellow-500/10 text-yellow-400') }}">
                {{ $p->status === 'completed' ? '✅ Payé' : ($p->status === 'failed' ? '❌ Échoué' : '⏳ En attente') }}
            </span>
            <span class="text-[10px] text-white/20 px-2 py-0.5 rounded-full bg-white/5">{{ str_replace('_', ' ', $p->payment_method) }}</span>
            <span class="text-[9px] text-white/15 cc-mono">{{ $p->created_at->format('d/m H:i') }}</span>
        </div>
    </div>
    @empty
    <div class="text-center py-16">
        <span class="text-4xl">💰</span>
        <p class="text-sm text-white/25 mt-3">Aucun paiement</p>
    </div>
    @endforelse
</div>

{{-- Desktop: Table --}}
<div class="hidden md:block admin-card rounded-2xl overflow-hidden">
    <div class="table-wrap">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5">
                    <th class="text-left px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Utilisateur</th>
                    <th class="text-left px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Montant</th>
                    <th class="text-left px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Méthode</th>
                    <th class="text-left px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Statut</th>
                    <th class="text-left px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Transaction</th>
                    <th class="text-right px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr class="border-b border-white/[0.03] hover:bg-white/[0.02] transition">
                    <td class="px-4 py-3">
                        <p class="font-medium text-white/70 text-xs">{{ $p->user->name ?? 'Supprimé' }}</p>
                        <p class="text-[10px] text-white/25">{{ $p->user->email ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-bold cc-mono text-xs {{ $p->status === 'completed' ? 'text-green-400' : 'text-white/50' }}">
                            {{ number_format($p->amount) }} F
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[10px] px-2 py-1 rounded-full bg-white/5 text-white/40">
                            {{ str_replace('_', ' ', ucfirst($p->payment_method)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-[10px] {{ $p->status === 'completed' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : ($p->status === 'failed' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20') }}">
                            {{ $p->status === 'completed' ? 'Payé' : ($p->status === 'failed' ? 'Échoué' : 'En attente') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[10px] text-white/20 cc-mono">{{ Str::limit($p->transaction_id, 15) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-[10px] text-white/20 cc-mono">{{ $p->created_at->format('d/m/Y H:i') }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-white/15 text-sm">Aucun paiement</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5 flex justify-center">
    {{ $payments->links('pagination::simple-tailwind') }}
</div>
@endsection
