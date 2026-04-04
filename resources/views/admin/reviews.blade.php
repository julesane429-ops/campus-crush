@extends('admin.layout')

@section('admin-content')
<div class="flex items-center justify-between mb-5">
    <h1 class="text-xl md:text-2xl font-bold">Avis utilisateurs</h1>
    <span class="text-xs text-white/25 cc-mono">{{ $reviews->total() }} total</span>
</div>

{{-- Filters --}}
<div class="flex gap-2 mb-5 overflow-x-auto pb-1" style="scrollbar-width: none;">
    @foreach(['all' => 'Tous', 'pending' => '⏳ En attente', 'approved' => '✅ Approuvés', 'rejected' => '❌ Rejetés'] as $val => $label)
    <a href="{{ route('admin.reviews', ['status' => $val]) }}"
       class="flex-shrink-0 px-3.5 py-2 rounded-xl text-xs font-medium transition {{ request('status', 'all') === $val ? 'text-white' : 'text-white/30 hover:text-white/50' }}"
       style="{{ request('status', 'all') === $val ? 'background: linear-gradient(135deg, #ff5e6c, #ff8a5c);' : 'background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Reviews list --}}
<div class="space-y-3">
    @forelse($reviews as $review)
    <div class="admin-card rounded-2xl p-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-sm overflow-hidden flex-shrink-0">
                @if($review->user->profile?->photo)
                <img src="{{ $review->user->profile->photo_url }}" class="w-full h-full object-cover">
                @else
                {{ substr($review->user->name, 0, 1) }}
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-medium text-white/70">{{ $review->user->name }}</span>
                    <div class="flex items-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="text-xs {{ $i <= $review->rating ? 'text-[#ffc145]' : 'text-white/10' }}">★</span>
                        @endfor
                    </div>
                    @if($review->is_featured)
                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-[#ffc145]/10 text-[#ffc145]">⭐ Featured</span>
                    @endif
                </div>

                <p class="text-xs text-white/50 leading-relaxed mb-2">{{ $review->comment }}</p>

                <div class="flex items-center gap-2">
                    <span class="text-[9px] text-white/15 cc-mono">{{ $review->created_at->diffForHumans() }}</span>
                    @if($review->status === 'pending')
                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-yellow-500/10 text-yellow-400">En attente</span>
                    @elseif($review->status === 'approved')
                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-green-500/10 text-green-400">Approuvé</span>
                    @else
                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-red-500/10 text-red-400">Rejeté</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-white/5">
            @if($review->status !== 'approved')
            <form method="POST" action="{{ route('admin.reviews.approve', $review->id) }}">@csrf
                <button class="px-3 py-1.5 rounded-xl text-[11px] font-medium bg-green-500/10 text-green-400 hover:bg-green-500/20 active:scale-95 transition">✅ Approuver</button>
            </form>
            @endif

            @if($review->status !== 'rejected')
            <form method="POST" action="{{ route('admin.reviews.reject', $review->id) }}">@csrf
                <button class="px-3 py-1.5 rounded-xl text-[11px] font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 active:scale-95 transition">❌ Rejeter</button>
            </form>
            @endif

            <form method="POST" action="{{ route('admin.reviews.feature', $review->id) }}">@csrf
                <button class="px-3 py-1.5 rounded-xl text-[11px] font-medium bg-[#ffc145]/10 text-[#ffc145] hover:bg-[#ffc145]/20 active:scale-95 transition">
                    {{ $review->is_featured ? '⭐ Retirer featured' : '⭐ Mettre en avant' }}
                </button>
            </form>

            <form method="POST" action="{{ route('admin.reviews.delete', $review->id) }}" onsubmit="return confirm('Supprimer cet avis ?')">@csrf @method('DELETE')
                <button class="px-3 py-1.5 rounded-xl text-[11px] font-medium bg-white/5 text-white/25 hover:bg-red-500/10 hover:text-red-400 active:scale-95 transition">🗑️</button>
            </form>
        </div>
    </div>
    @empty
    <div class="text-center py-16">
        <span class="text-4xl">💬</span>
        <p class="text-sm text-white/25 mt-3">Aucun avis</p>
    </div>
    @endforelse
</div>

<div class="mt-5 flex justify-center">
    {{ $reviews->withQueryString()->links('pagination::simple-tailwind') }}
</div>
@endsection
