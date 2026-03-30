@extends('admin.layout')

@section('admin-content')
<div class="flex items-center justify-between mb-5">
    <h1 class="text-xl md:text-2xl font-bold">Signalements</h1>
    <span class="text-xs text-white/25 cc-mono">{{ $reports->total() }} total</span>
</div>

{{-- Filter --}}
<div class="flex gap-2 mb-5 overflow-x-auto pb-1" style="scrollbar-width: none;">
    @foreach(['all' => 'Tous', 'pending' => '⏳ En attente', 'resolved' => '✅ Résolus', 'reviewed' => '👁 Examinés'] as $val => $label)
    <a href="{{ route('admin.reports', ['status' => $val]) }}"
       class="flex-shrink-0 px-3.5 py-2 rounded-xl text-xs font-medium transition {{ request('status', 'all') === $val ? 'text-white' : 'text-white/30 hover:text-white/50' }}"
       style="{{ request('status', 'all') === $val ? 'background: linear-gradient(135deg, #ff5e6c, #ff8a5c);' : 'background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Reports list --}}
<div class="space-y-3">
    @forelse($reports as $report)
    <div class="admin-card rounded-2xl p-4">
        <div class="flex items-start gap-3">
            {{-- Reporter --}}
            <div class="flex-shrink-0">
                <div class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-xs overflow-hidden">
                    @if($report->reporter->profile?->photo)
                    <img src="{{ $report->reporter->profile->photo_url }}" class="w-full h-full object-cover">
                    @else
                    {{ substr($report->reporter->name, 0, 1) }}
                    @endif
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                    <span class="text-xs font-medium text-white/70">{{ $report->reporter->name }}</span>
                    <span class="text-[10px] text-white/20">a signalé</span>
                    <span class="text-xs font-medium text-[#ff5e6c]">{{ $report->reportedUser->name }}</span>
                </div>

                <p class="text-xs text-white/40 leading-relaxed mb-2">{{ $report->reason }}</p>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[9px] text-white/15 cc-mono">{{ $report->created_at->diffForHumans() }}</span>

                    @if($report->status === 'pending')
                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">En attente</span>
                    @elseif($report->status === 'resolved')
                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-green-500/10 text-green-400 border border-green-500/20">Résolu</span>
                    @else
                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">Examiné</span>
                    @endif
                </div>
            </div>
        </div>

        @if($report->status === 'pending')
        <div class="flex gap-2 mt-3 pt-3 border-t border-white/5">
            <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}" class="flex-1">
                @csrf
                <input type="hidden" name="action" value="ban">
                <button class="w-full py-2 rounded-xl text-[11px] font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 active:scale-95 transition" onclick="return confirm('Bannir {{ e($report->reportedUser->name) }} ?')">
                    🚫 Bannir l'utilisateur
                </button>
            </form>
            <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}" class="flex-1">
                @csrf
                <input type="hidden" name="action" value="dismiss">
                <button class="w-full py-2 rounded-xl text-[11px] font-medium bg-white/5 text-white/40 hover:bg-white/10 active:scale-95 transition">
                    👁 Ignorer
                </button>
            </form>
        </div>
        @endif
    </div>
    @empty
    <div class="text-center py-16">
        <span class="text-4xl">🎉</span>
        <p class="text-sm text-white/25 mt-3">Aucun signalement</p>
    </div>
    @endforelse
</div>

<div class="mt-5 flex justify-center">
    {{ $reports->withQueryString()->links('pagination::simple-tailwind') }}
</div>
@endsection
