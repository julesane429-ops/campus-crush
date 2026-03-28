@extends('admin.layout')

@section('admin-content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Signalements</h1>
</div>

{{-- Filter --}}
<div class="flex gap-2 mb-6">
    @foreach(['all' => 'Tous', 'pending' => 'En attente', 'reviewed' => 'Examinés', 'resolved' => 'Résolus'] as $val => $label)
    <a href="?status={{ $val }}"
       class="px-4 py-2 rounded-xl text-xs font-medium transition {{ request('status', 'all') === $val ? 'bg-[#ff5e6c]/10 text-[#ff5e6c] border border-[#ff5e6c]/20' : 'bg-white/5 text-white/40 hover:bg-white/10' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="space-y-3">
    @forelse($reports as $report)
    <div class="admin-card rounded-2xl p-5">
        <div class="flex items-start gap-4">
            {{-- Reporter --}}
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-[10px] uppercase tracking-wider px-2 py-1 rounded-full
                        {{ $report->status === 'pending' ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' : ($report->status === 'resolved' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20') }}">
                        {{ $report->status === 'pending' ? 'En attente' : ($report->status === 'resolved' ? 'Résolu' : 'Examiné') }}
                    </span>
                    <span class="text-[10px] text-white/20 cc-mono">{{ $report->created_at->diffForHumans() }}</span>
                </div>

                <p class="text-sm text-white/70 mb-2">
                    <span class="font-medium text-white/90">{{ $report->reporter->name }}</span>
                    <span class="text-white/30">→</span>
                    <span class="font-medium text-red-400">{{ $report->reportedUser->name }}</span>
                </p>

                <p class="text-xs text-white/40 bg-white/[0.02] rounded-xl p-3">
                    "{{ $report->reason }}"
                </p>
            </div>

            {{-- Actions --}}
            @if($report->status === 'pending')
            <div class="flex flex-col gap-2 flex-shrink-0">
                <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="ban">
                    <button class="w-full px-4 py-2 rounded-xl text-[11px] font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 transition" onclick="return confirm('Bannir cet utilisateur ?')">
                        🚫 Bannir
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="dismiss">
                    <button class="w-full px-4 py-2 rounded-xl text-[11px] font-medium bg-white/5 text-white/40 hover:bg-white/10 transition">
                        ✓ Ignorer
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="text-center py-16">
        <span class="text-4xl">🎉</span>
        <p class="text-white/30 mt-3">Aucun signalement</p>
    </div>
    @endforelse
</div>

<div class="mt-6 flex justify-center">
    {{ $reports->withQueryString()->links('pagination::simple-tailwind') }}
</div>
@endsection
