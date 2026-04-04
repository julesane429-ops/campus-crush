{{-- Formulaire d'avis - à inclure dans profile/show.blade.php --}}
@php
    $myReview = \App\Models\Review::where('user_id', auth()->id())->first();
@endphp

<div class="cc-surface rounded-2xl p-5 mb-4 fade-up d3">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-[10px] text-white/25 uppercase tracking-widest font-medium">Ton avis sur Campus Crush</h2>
        @if($myReview)
        <span class="text-[9px] px-2 py-0.5 rounded-full {{ $myReview->status === 'approved' ? 'bg-green-500/10 text-green-400' : ($myReview->status === 'rejected' ? 'bg-red-500/10 text-red-400' : 'bg-yellow-500/10 text-yellow-400') }}">
            {{ $myReview->status === 'approved' ? '✅ Publié' : ($myReview->status === 'rejected' ? '❌ Rejeté' : '⏳ En attente') }}
        </span>
        @endif
    </div>

    <form method="POST" action="{{ route('review.store') }}">
        @csrf

        {{-- Stars --}}
        <div class="flex items-center gap-1 mb-3" id="star-rating">
            @for($i = 1; $i <= 5; $i++)
            <button type="button" data-rating="{{ $i }}"
                class="star-btn text-2xl transition-transform hover:scale-110 {{ $myReview && $i <= $myReview->rating ? 'text-[#ffc145]' : 'text-white/15' }}"
                onclick="setRating({{ $i }})">★</button>
            @endfor
            <input type="hidden" name="rating" id="rating-input" value="{{ $myReview?->rating ?? 0 }}">
        </div>

        {{-- Comment --}}
        <textarea name="comment" rows="3" maxlength="500" placeholder="Dis-nous ce que tu penses de Campus Crush..."
            class="w-full px-4 py-3 bg-white/[0.04] rounded-xl text-[13px] text-white placeholder-white/20 outline-none border border-white/[0.06] focus:border-[#ff5e6c]/40 transition resize-none mb-3">{{ $myReview?->comment }}</textarea>

        <div class="flex items-center gap-2">
            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white active:scale-95 transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
                {{ $myReview ? '✏️ Modifier' : '💬 Envoyer' }}
            </button>

            @if($myReview)
            <form method="POST" action="{{ route('review.destroy') }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl text-xs text-white/25 border border-white/8 hover:text-red-400 hover:border-red-400/20 active:scale-95 transition" onclick="return confirm('Supprimer ton avis ?')">
                    Supprimer
                </button>
            </form>
            @endif
        </div>
    </form>
</div>

<script>
function setRating(n) {
    document.getElementById('rating-input').value = n;
    document.querySelectorAll('.star-btn').forEach((btn, i) => {
        btn.className = btn.className.replace(/text-\[#ffc145\]|text-white\/15/g, '');
        btn.classList.add(i < n ? 'text-[#ffc145]' : 'text-white/15');
    });
}
</script>
