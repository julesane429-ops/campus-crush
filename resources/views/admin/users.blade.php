@extends('admin.layout')

@section('admin-content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Utilisateurs</h1>
    <span class="text-sm text-white/30 cc-mono">{{ $users->total() }} total</span>
</div>

{{-- Search & Filter --}}
<form method="GET" class="flex gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher nom ou email..."
           class="flex-1 px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-[#ff5e6c] placeholder-white/30">
    <select name="filter" onchange="this.form.submit()" class="px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none">
        <option value="" style="background:#1a1145">Tous</option>
        <option value="active" {{ request('filter')==='active'?'selected':'' }} style="background:#1a1145">Actifs</option>
        <option value="banned" {{ request('filter')==='banned'?'selected':'' }} style="background:#1a1145">Bannis</option>
        <option value="no_profile" {{ request('filter')==='no_profile'?'selected':'' }} style="background:#1a1145">Sans profil</option>
    </select>
    <button type="submit" class="px-6 py-3 rounded-xl text-sm font-medium text-white" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
        Chercher
    </button>
</form>

{{-- Users Table --}}
<div class="admin-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5">
                    <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase tracking-wider">Utilisateur</th>
                    <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase tracking-wider hidden md:table-cell">Profil</th>
                    <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase tracking-wider hidden md:table-cell">Abonnement</th>
                    <th class="text-left px-5 py-4 text-[10px] text-white/30 uppercase tracking-wider">Statut</th>
                    <th class="text-right px-5 py-4 text-[10px] text-white/30 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr class="border-b border-white/[0.03] hover:bg-white/[0.02] transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-xs overflow-hidden flex-shrink-0">
                                @if($u->profile?->photo)
                                <img src="{{ $u->profile->photo_url }}" class="w-full h-full object-cover">
                                @else
                                {{ substr($u->name, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-white/80">{{ $u->name }}</p>
                                <p class="text-[11px] text-white/30">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        @if($u->profile)
                        <span class="text-white/50">{{ $u->profile->gender }} · {{ $u->profile->age }} ans · {{ $u->profile->university_name }}</span>
                        @else
                        <span class="text-white/20">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        @if($u->subscription?->isActive())
                            @if($u->subscription->isTrial())
                            <span class="px-2 py-1 rounded-full text-[10px] bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                Essai · {{ $u->subscription->daysRemaining() }}j
                            </span>
                            @else
                            <span class="px-2 py-1 rounded-full text-[10px] bg-green-500/10 text-green-400 border border-green-500/20">
                                Payé · {{ $u->subscription->daysRemaining() }}j
                            </span>
                            @endif
                        @else
                        <span class="px-2 py-1 rounded-full text-[10px] bg-white/5 text-white/30">Expiré</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($u->is_admin)
                        <span class="px-2 py-1 rounded-full text-[10px] bg-[#ffc145]/10 text-[#ffc145] border border-[#ffc145]/20">Admin</span>
                        @elseif($u->is_banned)
                        <span class="px-2 py-1 rounded-full text-[10px] bg-red-500/10 text-red-400 border border-red-500/20">Banni</span>
                        @else
                        <span class="px-2 py-1 rounded-full text-[10px] bg-green-500/10 text-green-400 border border-green-500/20">Actif</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        @if(!$u->is_admin)
                        <div class="flex items-center justify-end gap-2">
                            @if($u->is_banned)
                            <form method="POST" action="{{ route('admin.users.unban', $u->id) }}">
                                @csrf
                                <button class="px-3 py-1.5 rounded-lg text-[11px] bg-green-500/10 text-green-400 hover:bg-green-500/20 transition">Débannir</button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.users.ban', $u->id) }}" onsubmit="return confirm('Bannir {{ e($u->name) }} ?')">
                                @csrf
                                <button class="px-3 py-1.5 rounded-lg text-[11px] bg-red-500/10 text-red-400 hover:bg-red-500/20 transition">Bannir</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.users.delete', $u->id) }}" onsubmit="return confirm('Supprimer définitivement {{ e($u->name) }} ?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 rounded-lg text-[11px] bg-white/5 text-white/30 hover:bg-red-500/10 hover:text-red-400 transition">🗑️</button>
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
<div class="mt-6 flex justify-center">
    {{ $users->withQueryString()->links('pagination::simple-tailwind') }}
</div>
@endsection
