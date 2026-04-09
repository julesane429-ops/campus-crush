@extends('layouts.app')

@section('content')
<div class="cc-bg-main cc-bg-noise min-h-screen flex justify-center">
<div class="relative z-10 w-full max-w-md flex flex-col text-white py-6 px-4">

    {{-- ── Barre de progression ── --}}
    <div id="progress-header" class="mb-6 cc-fade-up">

        {{-- Étiquette étape --}}
        <div class="flex justify-between items-center mb-3">
            <span id="step-label" class="text-xs font-semibold text-white/40 uppercase tracking-widest"></span>
            <span id="step-counter" class="text-xs font-mono text-white/25"></span>
        </div>

        {{-- Barre --}}
        <div class="w-full h-1.5 rounded-full" style="background: rgba(255,255,255,0.06);">
            <div id="progress-bar"
                 class="h-1.5 rounded-full transition-all duration-500 ease-out"
                 style="width: 33.33%; background: linear-gradient(90deg, #ff5e6c, #ffc145);">
            </div>
        </div>

        {{-- Étapes dots --}}
        <div class="flex justify-between mt-3 px-0.5">
            @foreach([['📸','Photo'],['🎓','Infos'],['✨','Personnalité']] as $i => $s)
            <div class="flex flex-col items-center gap-1.5">
                <div id="dot-{{ $i+1 }}"
                     class="w-7 h-7 rounded-full flex items-center justify-center text-xs transition-all duration-300"
                     style="{{ $i === 0 ? 'background: linear-gradient(135deg,#ff5e6c,#ff8a5c); box-shadow: 0 0 12px rgba(255,94,108,0.4);' : 'background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);' }}">
                    {{ $s[0] }}
                </div>
                <span id="dot-label-{{ $i+1 }}"
                      class="text-[10px] transition-colors duration-300 {{ $i === 0 ? 'text-white/60' : 'text-white/20' }}">
                    {{ $s[1] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Carte formulaire ── --}}
    <div class="rounded-3xl p-6 sm:p-8 cc-surface-raised cc-fade-up">

        <form method="POST" action="{{ route('profile.store') }}" enctype="multipart/form-data" id="profile-form">
            @csrf

            {{-- ═══════════════════════════
                 ÉTAPE 1 — Photo
            ═══════════════════════════ --}}
            <div id="step-1" class="step-section">
                <div class="text-center mb-8">
                    <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-4"
                         style="background: linear-gradient(135deg,rgba(255,94,108,0.15),rgba(168,85,247,0.1)); border:1px solid rgba(255,94,108,0.15);">
                        <span class="text-3xl">📸</span>
                    </div>
                    <h1 class="text-2xl font-bold">Ta photo de profil</h1>
                    <p class="text-white/30 mt-2 text-sm">Les profils avec photo reçoivent 3× plus de likes</p>
                </div>

                {{-- Zone upload --}}
                <div id="photo-drop-zone"
                     class="relative flex flex-col items-center justify-center w-full rounded-3xl transition-all duration-300 cursor-pointer overflow-hidden"
                     style="height: 260px; background: rgba(255,255,255,0.03); border: 2px dashed rgba(255,255,255,0.1);"
                     onclick="document.getElementById('photo').click()">

                    {{-- Preview --}}
                    <img id="photo-preview-img" src="" alt=""
                         class="hidden absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 hidden" id="photo-overlay"
                         style="background: rgba(0,0,0,0.35); backdrop-filter: blur(2px);">
                    </div>

                    {{-- Placeholder --}}
                    <div id="photo-placeholder" class="flex flex-col items-center gap-3 relative z-10">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center"
                             style="background: rgba(255,94,108,0.1); border: 1px solid rgba(255,94,108,0.15);">
                            <svg class="w-7 h-7 text-[#ff5e6c]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-white/70">Appuie pour choisir une photo</p>
                            <p class="text-xs text-white/25 mt-1">JPG, PNG · Max 5 Mo</p>
                        </div>
                    </div>

                    {{-- Label "Changer" quand photo choisie --}}
                    <div id="photo-change-label"
                         class="hidden relative z-10 flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                             style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" d="M15.232 5.232l3.536 3.536M9 11l6-6 3.536 3.536-6 6H9v-3.536z"/>
                            </svg>
                        </div>
                        <span class="text-xs text-white/70 font-medium">Changer la photo</span>
                    </div>
                </div>

                <input type="file" name="photo" id="photo" accept="image/*" class="hidden">
                <p class="text-center text-xs text-white/20 mt-3">Tu pourras la modifier plus tard depuis ton profil</p>
            </div>

            {{-- ═══════════════════════════
                 ÉTAPE 2 — Infos
            ═══════════════════════════ --}}
            <div id="step-2" class="step-section hidden">
                <div class="text-center mb-8">
                    <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-4"
                         style="background: linear-gradient(135deg,rgba(168,85,247,0.15),rgba(255,94,108,0.1)); border:1px solid rgba(168,85,247,0.15);">
                        <span class="text-3xl">🎓</span>
                    </div>
                    <h1 class="text-2xl font-bold">Tes infos</h1>
                    <p class="text-white/30 mt-2 text-sm">Pour te montrer aux bons étudiants</p>
                </div>

                <div class="space-y-5">
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
                            <option value="{{ $uni->id }}">{{ $uni->short_name }} — {{ $uni->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- UFR --}}
                    <div>
                        <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">UFR / Département</label>
                        <select name="ufr" required class="cc-select">
                            <option value="">Choisir</option>
                            @foreach(['SAT','SJP','S2ATA','LSH','SEFS','SEG','Sciences','Lettres','Droit','Économie','Médecine','Info','Autre'] as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Promotion --}}
                    <div>
                        <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">
                            Promotion <span class="text-white/20 normal-case">(optionnel)</span>
                        </label>
                        <input type="text" name="promotion" placeholder="ex: P30" class="cc-input">
                    </div>

                    {{-- Niveau --}}
                    <div>
                        <label class="text-xs text-white/40 uppercase tracking-wider mb-3 block">Niveau</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['L1','L2','L3','M1','M2','D1','D2','D3'] as $l)
                            <button type="button" data-level="{{ $l }}" class="level-btn cc-tag">{{ $l }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" id="level" name="level" required>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════
                 ÉTAPE 3 — Personnalité
            ═══════════════════════════ --}}
            <div id="step-3" class="step-section hidden">
                <div class="text-center mb-8">
                    <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-4"
                         style="background: linear-gradient(135deg,rgba(255,193,69,0.15),rgba(255,94,108,0.1)); border:1px solid rgba(255,193,69,0.2);">
                        <span class="text-3xl">✨</span>
                    </div>
                    <h1 class="text-2xl font-bold">Ta personnalité</h1>
                    <p class="text-white/30 mt-2 text-sm">Ce qui te rend unique 🌟</p>
                </div>

                <div class="space-y-5">
                    {{-- Intérêts --}}
                    <div>
                        <label class="text-xs text-white/40 uppercase tracking-wider mb-3 block">
                            Centres d'intérêt
                            <span id="interest-count" class="text-white/20 normal-case ml-1">(0 sélectionné)</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Sport','Musique','Cinéma','Voyage','Lecture','Jeux vidéo','Cuisine','Tech','Football','Danse','Mode','Entrepreneuriat'] as $int)
                            <button type="button" data-value="{{ $int }}" class="interest-btn cc-tag">{{ $int }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="interests" id="interests">
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label class="text-xs text-white/40 uppercase tracking-wider mb-2 block">
                            Bio
                            <span class="text-white/20 normal-case">
                                (<span id="char-count">0</span>/200)
                            </span>
                        </label>
                        <textarea name="bio" rows="4" maxlength="200" required
                                  placeholder="Parle de toi, de tes passions, de ce que tu cherches..."
                                  class="cc-input resize-none" id="bio"></textarea>
                        {{-- Suggestion de bio --}}
                        <p class="text-[11px] text-white/20 mt-2 leading-relaxed">
                            💡 Ex : "Étudiant en droit à l'UCAD, fan de foot et de bonne musique. Je cherche quelqu'un avec qui partager de bons moments."
                        </p>
                    </div>
                </div>

                {{-- Bouton submit --}}
                <button type="submit" id="save-btn"
                        class="w-full py-4 rounded-2xl font-semibold text-white mt-6 transition hover:-translate-y-0.5 active:scale-[0.98]"
                        style="background: linear-gradient(135deg,#ff5e6c,#ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                    <span id="save-text">🎉 Créer mon profil</span>
                    <span id="save-loading" class="hidden">
                        <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </span>
                </button>
            </div>

        </form>

        {{-- ── Navigation Précédent / Suivant ── --}}
        <div id="step-nav" class="flex gap-3 mt-5">
            <button id="btn-prev"
                    class="hidden flex-1 py-3.5 rounded-2xl font-medium text-white/40 text-sm border transition active:scale-95 hover:bg-white/5"
                    style="border-color: rgba(255,255,255,0.08);"
                    onclick="prevStep()">
                ← Retour
            </button>
            <button id="btn-next"
                    class="flex-1 py-3.5 rounded-2xl font-semibold text-white text-sm transition active:scale-95"
                    style="background: linear-gradient(135deg,rgba(255,94,108,0.8),rgba(255,138,92,0.8)); box-shadow: 0 4px 20px rgba(255,94,108,0.2);"
                    onclick="nextStep()">
                Suivant →
            </button>
        </div>

        {{-- Success state --}}
        <div id="success-state" class="hidden text-center py-8">
            <div class="text-5xl mb-4">🎉</div>
            <h2 class="text-2xl font-bold mb-2">Profil créé !</h2>
            <p class="text-white/40 mb-6">Bonne chance dans tes rencontres</p>
            <a href="{{ route('swipe') }}"
               class="inline-block px-8 py-3 rounded-2xl font-semibold text-white"
               style="background: linear-gradient(135deg,#ff5e6c,#ff8a5c);">
                Commencer à swiper 🔥
            </a>
        </div>
    </div>

    <p class="text-center text-white/20 text-xs mt-6">💕 Campus Crush</p>
</div>
</div>

@push('scripts')
<script>
// ═══════════════════════════════════════════
// CONFIG ÉTAPES
// ═══════════════════════════════════════════
const STEPS = [
    { id: 1, label: 'Photo de profil',  pct: 33.33 },
    { id: 2, label: 'Informations',     pct: 66.66 },
    { id: 3, label: 'Personnalité',     pct: 100   },
];
let currentStep = 1;

// ═══════════════════════════════════════════
// RENDU DE L'ÉTAPE
// ═══════════════════════════════════════════
function renderStep(step) {
    // Sections
    document.querySelectorAll('.step-section').forEach(s => s.classList.add('hidden'));
    document.getElementById('step-' + step).classList.remove('hidden');

    // Progress bar
    document.getElementById('progress-bar').style.width = STEPS[step-1].pct + '%';
    document.getElementById('step-label').textContent   = STEPS[step-1].label;
    document.getElementById('step-counter').textContent = step + ' / ' + STEPS.length;

    // Dots
    STEPS.forEach((s, i) => {
        const dot      = document.getElementById('dot-' + (i+1));
        const dotLabel = document.getElementById('dot-label-' + (i+1));
        const isDone   = (i+1) < step;
        const isActive = (i+1) === step;

        if (isActive) {
            dot.style.cssText = 'background: linear-gradient(135deg,#ff5e6c,#ff8a5c); box-shadow: 0 0 12px rgba(255,94,108,0.4);';
        } else if (isDone) {
            dot.style.cssText = 'background: rgba(255,94,108,0.2); border: 1px solid rgba(255,94,108,0.3);';
            dot.innerHTML = '<svg class="w-3.5 h-3.5 text-[#ff5e6c]" fill="none" stroke="#ff5e6c" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
        } else {
            dot.style.cssText = 'background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);';
        }
        dotLabel.style.color = isActive ? 'rgba(255,255,255,0.6)' : isDone ? 'rgba(255,94,108,0.5)' : 'rgba(255,255,255,0.2)';
    });

    // Boutons nav
    document.getElementById('btn-prev').classList.toggle('hidden', step === 1);
    document.getElementById('btn-next').classList.toggle('hidden', step === STEPS.length);
    document.getElementById('step-nav').style.display = step === STEPS.length ? 'none' : 'flex';

    // Scroll top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ═══════════════════════════════════════════
// NAVIGATION
// ═══════════════════════════════════════════
function nextStep() {
    if (!validateStep(currentStep)) return;
    if (currentStep < STEPS.length) {
        currentStep++;
        renderStep(currentStep);
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        renderStep(currentStep);
    }
}

function validateStep(step) {
    if (step === 1) return true; // Photo optionnelle
    if (step === 2) {
        const age    = document.querySelector('[name="age"]').value;
        const gender = document.querySelector('[name="gender"]').value;
        const ufr    = document.querySelector('[name="ufr"]').value;
        const level  = document.getElementById('level').value;
        if (!age || !gender || !ufr) {
            shakeNext('Complète l\'âge, le genre et l\'UFR 😊');
            return false;
        }
        if (!level) {
            shakeNext('Choisis ton niveau 🎓');
            return false;
        }
    }
    return true;
}

function shakeNext(msg) {
    const btn = document.getElementById('btn-next');
    btn.textContent = msg;
    btn.style.background = 'rgba(239,68,68,0.2)';
    btn.style.border = '1px solid rgba(239,68,68,0.3)';
    setTimeout(() => {
        btn.textContent = 'Suivant →';
        btn.style.background = '';
        btn.style.border = '';
    }, 2000);
}

// ═══════════════════════════════════════════
// PHOTO UPLOAD
// ═══════════════════════════════════════════
document.getElementById('photo').addEventListener('change', function(e) {
    const f = e.target.files[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = ev => {
        const img = document.getElementById('photo-preview-img');
        img.src = ev.target.result;
        img.classList.remove('hidden');
        document.getElementById('photo-overlay').classList.remove('hidden');
        document.getElementById('photo-placeholder').classList.add('hidden');
        document.getElementById('photo-change-label').classList.remove('hidden');
        document.getElementById('photo-drop-zone').style.borderColor = 'rgba(255,94,108,0.3)';
    };
    r.readAsDataURL(f);
});

// ═══════════════════════════════════════════
// NIVEAU
// ═══════════════════════════════════════════
document.querySelectorAll('.level-btn').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('.level-btn').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    document.getElementById('level').value = b.dataset.level;
}));

// ═══════════════════════════════════════════
// INTÉRÊTS
// ═══════════════════════════════════════════
let selectedInterests = [];
document.querySelectorAll('.interest-btn').forEach(b => b.addEventListener('click', () => {
    const v = b.dataset.value;
    if (selectedInterests.includes(v)) {
        selectedInterests = selectedInterests.filter(i => i !== v);
        b.classList.remove('active');
    } else {
        selectedInterests.push(v);
        b.classList.add('active');
    }
    document.getElementById('interests').value = selectedInterests.join(',');
    document.getElementById('interest-count').textContent =
        selectedInterests.length > 0
            ? '(' + selectedInterests.length + ' sélectionné' + (selectedInterests.length > 1 ? 's' : '') + ')'
            : '(0 sélectionné)';
}));

// ═══════════════════════════════════════════
// BIO COUNTER
// ═══════════════════════════════════════════
document.getElementById('bio').addEventListener('input', e => {
    document.getElementById('char-count').textContent = e.target.value.length;
});

// ═══════════════════════════════════════════
// SUBMIT
// ═══════════════════════════════════════════
document.getElementById('profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!document.getElementById('level').value) {
        alert('Choisis ton niveau scolaire.');
        return;
    }
    document.getElementById('save-text').classList.add('hidden');
    document.getElementById('save-loading').classList.remove('hidden');

    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            document.getElementById('profile-form').classList.add('hidden');
            document.getElementById('step-nav').style.display = 'none';
            document.getElementById('progress-header').classList.add('hidden');
            document.getElementById('success-state').classList.remove('hidden');
        } else if (d.errors) {
            alert(Object.values(d.errors).flat().join('\n'));
            document.getElementById('save-text').classList.remove('hidden');
            document.getElementById('save-loading').classList.add('hidden');
        }
    })
    .catch(() => {
        alert('Erreur réseau. Réessaie.');
        document.getElementById('save-text').classList.remove('hidden');
        document.getElementById('save-loading').classList.add('hidden');
    });
});

// ═══════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════
renderStep(1);
</script>
@endpush
@endsection