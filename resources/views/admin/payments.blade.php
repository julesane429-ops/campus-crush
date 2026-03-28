@extends('admin.layout')

@section('admin-content')
<h1 class="text-2xl font-bold mb-6">Paiements</h1>

<div class="admin-card rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/5">
                <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase">Utilisateur</th>
                <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase">Montant</th>
                <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase hidden md:table-cell">Méthode</th>
                <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase hidden md:table-cell">Téléphone</th>
                <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase">Statut</th>
                <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $p)
            <tr class="border-b border-white/[0.03] hover:bg-white/[0.02]">
                <td class="px-5 py-4 font-medium text-white/70">{{ $p->user->name ?? 'Supprimé' }}</td>
                <td class="px-5 py-4 cc-mono font-bold text-[#ffc145]">{{ number_format($p->amount) }} F</td>
                <td class="px-5 py-4 text-white/50 hidden md:table-cell">
                    @if($p->payment_method === 'orange_money') 🟠 Orange Money
                    @elseif($p->payment_method === 'wave') 🔵 Wave
                    @elseif($p->payment_method === 'free_money') 🟢 Free Money
                    @else {{ $p->payment_method }}
                    @endif
                </td>
                <td class="px-5 py-4 text-white/30 cc-mono text-xs hidden md:table-cell">{{ $p->phone_number }}</td>
                <td class="px-5 py-4">
                    <span class="px-2 py-1 rounded-full text-[10px]
                        {{ $p->status === 'completed' ? 'bg-green-500/10 text-green-400' : ($p->status === 'pending' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-red-500/10 text-red-400') }}">
                        {{ $p->status === 'completed' ? '✓ Payé' : ($p->status === 'pending' ? '⏳ Attente' : '✗ Échoué') }}
                    </span>
                </td>
                <td class="px-5 py-4 text-white/20 text-xs cc-mono">{{ $p->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center text-white/20">Aucun paiement</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6 flex justify-center">
    {{ $payments->links('pagination::simple-tailwind') }}
</div>
@endsection
