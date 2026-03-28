@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
<div class="relative z-10 w-full max-w-md flex flex-col text-white py-6 px-4">

    <div class="rounded-3xl p-6 sm:p-8 cc-surface-raised cc-fade-up">

        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-4" style="background: linear-gradient(135deg, rgba(255,94,108,0.15), rgba(168,85,247,0.1)); border: 1px solid rgba(255,94,108,0.15);">
                <svg class="w-8 h-8 text-[#ff5e6c]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </div>
            <h1 class="text-2xl font-bold">Créer votre profil</h1>
            <p class="text-white/30 mt-2 text-sm">Faites une bonne première impression ✨</p>
        </div>

        <form method="POST" action="{{ route('profile.store') }}" enctype="multipart/form-data" class="space-y-5" id="profile-form">
            @csrf

            {{-- Photo --}}
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Photo de profil</label>
                <input type="file" name="photo" id="photo" accept="image/*" class="cc-input text-sm file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:bg-[#ff5e6c]/20 file:text-[#ff5e6c]">
                <div id="photo-preview" class="flex gap-3 mt-3"></div>
            </div>

            {{-- Age & Gender --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Âge</label>
                    <input type="number" name="age" min="17" max="60" placeholder="21" required class="cc-input">
                </div>
                <div>
                    <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Genre</label>
                    <select name="gender" required class="cc-select">
                        <option value="" disabled selected>Choisir</option>
                        <option value="homme">Homme</option>
                        <option value="femme">Femme</option>
                    </select>
                </div>
            </div>

            {{-- Université --}}
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Université 🏫</label>
                <select name="university_id" class="cc-select">
                    <option value="">Sélectionner votre université</option>
                    @foreach($universities as $uni)
                    <option value="{{ $uni->id }}">{{ $uni->short_name }} - {{ $uni->name }} ({{ $uni->city }})</option>
                    @endforeach
                </select>
            </div>

            {{-- UFR --}}
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">UFR / Département</label>
                <select name="ufr" required class="cc-select">
                    <option value="">Choisir</option>
                    @foreach(['SAT','SJP','S2ATA','LSH','SEFS','Sciences','Lettres','Droit','Économie','Médecine','Info','Autre'] as $u)
                    <option value="{{ $u }}">{{ $u }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Promotion --}}
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Promotion <span class="text-white/20">(optionnel)</span></label>
                <input type="text" name="promotion" placeholder="ex: P30" class="cc-input">
            </div>

            {{-- Level --}}
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-3 block">Niveau</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['L1','L2','L3','M1','M2'] as $l)
                    <button type="button" data-level="{{ $l }}" class="level-btn cc-tag">{{ $l }}</button>
                    @endforeach
                </div>
                <input type="hidden" id="level" name="level" required>
            </div>

            {{-- Interests --}}
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-3 block">Centres d'intérêt</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Sport','Musique','Cinéma','Voyage','Lecture','Jeux vidéo','Cuisine','Tech','Football','Danse','Mode','Entrepreneuriat'] as $int)
                    <button type="button" data-value="{{ $int }}" class="interest-btn cc-tag">{{ $int }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="interests" id="interests">
            </div>

            {{-- Bio --}}
            <div>
                <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">Bio <span class="text-white/20 normal-case">(<span id="char-count">0</span>/200)</span></label>
                <textarea name="bio" rows="3" maxlength="200" required placeholder="Parlez de vous..." class="cc-input resize-none" id="bio"></textarea>
            </div>

            <button type="submit" id="save-btn" class="w-full py-4 rounded-2xl font-semibold text-white mt-2 transition hover:-translate-y-0.5 active:scale-[0.98]" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                <span id="save-text">Enregistrer mon profil</span>
                <span id="save-loading" class="hidden"><svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg></span>
            </button>
        </form>

        <div id="success-state" class="hidden text-center py-8">
            <div class="text-5xl mb-4">🎉</div>
            <h2 class="text-2xl font-bold mb-2">Profil créé !</h2>
            <p class="text-white/40 mb-6">Bonne chance dans vos rencontres</p>
            <a href="{{ route('swipe') }}" class="inline-block px-8 py-3 rounded-2xl font-semibold text-white" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">Commencer à swiper 🔥</a>
        </div>
    </div>
    <p class="text-center text-white/20 text-xs mt-6">💕 Campus Crush</p>
</div>
</div>

@push('scripts')
<script>
let selectedInterests = [];
const form = document.getElementById('profile-form');

document.querySelectorAll('.level-btn').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('.level-btn').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    document.getElementById('level').value = b.dataset.level;
}));

document.querySelectorAll('.interest-btn').forEach(b => b.addEventListener('click', () => {
    const v = b.dataset.value;
    if (selectedInterests.includes(v)) { selectedInterests = selectedInterests.filter(i=>i!==v); b.classList.remove('active'); }
    else { selectedInterests.push(v); b.classList.add('active'); }
    document.getElementById('interests').value = selectedInterests.join(',');
}));

document.getElementById('bio').addEventListener('input', e => document.getElementById('char-count').textContent = e.target.value.length);

document.getElementById('photo').addEventListener('change', function(e) {
    const p = document.getElementById('photo-preview'); p.innerHTML='';
    const f = e.target.files[0]; if(!f) return;
    const r = new FileReader();
    r.onload = ev => { const img = document.createElement('img'); img.src=ev.target.result; img.className='w-20 h-20 rounded-xl object-cover'; p.appendChild(img); };
    r.readAsDataURL(f);
});

form.addEventListener('submit', function(e) {
    e.preventDefault();
    if (!document.getElementById('level').value) { alert('Choisissez votre niveau.'); return; }
    document.getElementById('save-text').classList.add('hidden');
    document.getElementById('save-loading').classList.remove('hidden');
    fetch(form.action, {
        method: 'POST', body: new FormData(form),
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { form.classList.add('hidden'); document.getElementById('success-state').classList.remove('hidden'); }
        else if (d.errors) { alert(Object.values(d.errors).flat().join('\n')); document.getElementById('save-text').classList.remove('hidden'); document.getElementById('save-loading').classList.add('hidden'); }
    })
    .catch(() => { alert('Erreur réseau.'); document.getElementById('save-text').classList.remove('hidden'); document.getElementById('save-loading').classList.add('hidden'); });
});
</script>
@endpush
@endsection
