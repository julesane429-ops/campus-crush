@php
    $currentRoute = request()->route()?->getName();
    $lastMatch = \App\Models\Matche::forUser(auth()->id())->latest()->first();
@endphp

<div class="fixed bottom-5 left-0 right-0 flex justify-center z-50 px-4">
    <nav class="flex items-center justify-between px-2 py-2 rounded-[20px] cc-surface-raised w-full max-w-sm">

        @php
            $navItems = [
                ['route' => 'swipe', 'label' => 'Découvrir', 'icon' => 'fire'],
                ['route' => 'matches', 'label' => 'Matchs', 'icon' => 'heart'],
                ['route' => 'messages.chat', 'label' => 'Chat', 'icon' => 'chat'],
                ['route' => 'profile.show', 'label' => 'Profil', 'icon' => 'user'],
            ];
        @endphp

        @foreach($navItems as $item)
        @php
            $isActive = $currentRoute === $item['route'];
            $href = match($item['route']) {
                'swipe' => route('swipe'),
                'matches' => route('matches'),
                'messages.chat' => $lastMatch ? route('messages.chat', $lastMatch->id) : route('matches'),
                'profile.show' => route('profile.show'),
                default => '#'
            };
        @endphp
        <a href="{{ $href }}"
           class="flex flex-col items-center gap-1 px-4 py-2 rounded-2xl transition-all duration-300
                  {{ $isActive ? 'bg-gradient-to-r from-[#ff5e6c] to-[#ff8a5c] shadow-lg shadow-[#ff5e6c]/20' : 'hover:bg-white/5' }}">

            @if($item['icon'] === 'fire')
            <svg class="w-5 h-5 {{ $isActive ? 'text-white' : 'text-white/40' }}" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 23c-3.6 0-7-2.4-7-7 0-3.1 2.1-5.7 4-7.8l.7-.7c.4-.5 1.1-.5 1.5 0 .6.6 1.3 1.1 2 1.5-.3-1.5-.2-3.1.5-4.5.3-.6 1.1-.7 1.5-.2C17.5 7 19 10.3 19 13.5 19 18.2 16.6 23 12 23z"/>
            </svg>
            @elseif($item['icon'] === 'heart')
            <svg class="w-5 h-5 {{ $isActive ? 'text-white' : 'text-white/40' }}" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            @elseif($item['icon'] === 'chat')
            <svg class="w-5 h-5 {{ $isActive ? 'text-white' : 'text-white/40' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            @elseif($item['icon'] === 'user')
            <svg class="w-5 h-5 {{ $isActive ? 'text-white' : 'text-white/40' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            @endif

            <span class="text-[10px] font-medium {{ $isActive ? 'text-white' : 'text-white/30' }}">{{ $item['label'] }}</span>
        </a>
        @endforeach

    </nav>
</div>

<div class="h-28"></div>
