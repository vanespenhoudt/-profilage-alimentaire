@extends('layouts.public')

@section('title', 'Mon questionnaire nutritionnel')

@section('content')
@php use App\Data\QuestionnaireData; @endphp

{{-- En-tête --}}
<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
         style="width:64px;height:64px;background:#eef1f8">
        <i class="bi bi-clipboard2-pulse fs-2" style="color:var(--primary)"></i>
    </div>
    <h1 class="h3 fw-bold mb-1" style="color:var(--primary)">Questionnaire nutritionnel</h1>
    <p class="text-muted mb-0">Répondez à votre rythme — vos réponses sont sauvegardées automatiquement.</p>
    <p class="text-muted small mt-1">Cliquez sur <strong>Soumettre</strong> quand vous avez terminé.</p>
</div>

{{-- Progression globale --}}
<div class="card mb-4">
    <div class="card-body py-2 d-flex align-items-center gap-3">
        <span class="small fw-semibold text-muted text-nowrap">Ma progression</span>
        <div class="progress flex-grow-1" style="height:10px">
            <div class="progress-bar" id="globalBar" role="progressbar" style="width:0%;background:var(--primary)"></div>
        </div>
        <span class="small fw-semibold text-nowrap" id="globalLabel">0 / 0</span>
    </div>
</div>

{{-- Statut sauvegarde --}}
<div class="d-flex align-items-center gap-2 mb-3">
    <span class="small text-muted" id="saveStatus">
        @if($questionnaire->updated_at)
            Dernière sauvegarde : {{ $questionnaire->updated_at->format('d/m/Y à H:i') }}
        @else
            Pas encore sauvegardé
        @endif
    </span>
    <span class="spinner-border spinner-border-sm text-muted d-none" id="saveSpinner" role="status"></span>
</div>

<form id="questForm">
    @csrf

    <div class="accordion" id="questAccordion">

        {{-- SECTION 1 — TYPAGE MÉTABOLIQUE --}}
        <div class="accordion-item mb-2 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s1" aria-expanded="true">
                    <i class="bi bi-activity me-2" style="color:var(--primary)"></i>
                    1. Typage Métabolique
                    <span class="badge ms-3 bg-secondary" id="badge-s1">0 / 37 questions</span>
                </button>
            </h2>
            <div id="s1" class="accordion-collapse collapse show" data-bs-parent="#questAccordion">
                <div class="accordion-body">
                    <div class="alert alert-light border mb-3 py-2 small">
                        <strong>A = Cueilleur</strong> · <strong>B = Chasseur</strong> · Laissez vide si aucune option ne vous correspond.
                    </div>

                    @foreach(QuestionnaireData::$metabolique_binaire as $q)
                    <div class="mb-3 p-3 rounded" style="background:#f8f9fc">
                        <div class="small fw-semibold text-muted mb-2">{{ $loop->iteration }}. {{ $q['label'] }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="a"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_a" data-section="s1"
                                       @checked(($answers[$q['id']] ?? '') === 'a')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_a">
                                    <strong class="me-1">A</strong>{{ $q['a'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="b"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_b" data-section="s1"
                                       @checked(($answers[$q['id']] ?? '') === 'b')>
                                <label class="btn btn-outline-danger btn-sm w-100 text-start" for="{{ $q['id'] }}_b">
                                    <strong class="me-1">B</strong>{{ $q['b'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4">
                    <p class="fw-semibold mb-3"><i class="bi bi-check2-square me-2"></i>Symptômes — cochez ce qui vous correspond</p>
                    <div class="row g-2">
                        @foreach(QuestionnaireData::$metabolique_symptomes as $q)
                        <div class="col-md-6">
                            <div class="form-check p-3 rounded" style="background:#f8f9fc">
                                <input class="form-check-input" type="checkbox" name="{{ $q['id'] }}" value="1"
                                       id="{{ $q['id'] }}" data-section="s1-sym"
                                       @checked(!empty($answers[$q['id']]))>
                                <label class="form-check-label small" for="{{ $q['id'] }}">{{ $q['label'] }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2 — AYURVEDA --}}
        <div class="accordion-item mb-2 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s2">
                    <i class="bi bi-yin-yang me-2" style="color:var(--primary)"></i>
                    2. Ayurveda
                    <span class="badge ms-3 bg-secondary" id="badge-s2">0 / 59 questions</span>
                </button>
            </h2>
            <div id="s2" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body">
                    <div class="alert alert-light border mb-3 py-2 small">
                        Évaluez chaque affirmation de <strong>1</strong> (pas du tout) à <strong>6</strong> (totalement vrai pour moi).
                    </div>

                    <h6 class="fw-bold mb-3" style="color:var(--primary)">Vâta <small class="text-muted fw-normal">(19 questions)</small></h6>
                    @foreach(QuestionnaireData::$vata as $i => $label)
                    <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background:#f8f9fc">
                        <span class="small flex-grow-1">{{ $i + 1 }}. {{ $label }}</span>
                        <div class="btn-group btn-group-sm flex-shrink-0" role="group">
                            @for($v = 1; $v <= 6; $v++)
                            <input type="radio" class="btn-check radio-q" name="v{{ $i }}" value="{{ $v }}"
                                   id="v{{ $i }}_{{ $v }}" data-section="s2"
                                   @checked(($answers['v'.$i] ?? '') == $v)>
                            <label class="btn btn-outline-primary" for="v{{ $i }}_{{ $v }}">{{ $v }}</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3" style="color:var(--primary)">Pitta <small class="text-muted fw-normal">(20 questions)</small></h6>
                    @foreach(QuestionnaireData::$pitta as $i => $label)
                    <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background:#f8f9fc">
                        <span class="small flex-grow-1">{{ $i + 1 }}. {{ $label }}</span>
                        <div class="btn-group btn-group-sm flex-shrink-0" role="group">
                            @for($v = 1; $v <= 6; $v++)
                            <input type="radio" class="btn-check radio-q" name="p{{ $i }}" value="{{ $v }}"
                                   id="p{{ $i }}_{{ $v }}" data-section="s2"
                                   @checked(($answers['p'.$i] ?? '') == $v)>
                            <label class="btn btn-outline-warning" for="p{{ $i }}_{{ $v }}">{{ $v }}</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3" style="color:var(--primary)">Kapha <small class="text-muted fw-normal">(20 questions)</small></h6>
                    @foreach(QuestionnaireData::$kapha as $i => $label)
                    <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background:#f8f9fc">
                        <span class="small flex-grow-1">{{ $i + 1 }}. {{ $label }}</span>
                        <div class="btn-group btn-group-sm flex-shrink-0" role="group">
                            @for($v = 1; $v <= 6; $v++)
                            <input type="radio" class="btn-check radio-q" name="k{{ $i }}" value="{{ $v }}"
                                   id="k{{ $i }}_{{ $v }}" data-section="s2"
                                   @checked(($answers['k'.$i] ?? '') == $v)>
                            <label class="btn btn-outline-success" for="k{{ $i }}_{{ $v }}">{{ $v }}</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SECTION 3 — JULIA ROSS --}}
        <div class="accordion-item mb-2 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s3">
                    <i class="bi bi-brain me-2" style="color:var(--primary)"></i>
                    3. Julia Ross
                    <span class="badge ms-3 bg-secondary" id="badge-s3">0 cochés</span>
                </button>
            </h2>
            <div id="s3" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body">
                    <div class="alert alert-light border mb-3 py-2 small">
                        Cochez les affirmations qui vous correspondent.
                    </div>
                    @foreach(QuestionnaireData::$julia_ross as $classe)
                    <div class="card mb-3 border">
                        <div class="card-header py-2" style="background:#eef1f8">
                            <span class="fw-semibold small" style="color:var(--primary)">{{ $classe['titre'] }}</span>
                        </div>
                        <div class="card-body py-2">
                            @foreach($classe['questions'] as $qi => $q)
                            <div class="form-check py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <input class="form-check-input" type="checkbox"
                                       name="{{ $classe['id'] }}_{{ $qi }}" value="1"
                                       id="{{ $classe['id'] }}_{{ $qi }}" data-section="s3"
                                       @checked(!empty($answers[$classe['id'].'_'.$qi]))>
                                <label class="form-check-label small d-flex justify-content-between" for="{{ $classe['id'] }}_{{ $qi }}">
                                    <span>{{ $q['t'] }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SECTION 4 — DIATHÈSE --}}
        <div class="accordion-item mb-2 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s4">
                    <i class="bi bi-diagram-3 me-2" style="color:var(--primary)"></i>
                    4. Diathèse de Ménétrier
                    <span class="badge ms-3 bg-secondary" id="badge-s4">0 / 14 questions</span>
                </button>
            </h2>
            <div id="s4" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body">
                    <div class="alert alert-light border mb-3 py-2 small">
                        Pour chaque paire, choisissez l'option qui vous correspond le mieux. Laissez vide si aucune ne s'applique clairement.
                    </div>

                    <h6 class="fw-bold mb-3" style="color:var(--primary)">Période enfance (avant 12–15 ans)</h6>
                    @foreach(QuestionnaireData::$diathese_col1 as $q)
                    <div class="mb-3 p-3 rounded" style="background:#f8f9fc">
                        <div class="small text-muted fw-semibold mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1" data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d-block mb-1 text-primary" style="font-size:.7rem">D1</strong>{{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2" data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd2')>
                                <label class="btn btn-outline-secondary btn-sm w-100 text-start" for="{{ $q['id'] }}_d2">
                                    <strong class="d-block mb-1 text-secondary" style="font-size:.7rem">D2</strong>{{ $q['d2'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3" style="color:var(--primary)">Période adulte (aujourd'hui)</h6>
                    @foreach(QuestionnaireData::$diathese_col2 as $q)
                    <div class="mb-3 p-3 rounded" style="background:#f8f9fc">
                        <div class="small text-muted fw-semibold mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1" data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d-block mb-1 text-primary" style="font-size:.7rem">D1</strong>{{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2" data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd2')>
                                <label class="btn btn-outline-secondary btn-sm w-100 text-start" for="{{ $q['id'] }}_d2">
                                    <strong class="d-block mb-1 text-secondary" style="font-size:.7rem">D2</strong>{{ $q['d2'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SECTION 5 — BILAN HORMONAL --}}
        <div class="accordion-item mb-2 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s5">
                    <i class="bi bi-droplet-half me-2" style="color:var(--primary)"></i>
                    5. Bilan Hormonal
                    <span class="badge ms-3 bg-secondary" id="badge-s5">0 cochés</span>
                </button>
            </h2>
            <div id="s5" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body">
                    <div class="alert alert-light border mb-3 py-2 small">
                        Cochez les affirmations qui vous correspondent actuellement.
                    </div>
                    <div class="row g-3">
                        @foreach(QuestionnaireData::$hormones as $cat)
                        <div class="col-md-6">
                            <div class="card h-100 border">
                                <div class="card-header py-2" style="background:#eef1f8">
                                    <span class="fw-semibold small" style="color:var(--primary)">{{ $cat['titre'] }}</span>
                                </div>
                                <div class="card-body py-2">
                                    @foreach($cat['questions'] as $qi => $question)
                                    <div class="form-check py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <input class="form-check-input" type="checkbox"
                                               name="{{ $cat['id'] }}_{{ $qi }}" value="1"
                                               id="{{ $cat['id'] }}_{{ $qi }}" data-section="s5"
                                               @checked(!empty($answers[$cat['id'].'_'.$qi]))>
                                        <label class="form-check-label small" for="{{ $cat['id'] }}_{{ $qi }}">{{ $question }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /accordion --}}
</form>

{{-- Bouton flottant Soumettre --}}
<div class="position-fixed bottom-0 start-0 end-0 p-3" style="z-index:1050;background:rgba(240,242,248,.95);border-top:1px solid #dee2e6;backdrop-filter:blur(4px)">
    <div class="d-flex align-items-center justify-content-between" style="max-width:860px;margin:0 auto">
        <div>
            <span class="small text-muted" id="floatStatus">Répondez aux questions puis soumettez.</span>
        </div>
        <form method="POST" action="{{ route('questionnaire.public.submit', $token) }}" id="submitForm">
            @csrf
        </form>
        <button type="button" class="btn btn-primary px-5 fw-semibold" id="submitBtn"
                onclick="submitQuestionnaire()">
            <i class="bi bi-send me-2"></i>Soumettre le questionnaire
        </button>
    </div>
</div>

<script>
(function () {
    const TOKEN  = @json($token);
    const SAVE_URL   = '/q/' + TOKEN + '/save';
    const SUBMIT_URL = '/q/' + TOKEN + '/submit';
    const CSRF   = document.querySelector('meta[name="csrf-token"]').content;

    const sections = {
        s1:      { type: 'radio', total: 37,   badgeId: 'badge-s1', suffix: ' / 37 questions' },
        's1-sym':{ type: 'check', total: null,  badgeId: null,       suffix: '' },
        s2:      { type: 'radio', total: 59,   badgeId: 'badge-s2', suffix: ' / 59 questions' },
        s3:      { type: 'check', total: null,  badgeId: 'badge-s3', suffix: ' cochés' },
        s4:      { type: 'radio', total: 14,   badgeId: 'badge-s4', suffix: ' / 14 questions' },
        s5:      { type: 'check', total: null,  badgeId: 'badge-s5', suffix: ' cochés' },
    };

    function countSection(key) {
        const inputs = document.querySelectorAll(`[data-section="${key}"]`);
        if (sections[key].type === 'radio') {
            const groups = {};
            inputs.forEach(el => {
                if (!groups[el.name]) groups[el.name] = false;
                if (el.checked) groups[el.name] = true;
            });
            return Object.values(groups).filter(Boolean).length;
        }
        return Array.from(inputs).filter(el => el.checked).length;
    }

    function updateBadges() {
        let answered = 0, total = 37 + 59 + 14;
        Object.entries(sections).forEach(([key, cfg]) => {
            const count = countSection(key);
            if (cfg.badgeId) {
                const badge = document.getElementById(cfg.badgeId);
                if (badge) badge.textContent = count + cfg.suffix;
            }
            if (cfg.type === 'radio') answered += count;
        });
        const pct = total > 0 ? Math.round((answered / total) * 100) : 0;
        document.getElementById('globalBar').style.width = pct + '%';
        document.getElementById('globalLabel').textContent = answered + ' / ' + total;
        const status = document.getElementById('floatStatus');
        status.textContent = pct === 100
            ? '✓ Questionnaire complet — vous pouvez soumettre !'
            : pct + '% complété — continuez à répondre.';
    }

    // Auto-sauvegarde AJAX (debounce 2s)
    let saveTimer;
    function scheduleAutoSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(autoSave, 2000);
    }

    function autoSave() {
        const spinner = document.getElementById('saveSpinner');
        const status  = document.getElementById('saveStatus');
        spinner.classList.remove('d-none');

        const data = new FormData(document.getElementById('questForm'));

        fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: data,
        })
        .then(r => r.json())
        .then(d => {
            spinner.classList.add('d-none');
            if (d.saved) {
                status.textContent = 'Dernière sauvegarde : ' + d.time;
                showToast();
            }
        })
        .catch(() => {
            spinner.classList.add('d-none');
            status.textContent = 'Erreur de sauvegarde — vérifiez votre connexion.';
        });
    }

    function showToast() {
        const toast = document.getElementById('saveToast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }

    // Soumission finale
    window.submitQuestionnaire = function () {
        if (!confirm('Êtes-vous sûr de vouloir soumettre votre questionnaire ? Vous ne pourrez plus le modifier.')) return;

        // Copier les réponses dans le form de soumission caché et soumettre
        const src = document.getElementById('questForm');
        const dst = document.getElementById('submitForm');

        // Ajouter tous les champs du questForm dans submitForm
        const existing = dst.querySelectorAll('.q-field');
        existing.forEach(el => el.remove());

        new FormData(src).forEach((val, key) => {
            if (key === '_token') return;
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = key;
            input.value = val;
            input.classList.add('q-field');
            dst.appendChild(input);
        });

        dst.submit();
    };

    document.addEventListener('change', function(e) {
        updateBadges();
        scheduleAutoSave();
    });

    document.addEventListener('DOMContentLoaded', updateBadges);
})();
</script>

@endsection
