@extends('admin.layout')

@section('admin-content')
<div class="flex items-center justify-between mb-5">
    <h1 class="text-xl md:text-2xl font-bold">Utilisateurs</h1>
    <span class="text-xs text-white/25 cc-mono">{{ $users->total() }} total</span>
</div>

{{-- Search & Filter --}}
<form method="GET" class="flex flex-col sm:flex-row gap-2 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
           class="flex-1 px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-[#ff5e6c] placeholder-white/25">
    <div class="flex gap-2">
        <select name="filter" onchange="this.form.submit()" class="flex-1 sm:flex-none px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none">
            <option value="" style="background:#1a1145">Tous</option>
            <option value="active" {{ request('filter')==='active'?'selected':'' }} style="background:#1a1145">Actifs</option>
            <option value="banned" {{ request('filter')==='banned'?'selected':'' }} style="background:#1a1145">Bannis</option>
            <option value="no_profile" {{ request('filter')==='no_profile'?'selected':'' }} style="background:#1a1145">Sans profil</option>
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-white" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
            🔍
        </button>
    </div>
</form>

{{-- Mobile: Card layout --}}
<div class="md:hidden space-y-3">
    @foreach($users as $u)
    <div class="admin-card rounded-2xl p-4">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-sm overflow-hidden flex-shrink-0">
                @if($u->profile?->photo)
                <img src="{{ $u->profile->photo_url }}" class="w-full h-full object-cover">
                @else
                {{ substr($u->name, 0, 1) }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-sm truncate">{{ $u->name }}</p>
                <p class="text-[11px] text-white/25 truncate">{{ $u->email }}</p>
            </div>
            @if($u->is_admin)
            <span class="text-[9px] px-2 py-1 rounded-full bg-[#ffc145]/10 text-[#ffc145] border border-[#ffc145]/20 flex-shrink-0">Admin</span>
            @elseif($u->is_banned)
            <span class="text-[9px] px-2 py-1 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 flex-shrink-0">Banni</span>
            @else
            <span class="text-[9px] px-2 py-1 rounded-full bg-green-500/10 text-green-400 border border-green-500/20 flex-shrink-0">Actif</span>
            @endif
        </div>

        @if($u->profile)
        <div class="flex flex-wrap gap-1.5 mb-3">
            <span class="text-[10px] text-white/30 px-2 py-0.5 rounded-full bg-white/5">{{ $u->profile->gender }} · {{ $u->profile->age }} ans</span>
            <span class="text-[10px] text-white/30 px-2 py-0.5 rounded-full bg-white/5">{{ $u->profile->university_name ?? 'UGB' }}</span>
            @if($u->subscription?->isActive())
            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $u->subscription->isTrial() ? 'bg-blue-500/10 text-blue-400' : 'bg-green-500/10 text-green-400' }}">
                {{ $u->subscription->isTrial() ? 'Essai' : 'Payé' }} · {{ $u->subscription->daysRemaining() }}j
            </span>
            @else
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-white/5 text-white/20">Expiré</span>
            @endif
        </div>
        @endif

        @if(!$u->is_admin)
        <div class="flex gap-2">
            @if($u->is_banned)
            <form method="POST" action="{{ route('admin.users.unban', $u->id) }}" class="flex-1">@csrf
                <button class="w-full py-2 rounded-xl text-[11px] font-medium bg-green-500/10 text-green-400 active:scale-95 transition">Débannir</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.users.ban', $u->id) }}" class="flex-1" onsubmit="return confirm('Bannir {{ e($u->name) }} ?')">@csrf
                <button class="w-full py-2 rounded-xl text-[11px] font-medium bg-red-500/10 text-red-400 active:scale-95 transition">Bannir</button>
            </form>
            @endif
            <form method="POST" action="{{ route('admin.users.delete', $u->id) }}" onsubmit="return confirm('Supprimer {{ e($u->name) }} ?')">@csrf @method('DELETE')
                <button class="py-2 px-3 rounded-xl text-[11px] bg-white/5 text-white/25 active:scale-95 transition">🗑️</button>
            </form>
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- Desktop: Table layout --}}
<div class="hidden md:block admin-card rounded-2xl overflow-hidden">
    <div class="table-wrap">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5">
                    <th class="text-left px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Utilisateur</th>
                    <th class="text-left px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Profil</th>
                    <th class="text-left px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Abonnement</th>
                    <th class="text-left px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Statut</th>
                    <th class="text-right px-4 py-3 text-[10px] text-white/25 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr class="border-b border-white/[0.03] hover:bg-white/[0.02] transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-xs overflow-hidden flex-shrink-0">
                                @if($u->profile?->photo)
                                <img src="{{ $u->profile->photo_url }}" class="w-full h-full object-cover">
                                @else
                                {{ substr($u->name, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-white/70 text-xs">{{ $u->name }}</p>
                                <p class="text-[10px] text-white/25">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if($u->profile)
                        <span class="text-white/40 text-xs">{{ $u->profile->gender }} · {{ $u->profile->age }} ans · {{ $u->profile->university_name ?? 'UGB' }}</span>
                        @else
                        <span class="text-white/15 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($u->subscription?->isActive())
                        <span class="px-2 py-1 rounded-full text-[10px] {{ $u->subscription->isTrial() ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-green-500/10 text-green-400 border border-green-500/20' }}">
                            {{ $u->subscription->isTrial() ? 'Essai' : 'Payé' }} · {{ $u->subscription->daysRemaining() }}j
                        </span>
                        @else
                        <span class="px-2 py-1 rounded-full text-[10px] bg-white/5 text-white/20">Expiré</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($u->is_admin)
                        <span class="px-2 py-1 rounded-full text-[10px] bg-[#ffc145]/10 text-[#ffc145] border border-[#ffc145]/20">Admin</span>
                        @elseif($u->is_banned)
                        <span class="px-2 py-1 rounded-full text-[10px] bg-red-500/10 text-red-400 border border-red-500/20">Banni</span>
                        @else
                        <span class="px-2 py-1 rounded-full text-[10px] bg-green-500/10 text-green-400 border border-green-500/20">Actif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if(!$u->is_admin)
                        <div class="flex items-center justify-end gap-1.5">
                            @if($u->is_banned)
                            <form method="POST" action="{{ route('admin.users.unban', $u->id) }}">@csrf
                                <button class="px-2.5 py-1 rounded-lg text-[10px] bg-green-500/10 text-green-400 hover:bg-green-500/20 transition">Débannir</button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.users.ban', $u->id) }}" onsubmit="return confirm('Bannir ?')">@csrf
                                <button class="px-2.5 py-1 rounded-lg text-[10px] bg-red-500/10 text-red-400 hover:bg-red-500/20 transition">Bannir</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.users.delete', $u->id) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                                <button class="px-2.5 py-1 rounded-lg text-[10px] bg-white/5 text-white/25 hover:bg-red-500/10 hover:text-red-400 transition">🗑️</button>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-5 flex justify-center">
    {{ $users->withQueryString()->links('pagination::simple-tailwind') }}
</div>
@endsection
