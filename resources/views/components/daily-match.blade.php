{{-- Match du jour - inclure dans swipe/index.blade.php --}}
@php
    $dailyMatchId = \Illuminate\Support\Facades\Cache::get('daily_match_' . auth()->id());
    $dailyMatch = $dailyMatchId ? \App\Models\User::with('profile')->find($dailyMatchId) : null;
    $dailyProfile = $dailyMatch?->profile;
@endphp

@if($dailyMatch && $dailyProfile)
<div id="daily-match-banner" class="cc-surface rounded-2xl p-4 mb-4 relative overflow-hidden fade-up" style="animation-delay:0.05s; background: linear-gradient(135deg, rgba(168,85,247,0.08), rgba(255,94,108,0.08)); border: 1px solid rgba(168,85,247,0.15);">
    <button onclick="document.getElementById('daily-match-banner').style.display='none'" class="absolute top-3 right-3 text-white/20 hover:text-white/40 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    <div class="flex items-center gap-3.5">
        <div class="w-14 h-14 rounded-full p-[2px] flex-shrink-0" style="background: linear-gradient(135deg, #a855f7, #ff5e6c);">
            <div class="w-full h-full rounded-full overflow-hidden">
                <img src="{{ $dailyProfile->photo_url }}" class="w-full h-full object-cover" alt="">
            </div>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold" style="background: rgba(168,85,247,0.15); color: #c084fc;">✨ Match du jour</span>
            </div>
            <h3 class="font-semibold text-sm truncate">{{ e($dailyMatch->name) }}<span class="text-white/40 font-normal ml-1">{{ $dailyProfile->age }}</span></h3>
            <p class="text-[10px] text-white/30 truncate">🎓 {{ $dailyProfile->university_name ?? '' }} · {{ $dailyProfile->ufr ?? '' }}</p>
        </div>
    </div>

    @if($dailyProfile->bio)
    <p class="text-xs text-white/35 mt-2.5 line-clamp-2 leading-relaxed italic">« {{ e(\Illuminate\Support\Str::limit($dailyProfile->bio, 100)) }} »</p>
    @endif

    <div class="flex gap-2 mt-3">
        <button onclick="document.getElementById('daily-match-banner').style.display='none'"
            class="flex-1 py-2 rounded-xl text-xs font-medium text-white/30 border border-white/8 hover:bg-white/5 active:scale-95 transition">
            Pas maintenant
        </button>
        <button onclick="likeDailyMatch({{ $dailyMatch->id }})"
            class="flex-1 py-2 rounded-xl text-xs font-semibold text-white active:scale-95 transition" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
            💚 Liker
        </button>
    </div>
</div>

<script>
function likeDailyMatch(userId) {
    fetch('/like/' + userId, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('daily-match-banner').style.display = 'none';
        if (data.match) {
            alert('🎉 C\'est un match ! Allez discuter !');
            window.location.href = '/messages/' + data.match_id;
        }
    })
    .catch(() => {});
}
</script>
@endif
