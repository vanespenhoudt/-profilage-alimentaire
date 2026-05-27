@extends('layouts.app')

@section('title', 'Questionnaire – ' . $client->nom_complet)

{{-- ── Zone verte : titre client + progression ──────────────────────── --}}
@section('green-header')
<div class="q-header-inner">
    <div class="q-header-actions">
        <a href="{{ route('clients.show', $client) }}" class="btn-white-outline">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="q-title font-syne fw-bold">
            <i class="bi bi-clipboard2-pulse me-2"></i>Questionnaire nutritionnel
        </h1>
        <span class="q-header-badge">{{ $client->nom_complet }}</span>
        <span class="q-save-status" id="saveStatus">
            @if($questionnaire?->updated_at)
                <i class="bi bi-cloud-check"></i>
                Sauvegardé le {{ $questionnaire->updated_at->format('d/m/Y à H:i') }}
            @else
                <i class="bi bi-cloud"></i>Pas encore sauvegardé
            @endif
        </span>
        <span class="spinner-border spinner-border-sm text-white d-none" id="saveSpinner" role="status"></span>
    </div>
    {{-- Barre de progression globale dans la zone verte --}}
    <div class="q-progress-row">
        <div class="progress on-green flex-grow-1">
            <div class="progress-bar" id="globalBar" role="progressbar" style="width:0%;"></div>
        </div>
        <span class="q-progress-label" id="globalLabel">0 / 0</span>
    </div>
</div>
@endsection

@section('content')
@php use App\Data\QuestionnaireData; @endphp

<style>
    /* ── Spécifique vue interne questionnaire ──────────────────────── */
    .section-icon {
        width: 28px; height: 28px; background: var(--color-bg-tint);
        border-radius: 6px; display: inline-flex; align-items: center;
        justify-content: center; color: var(--color-primary-dark);
        font-size: .9rem; flex-shrink: 0; margin-right: 10px;
    }
    .accordion-button:not(.collapsed) .section-icon { background: rgba(59,148,94,0.15); }
    .accordion-button { padding: 14px 18px; }
    .q-label { font-family: 'Outfit', sans-serif; font-size: 13px; color: var(--color-navy); margin-bottom: 8px; }
    .q-num   { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-muted); margin-bottom: 4px; }
    .subsection-card { border: none !important; border-radius: var(--radius-card); overflow: hidden; }
    .subsection-card .card-header { display: flex; justify-content: space-between; align-items: center; }
    .float-bar { background: var(--color-bg-card); border-top: 1px solid var(--color-border-card); border-radius: var(--radius-card); padding: 12px 16px; }
    /* Boutons Diathèse D2 — sélection = navy */
    .btn-check:checked + .btn-outline-secondary {
        background: var(--color-navy); border-color: var(--color-navy); color: var(--color-text-on-green); font-weight: 600;
    }
    /* Boutons métabolique — text-align left */
    .btn-outline-primary, .btn-outline-chasseur { text-align: left; }
</style>

<form method="POST" action="{{ route('questionnaire.store', $client) }}" id="questForm">
    @csrf

    {{-- FICHE D'IDENTITÉ ──────────────────────────────────────────── --}}
    <div class="mb-3">
        <h2 class="sub-header mb-1">
            <i class="bi bi-person-vcard me-2"></i>Fiche d'identité
        </h2>
        <p class="q-section-desc">Renseignez les informations du client.</p>
        <div class="card">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="identite_nom">Nom</label>
                        <input type="text" name="identite_nom" id="identite_nom" class="form-control"
                               value="{{ $client->nom ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_prenom">Prénom</label>
                        <input type="text" name="identite_prenom" id="identite_prenom" class="form-control"
                               value="{{ $client->prenom ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_age">Âge</label>
                        <input type="number" name="identite_age" id="identite_age" class="form-control"
                               min="0" max="120" value="{{ $client->age ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_sexe">Sexe</label>
                        <input type="text" name="identite_sexe" id="identite_sexe" class="form-control"
                               value="{{ $client->sexe ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_taille">Taille (cm)</label>
                        <input type="number" name="identite_taille" id="identite_taille" class="form-control"
                               min="0" max="300" value="{{ $client->taille ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_poids">Poids (kg)</label>
                        <input type="number" name="identite_poids" id="identite_poids" class="form-control"
                               step="0.1" min="0" value="{{ $client->poids ?? '' }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="identite_sentinelles">
                            Sentinelles <span class="note-label">(notes internes conseiller)</span>
                        </label>
                        <textarea name="identite_sentinelles" id="identite_sentinelles" class="form-control"
                                  rows="2" placeholder="Alertes cliniques, signaux à surveiller...">{{ $client->sentinelles ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACCORDÉON ──────────────────────────────────────────────────── --}}
    <div class="accordion d-flex flex-column gap-2" id="questAccordion">

        {{-- ══ SECTION 1 — TYPAGE MÉTABOLIQUE ══ --}}
        <div class="accordion-item" id="wrap-s1">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s1" aria-expanded="true">
                    <span class="section-icon"><i class="bi bi-activity"></i></span>
                    1. Typage Métabolique
                    <span class="badge-progress ms-3" id="badge-s1">0 / 37 questions</span>
                </button>
            </h2>
            <div id="s1" class="accordion-collapse collapse show" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">

                    <div class="alert-section-info mb-3">
                        <strong>A = Cueilleur</strong> · <strong>B = Chasseur</strong> · Laissez vide si aucune option ne vous correspond.
                    </div>

                    @foreach(QuestionnaireData::$metabolique_binaire as $q)
                    <div class="q-row">
                        <div class="q-num">{{ $loop->iteration }}.</div>
                        <div class="q-label mb-2">{{ $q['label'] }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="a"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_a"
                                       data-section="s1"
                                       @checked(($answers[$q['id']] ?? '') === 'a')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_a">
                                    <strong class="me-1">A</strong>{{ $q['a'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="b"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_b"
                                       data-section="s1"
                                       @checked(($answers[$q['id']] ?? '') === 'b')>
                                <label class="btn btn-outline-chasseur btn-sm w-100 text-start" for="{{ $q['id'] }}_b">
                                    <strong class="me-1">B</strong>{{ $q['b'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4 hr-section">
                    <p class="fw-semibold mb-3 sub-header">
                        <i class="bi bi-check2-square me-2"></i>Symptômes unilatéraux (Chasseur B)
                    </p>

                    <div class="row g-2">
                        @foreach(QuestionnaireData::$metabolique_symptomes as $q)
                        <div class="col-md-6">
                            <div class="form-check check-item p-3 rounded">
                                <input class="form-check-input" type="checkbox"
                                       name="{{ $q['id'] }}" value="1"
                                       id="{{ $q['id'] }}"
                                       data-section="s1-sym"
                                       @checked(!empty($answers[$q['id']])) >
                                <label class="form-check-label form-check-label-navy" for="{{ $q['id'] }}">{{ $q['label'] }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        {{-- ══ SECTION 2 — AYURVEDA ══ --}}
        <div class="accordion-item" id="wrap-s2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s2">
                    <span class="section-icon"><i class="bi bi-yin-yang"></i></span>
                    2. Ayurveda
                    <span class="badge-progress ms-3" id="badge-s2">0 / 59 questions</span>
                </button>
            </h2>
            <div id="s2" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">

                    <div class="alert-section-info mb-3">
                        Évaluez chaque affirmation de <strong>1</strong> (peu ou pas du tout) à <strong>6</strong> (totalement vrai pour moi).
                    </div>

                    {{-- Vâta --}}
                    <h6 class="sub-header"><i class="bi bi-water me-1"></i>Vâta <small class="sub-hint">(19 questions · max 114 pts)</small></h6>
                    @foreach(QuestionnaireData::$vata as $i => $label)
                    <div class="q-row">
                        <div class="q-label mb-2">{{ $i + 1 }}. {{ $label }}</div>
                        <div class="btn-group btn-group-sm" role="group">
                            @for($v = 1; $v <= 6; $v++)
                            <input type="radio" class="btn-check radio-q" name="v{{ $i }}" value="{{ $v }}"
                                   id="v{{ $i }}_{{ $v }}" data-section="s2"
                                   @checked(($answers['v'.$i] ?? '') == $v)>
                            <label class="btn btn-outline-primary" for="v{{ $i }}_{{ $v }}">{{ $v }}</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4 hr-section">
                    {{-- Pitta --}}
                    <h6 class="sub-header"><i class="bi bi-fire me-1"></i>Pitta <small class="sub-hint">(20 questions · max 120 pts)</small></h6>
                    @foreach(QuestionnaireData::$pitta as $i => $label)
                    <div class="q-row">
                        <div class="q-label mb-2">{{ $i + 1 }}. {{ $label }}</div>
                        <div class="btn-group btn-group-sm" role="group">
                            @for($v = 1; $v <= 6; $v++)
                            <input type="radio" class="btn-check radio-q" name="p{{ $i }}" value="{{ $v }}"
                                   id="p{{ $i }}_{{ $v }}" data-section="s2"
                                   @checked(($answers['p'.$i] ?? '') == $v)>
                            <label class="btn btn-outline-pitta" for="p{{ $i }}_{{ $v }}">{{ $v }}</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4 hr-section">
                    {{-- Kapha --}}
                    <h6 class="sub-header"><i class="bi bi-cloud me-1"></i>Kapha <small class="sub-hint">(20 questions · max 120 pts)</small></h6>
                    @foreach(QuestionnaireData::$kapha as $i => $label)
                    <div class="q-row">
                        <div class="q-label mb-2">{{ $i + 1 }}. {{ $label }}</div>
                        <div class="btn-group btn-group-sm" role="group">
                            @for($v = 1; $v <= 6; $v++)
                            <input type="radio" class="btn-check radio-q" name="k{{ $i }}" value="{{ $v }}"
                                   id="k{{ $i }}_{{ $v }}" data-section="s2"
                                   @checked(($answers['k'.$i] ?? '') == $v)>
                            <label class="btn btn-outline-teal" for="k{{ $i }}_{{ $v }}">{{ $v }}</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- ══ SECTION 3 — JULIA ROSS ══ --}}
        <div class="accordion-item" id="wrap-s3">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s3">
                    <span class="section-icon"><i class="bi bi-brain"></i></span>
                    3. Julia Ross
                    <span class="badge-progress ms-3" id="badge-s3">0 cochés</span>
                </button>
            </h2>
            <div id="s3" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">

                    <div class="alert-section-info mb-3">
                        Cochez les affirmations qui vous correspondent. Chaque réponse positive contribue au score pondéré de sa classe.
                    </div>

                    @foreach(QuestionnaireData::$julia_ross as $classe)
                    <div class="card mb-3 subsection-card">
                        <div class="card-header">
                            <span>{{ $classe['titre'] }}</span>
                            <span class="badge-tint">Seuil : {{ $classe['seuil'] }}</span>
                        </div>
                        <div class="card-body py-2 px-3">
                            @foreach($classe['questions'] as $qi => $q)
                            <div class="form-check py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <input class="form-check-input" type="checkbox"
                                       name="{{ $classe['id'] }}_{{ $qi }}" value="1"
                                       id="{{ $classe['id'] }}_{{ $qi }}"
                                       data-section="s3"
                                       @checked(!empty($answers[$classe['id'].'_'.$qi]))>
                                <label class="form-check-label form-check-label-navy d-flex justify-content-between" for="{{ $classe['id'] }}_{{ $qi }}">
                                    <span>{{ $q['t'] }}</span>
                                    <span class="badge-tint-bordered">+{{ $q['w'] }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- ══ SECTION 4 — DIATHÈSE DE MÉNÉTRIER ══ --}}
        <div class="accordion-item" id="wrap-s4">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s4">
                    <span class="section-icon"><i class="bi bi-diagram-3"></i></span>
                    4. Diathèse de Ménétrier
                    <span class="badge-progress ms-3" id="badge-s4">0 / 14 questions</span>
                </button>
            </h2>
            <div id="s4" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">

                    <div class="alert-section-info mb-3">
                        Pour chaque paire, choisissez l'option qui vous correspond le mieux. Laissez vide si aucune des deux ne s'applique clairement.
                    </div>

                    {{-- Colonne 1 --}}
                    <h6 class="sub-header"><i class="bi bi-person-standing me-1"></i>Période enfance (avant 12–15 ans)</h6>
                    @foreach(QuestionnaireData::$diathese_col1 as $q)
                    <div class="q-row">
                        <div class="q-num mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1"
                                       data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d1-label">D1</strong>
                                    {{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2"
                                       data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd2')>
                                <label class="btn btn-outline-secondary btn-sm w-100 text-start" for="{{ $q['id'] }}_d2">
                                    <strong class="d1-label">D2</strong>
                                    {{ $q['d2'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4 hr-section">
                    {{-- Colonne 2 --}}
                    <h6 class="sub-header"><i class="bi bi-person me-1"></i>Période adulte (aujourd'hui)</h6>
                    @foreach(QuestionnaireData::$diathese_col2 as $q)
                    <div class="q-row">
                        <div class="q-num mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1"
                                       data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d1-label">D1</strong>
                                    {{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2"
                                       data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd2')>
                                <label class="btn btn-outline-secondary btn-sm w-100 text-start" for="{{ $q['id'] }}_d2">
                                    <strong class="d1-label">D2</strong>
                                    {{ $q['d2'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- ══ SECTION 5 — BILAN HORMONAL ══ --}}
        <div class="accordion-item" id="wrap-s5">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s5">
                    <span class="section-icon"><i class="bi bi-droplet-half"></i></span>
                    5. Bilan Hormonal
                    <span class="badge-progress ms-3" id="badge-s5">0 cochés</span>
                </button>
            </h2>
            <div id="s5" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">

                    <div class="alert-section-info mb-3">
                        Cochez les affirmations qui vous correspondent actuellement.
                    </div>

                    <div class="row g-3">
                        @foreach(QuestionnaireData::$hormones as $cat)
                        <div class="col-md-6">
                            <div class="card h-100 subsection-card">
                                <div class="card-header">
                                    <span>{{ $cat['titre'] }}</span>
                                    <span class="badge-tint">/ {{ $cat['max'] }}</span>
                                </div>
                                <div class="card-body py-2 px-3">
                                    @foreach($cat['questions'] as $qi => $question)
                                    <div class="form-check py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <input class="form-check-input" type="checkbox"
                                               name="{{ $cat['id'] }}_{{ $qi }}" value="1"
                                               id="{{ $cat['id'] }}_{{ $qi }}"
                                               data-section="s5"
                                               @checked(!empty($answers[$cat['id'].'_'.$qi]))>
                                        <label class="form-check-label form-check-label-navy" for="{{ $cat['id'] }}_{{ $qi }}">
                                            {{ $question }}
                                        </label>
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

    <div class="spacer-bottom"></div>
</form>

{{-- Barre flottante bas ──────────────────────────────────────────── --}}
<div class="position-fixed bottom-0 end-0 p-4 zi-1050">
    <div class="d-flex gap-2 align-items-center float-bar">

        {{-- Progression mini --}}
        <div class="float-bar-inner">
            <div class="progress on-panel flex-grow-1 w-120">
                <div class="progress-bar" id="floatBar" style="width:0%;transition:width .4s ease;"></div>
            </div>
            <span id="floatStatus" class="float-bar-status">0%</span>
        </div>

        {{-- Boutons --}}
        @if($questionnaire)
        <a href="{{ route('questionnaire.bilan', $client) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-bar-chart me-1"></i>Voir bilan
        </a>
        @endif
        <button type="submit" form="questForm" class="btn btn-primary btn-sm">
            <i class="bi bi-save me-1"></i>Enregistrer
        </button>
    </div>
</div>

<script>
(function () {
    const sections = {
        s1:      { type: 'radio', total: 37,  badgeId: 'badge-s1',  suffix: ' / 37 questions' },
        's1-sym':{ type: 'check', total: 11,  badgeId: null,        suffix: '' },
        s2:      { type: 'radio', total: 59,  badgeId: 'badge-s2',  suffix: ' / 59 questions' },
        s3:      { type: 'check', total: null, badgeId: 'badge-s3', suffix: ' cochés' },
        s4:      { type: 'radio', total: 14,  badgeId: 'badge-s4',  suffix: ' / 14 questions' },
        s5:      { type: 'check', total: null, badgeId: 'badge-s5', suffix: ' cochés' },
    };

    function countSection(key) {
        const cfg    = sections[key];
        const inputs = document.querySelectorAll(`[data-section="${key}"]`);
        if (cfg.type === 'radio') {
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
        let totalAnswered = 0;
        const totalAll   = 37 + 59 + 14;

        Object.entries(sections).forEach(([key, cfg]) => {
            const count = countSection(key);
            if (cfg.badgeId) {
                const badge = document.getElementById(cfg.badgeId);
                if (badge) badge.textContent = count + cfg.suffix;
            }
            if (cfg.type === 'radio') totalAnswered += count;
        });

        const pct = totalAll > 0 ? Math.round((totalAnswered / totalAll) * 100) : 0;
        document.getElementById('globalBar').style.width = pct + '%';
        document.getElementById('globalLabel').textContent = totalAnswered + ' / ' + totalAll;

        const floatBar = document.getElementById('floatBar');
        if (floatBar) floatBar.style.width = pct + '%';

        const status = document.getElementById('floatStatus');
        if (status) {
            if (pct === 100) {
                status.textContent = 'Complet';
                status.style.color = 'var(--color-primary-dark)';
                status.style.fontWeight = '600';
            } else {
                status.textContent = pct + '% complété';
                status.style.color = 'var(--color-text-muted)';
                status.style.fontWeight = '';
            }
        }
    }

    // Auto-save
    const SAVE_URL = '{{ route('questionnaire.autosave', $client) }}';
    const CSRF     = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let saveTimer  = null;

    function triggerSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(doSave, 2000);
    }

    function doSave() {
        const spinner = document.getElementById('saveSpinner');
        const status  = document.getElementById('saveStatus');
        if (spinner) spinner.classList.remove('d-none');

        fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: new FormData(document.getElementById('questForm')),
        })
        .then(r => r.json())
        .then(data => {
            if (spinner) spinner.classList.add('d-none');
            if (status && data.saved) {
                status.innerHTML = '<i class="bi bi-cloud-check"></i> Sauvegardé à ' + data.time;
            }
        })
        .catch(() => {
            if (spinner) spinner.classList.add('d-none');
            if (status) status.textContent = 'Erreur de sauvegarde';
        });
    }

    document.addEventListener('change', function () { updateBadges(); triggerSave(); });
    document.addEventListener('input',  function (e) {
        if (e.target.matches('input[type="text"],input[type="number"],textarea')) triggerSave();
    });
    document.addEventListener('DOMContentLoaded', updateBadges);
})();
</script>

@endsection
