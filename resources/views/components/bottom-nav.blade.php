@php
    $currentRoute = request()->route()?->getName();

    $navBadges = ['matches' => 0, 'likes' => 0];
    if (auth()->check()) {
        $userId = auth()->id();

        // ── Cache 60 s — évite 3 requêtes SQL sur CHAQUE page ──────────
        // Invalidé automatiquement dans SwipeController::invalidateUserCaches()
        // après like, pass, message lu, nouveau match
        $cached = \Illuminate\Support\Facades\Cache::remember(
            "nav_badges_{$userId}",
            60,
            function () use ($userId) {
                $myMatchIds = \App\Models\Matche::forUser($userId)->pluck('id');

                $unreadMessages = $myMatchIds->isEmpty() ? 0
                    : \Illuminate\Support\Facades\DB::table('messages')
                        ->whereIn('match_id', $myMatchIds)
                        ->where('sender_id', '!=', $userId)
                        ->whereNull('read_at')
                        ->count();

                $matchedIds  = \App\Models\Matche::forUser($userId)->get()
                    ->map(fn($m) => $m->user1_id === $userId ? $m->user2_id : $m->user1_id);
                $myLikedIds  = \App\Models\Like::where('user_id', $userId)->pluck('liked_user_id');
                $pendingLikes = \App\Models\Like::where('liked_user_id', $userId)
                    ->whereNotIn('user_id', $matchedIds)
                    ->whereNotIn('user_id', $myLikedIds)
                    ->count();

                return ['matches' => $unreadMessages, 'likes' => $pendingLikes];
            }
        );

        $navBadges = $cached;
    }
@endphp

<div class="fixed bottom-5 left-0 right-0 flex justify-center z-50 px-4">
    <nav id="cc-bottom-nav" class="flex items-center justify-between px-2 py-2 rounded-[20px] cc-surface-raised w-full max-w-sm">
        @php
            $navItems = [
                ['route' => 'swipe',       'label' => 'Découvrir', 'icon' => 'fire',   'badge' => 0],
                ['route' => 'matches',     'label' => 'Matchs',    'icon' => 'heart',  'badge' => $navBadges['matches']],
                ['route' => 'likes.index', 'label' => 'Likes',     'icon' => 'likes',  'badge' => $navBadges['likes']],
                ['route' => 'profile.show','label' => 'Profil',    'icon' => 'user',   'badge' => 0],
            ];
        @endphp

        @foreach($navItems as $item)
        @php
            $isActive = $currentRoute === $item['route'];
            $href     = route($item['route']);
            $badge    = $item['badge'];
        @endphp
        <a href="{{ $href }}"
           class="flex flex-col items-center gap-1 px-4 py-2 rounded-2xl transition-all duration-300 relative
                  {{ $isActive ? 'bg-gradient-to-r from-[#ff5e6c] to-[#ff8a5c] shadow-lg shadow-[#ff5e6c]/20' : 'hover:bg-white/5' }}">

            @if($item['icon'] === 'fire')
            <svg class="w-5 h-5 {{ $isActive ? 'text-white' : 'text-white/40' }}" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 23c-3.6 0-7-2.4-7-7 0-3.1 2.1-5.7 4-7.8l.7-.7c.4-.5 1.1-.5 1.5 0 .6.6 1.3 1.1 2 1.5-.3-1.5-.2-3.1.5-4.5.3-.6 1.1-.7 1.5-.2C17.5 7 19 10.3 19 13.5 19 18.2 16.6 23 12 23z"/>
            </svg>
            @elseif($item['icon'] === 'heart')
            <svg class="w-5 h-5 {{ $isActive ? 'text-white' : 'text-white/40' }}" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            @elseif($item['icon'] === 'likes')
            <svg class="w-5 h-5 {{ $isActive ? 'text-white' : 'text-white/40' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            @elseif($item['icon'] === 'user')
            <svg class="w-5 h-5 {{ $isActive ? 'text-white' : 'text-white/40' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            @endif

            <span class="text-[10px] font-medium {{ $isActive ? 'text-white' : 'text-white/30' }}">{{ $item['label'] }}</span>

            @if($badge > 0 && !$isActive)
            <span class="absolute top-1 right-2 min-w-[16px] h-4 flex items-center justify-center rounded-full text-[9px] font-bold text-white px-1 leading-none"
                  style="background: #ff5e6c; box-shadow: 0 2px 8px rgba(255,94,108,0.5);">
                {{ $badge > 9 ? '9+' : $badge }}
            </span>
            @endif
        </a>
        @endforeach
    </nav>
</div>
<div class="h-28"></div>
