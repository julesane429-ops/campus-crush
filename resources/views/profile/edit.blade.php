@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
<div class="relative z-10 w-full max-w-md flex flex-col text-white py-6 px-4">

    <div class="rounded-3xl p-6 sm:p-8 cc-surface-raised cc-fade-up">

        <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-1.5 text-xs text-white/30 hover:text-white/60 transition mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
            Retour
        </a>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold">Modifier profil</h1>
            <p class="text-white/30 mt-1 text-sm">Gardez votre profil à jour ✨</p>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PATCH')

            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Photo</label>
                <input type="file" name="photo" id="photo" accept="image/*" class="cc-input text-sm file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:bg-[#ff5e6c]/20 file:text-[#ff5e6c]">
                <div id="photo-preview" class="flex gap-3 mt-3">
                    <img src="{{ $profile->photo_url }}" class="w-20 h-20 rounded-xl object-cover">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Âge</label>
                    <input type="number" name="age" value="{{ $profile->age }}" min="17" max="60" required class="cc-input">
                </div>
                <div>
                    <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Genre</label>
                    <select name="gender" required class="cc-select">
                        <option value="homme" {{ $profile->gender=='homme'?'selected':'' }}>Homme</option>
                        <option value="femme" {{ $profile->gender=='femme'?'selected':'' }}>Femme</option>
                    </select>
                </div>
            </div>

            {{-- Université --}}
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Université 🏫</label>
                <select name="university_id" class="cc-select">
                    <option value="">Sélectionner</option>
                    @foreach($universities as $uni)
                    <option value="{{ $uni->id }}" {{ $profile->university_id == $uni->id ? 'selected' : '' }}>
                        {{ $uni->short_name }} - {{ $uni->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">UFR / Département</label>
                <select name="ufr" required class="cc-select">
                    @foreach(['SAT','SJP','S2ATA','LSH','SEFS','Sciences','Lettres','Droit','Économie','Médecine','Info','Autre'] as $u)
                    <option value="{{ $u }}" {{ $profile->ufr==$u?'selected':'' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Promotion</label>
                <input type="text" name="promotion" value="{{ $profile->promotion }}" placeholder="ex: P30" class="cc-input">
            </div>

            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-3 block">Niveau</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['L1','L2','L3','M1','M2'] as $l)
                    <button type="button" data-level="{{ $l }}" class="level-btn cc-tag {{ $profile->level==$l ? 'active' : '' }}">{{ $l }}</button>
                    @endforeach
                </div>
                <input type="hidden" id="level" name="level" value="{{ $profile->level }}">
            </div>

            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Bio</label>
                <textarea name="bio" rows="3" maxlength="200" class="cc-input resize-none" id="bio">{{ $profile->bio }}</textarea>
                @php $bioLen = strlen($profile->bio ?? ''); @endphp
                <div class="text-right text-[10px] text-white/20 mt-1"><span id="bio-count">{{ $bioLen }}</span>/200</div>
            </div>

            <button type="submit" class="w-full py-4 rounded-2xl font-semibold text-white transition hover:-translate-y-0.5" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                Enregistrer
            </button>
        </form>
    </div>
    <p class="text-center text-white/20 text-xs mt-6">💕 Campus Crush</p>
</div>
</div>

@push('scripts')
<script>
document.getElementById('photo').addEventListener('change', e => {
    const p = document.getElementById('photo-preview'); p.innerHTML='';
    const f = e.target.files[0]; if(!f) return;
    const r = new FileReader();
    r.onload = ev => { const img = document.createElement('img'); img.src=ev.target.result; img.className='w-20 h-20 rounded-xl object-cover'; p.appendChild(img); };
    r.readAsDataURL(f);
});
document.querySelectorAll('.level-btn').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('.level-btn').forEach(x => x.classList.remove('active'));
    b.classList.add('active'); document.getElementById('level').value = b.dataset.level;
}));
const bio = document.getElementById('bio');
bio.addEventListener('input', () => document.getElementById('bio-count').textContent = bio.value.length);
</script>
@endpush
@endsection
