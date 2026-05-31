@extends('layouts.public')

@section('title', 'Mon questionnaire nutritionnel')

@section('content')
@php
use App\Data\QuestionnaireData;
$totalCanaris = count(QuestionnaireData::$canaris_adulte)
              + count(QuestionnaireData::$canaris_enfant)
              + count(QuestionnaireData::$canaris_contexte) - 1; // ctx1 non compté (profil)
@endphp

<style>
    /* ── Spécifique vue publique questionnaire ──────────────────── */
    .section-icon {
        width: 28px; height: 28px; background: var(--color-bg-tint);
        border-radius: 6px; display: inline-flex; align-items: center;
        justify-content: center; color: var(--color-primary-dark);
        font-size: .9rem; flex-shrink: 0; margin-right: 10px;
    }
    .accordion-button:not(.collapsed) .section-icon { background: rgba(59,148,94,0.15); }
    .q-num   { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-muted); margin-bottom: 4px; }
    .q-label { font-family: 'Outfit', sans-serif; font-size: 13px; color: var(--color-navy); margin-bottom: 8px; }
    .quest-public-title { color: var(--color-navy); font-family: 'Syne', sans-serif; font-weight: 700; }
    /* Boutons pleine largeur — texte long autorisé à wrapper */
    .btn.w-100 {
        white-space: normal;
        height: auto !important;
        min-height: 30px;
        padding-top: 5px;
        padding-bottom: 5px;
        line-height: 1.45;
        align-items: flex-start;
        display: flex;
    }
    /* Boutons Diathèse D2 — bordure visible (override global.css #ECECEC trop clair) */
    .btn-outline-secondary.w-100 {
        border-color: var(--color-primary) !important;
        color: var(--color-navy);
    }
    .btn-check:checked + .btn-outline-secondary.w-100 {
        background: var(--color-primary-dark) !important;
        border-color: var(--color-primary-dark) !important;
        color: var(--color-text-on-green) !important;
        font-weight: 600;
    }
    .btn-outline-primary, .btn-outline-chasseur { text-align: left; }
    .subsection-card { border: none !important; border-radius: var(--radius-card); overflow: hidden; }
    .subsection-card .card-header { display: flex; justify-content: space-between; align-items: center; }
</style>

{{-- En-tête ─────────────────────────────────────────────────────── --}}
<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center rounded mb-3 q-intro-icon">
        <i class="bi bi-clipboard2-pulse fs-2 text-green-dark"></i>
    </div>
    <h1 class="h3 fw-bold mb-1 quest-public-title">Questionnaire nutritionnel</h1>
    <p class="text-muted-pa mb-0">Répondez à votre rythme — vos réponses sont sauvegardées automatiquement.</p>
    <p class="text-muted-pa fs-13 mt-1">Cliquez sur <strong>Soumettre</strong> quand vous avez terminé.</p>
</div>

{{-- Progression globale ─────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body py-3 d-flex align-items-center gap-3">
        <span class="text-nowrap fs-12 text-muted-pa fw-medium">
            <i class="bi bi-check2-circle me-1 text-green-dark"></i>Ma progression
        </span>
        <div class="progress on-panel flex-grow-1">
            <div class="progress-bar" id="globalBar" role="progressbar" style="width:0%;"></div>
        </div>
        <span class="text-nowrap fw-semibold pub-progress-lbl" id="globalLabel">0 / 0</span>
    </div>
</div>

{{-- Statut sauvegarde ───────────────────────────────────────────── --}}
<div class="d-flex align-items-center gap-2 mb-3">
    <span class="pub-save-status" id="saveStatus">
        @if($questionnaire->updated_at)
            <i class="bi bi-cloud-check me-1 text-green-dark"></i>
            Dernière sauvegarde : {{ $questionnaire->updated_at->format('d/m/Y à H:i') }}
        @else
            <i class="bi bi-cloud me-1"></i>Pas encore sauvegardé
        @endif
    </span>
    <span class="spinner-border spinner-border-sm d-none text-green-dark" id="saveSpinner" role="status"></span>
</div>

<form id="questForm">
    @csrf

    @php $sNum = 0; @endphp

    {{-- FICHE D'IDENTITÉ ──────────────────────────────────────────── --}}
    <div class="mb-3">
        <h2 class="sub-header">
            <i class="bi bi-person-vcard me-2"></i>Fiche d'identité
        </h2>
        <p class="fs-12 text-muted-pa mb-3">Renseignez les informations de la personne.</p>
        <div class="card">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="identite_nom">Nom</label>
                        <input type="text" name="identite_nom" id="identite_nom" class="form-control"
                               value="{{ $answers['identite_nom'] ?? $questionnaire->client->nom ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_prenom">Prénom</label>
                        <input type="text" name="identite_prenom" id="identite_prenom" class="form-control"
                               value="{{ $answers['identite_prenom'] ?? $questionnaire->client->prenom ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_age">Âge</label>
                        <input type="number" name="identite_age" id="identite_age" class="form-control"
                               min="0" max="120"
                               value="{{ $answers['identite_age'] ?? $questionnaire->client->age ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_sexe">Sexe</label>
                        <input type="text" name="identite_sexe" id="identite_sexe" class="form-control"
                               value="{{ $answers['identite_sexe'] ?? $questionnaire->client->sexe ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_taille">Taille (cm)</label>
                        <input type="number" name="identite_taille" id="identite_taille" class="form-control"
                               min="0" max="300"
                               value="{{ $answers['identite_taille'] ?? $questionnaire->client->taille ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="identite_poids">Poids (kg)</label>
                        <input type="number" name="identite_poids" id="identite_poids" class="form-control"
                               step="0.1" min="0"
                               value="{{ $answers['identite_poids'] ?? $questionnaire->client->poids ?? '' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion d-flex flex-column gap-2" id="questAccordion">

        {{-- SECTION 1 — JULIA ROSS — NEUROTRANSMETTEURS ─────────── --}}
        @if(in_array('julia_ross', $sections))
        @php $sNum++; @endphp
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $sNum > 1 ? 'collapsed' : '' }} fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s1" @if($sNum === 1) aria-expanded="true" @endif>
                    <span class="section-icon"><i class="bi bi-brain"></i></span>
                    {{ $sNum }}. Julia Ross — Neurotransmetteurs
                    <span class="badge-progress ms-3" id="badge-s1">0 cochés</span>
                </button>
            </h2>
            <div id="s1" class="accordion-collapse collapse {{ $sNum === 1 ? 'show' : '' }}" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">
                    <div class="alert-section-info mb-3">
                        Cochez les affirmations qui vous correspondent.
                    </div>
                    @foreach(QuestionnaireData::$julia_ross as $classe)
                    <div class="card mb-3 subsection-card">
                        <div class="card-header">
                            <span>{{ $classe['titre'] }}</span>
                        </div>
                        <div class="card-body py-2 px-3">
                            @foreach($classe['questions'] as $qi => $q)
                            <div class="form-check py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <input class="form-check-input" type="checkbox"
                                       name="{{ $classe['id'] }}_{{ $qi }}" value="1"
                                       id="{{ $classe['id'] }}_{{ $qi }}" data-section="s1"
                                       @checked(!empty($answers[$classe['id'].'_'.$qi]))>
                                <label class="form-check-label form-check-label-navy d-flex justify-content-between" for="{{ $classe['id'] }}_{{ $qi }}">
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
        @endif

        {{-- SECTION 2 — MÉTABOLTYPING ───────────────────────────── --}}
        @if(in_array('metabolique', $sections))
        @php $sNum++; @endphp
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $sNum > 1 ? 'collapsed' : '' }} fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s2" @if($sNum === 1) aria-expanded="true" @endif>
                    <span class="section-icon"><i class="bi bi-activity"></i></span>
                    {{ $sNum }}. Métaboltyping
                    <span class="badge-progress ms-3" id="badge-s2">0 / 37</span>
                </button>
            </h2>
            <div id="s2" class="accordion-collapse collapse {{ $sNum === 1 ? 'show' : '' }}" data-bs-parent="#questAccordion">
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
                                       class="btn-check radio-q" id="{{ $q['id'] }}_a" data-section="s2"
                                       @checked(($answers[$q['id']] ?? '') === 'a')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_a">
                                    <strong class="me-1">A</strong>{{ $q['a'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="b"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_b" data-section="s2"
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
                        <i class="bi bi-check2-square me-2"></i>Symptômes — cochez ce qui vous correspond
                    </p>
                    <div class="row g-2">
                        @foreach(QuestionnaireData::$metabolique_symptomes as $q)
                        <div class="col-md-6">
                            <div class="form-check check-item">
                                <input class="form-check-input" type="checkbox" name="{{ $q['id'] }}" value="1"
                                       id="{{ $q['id'] }}" data-section="s2-sym"
                                       @checked(!empty($answers[$q['id']]))>
                                <label class="form-check-label form-check-label-navy" for="{{ $q['id'] }}">{{ $q['label'] }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- SECTION 3 — DIATHÈSES ──────────────────────────────── --}}
        @if(in_array('diathese', $sections))
        @php $sNum++; @endphp
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $sNum > 1 ? 'collapsed' : '' }} fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s3" @if($sNum === 1) aria-expanded="true" @endif>
                    <span class="section-icon"><i class="bi bi-diagram-3"></i></span>
                    {{ $sNum }}. Diathèses
                    <span class="badge-progress ms-3" id="badge-s3">0 / 14</span>
                </button>
            </h2>
            <div id="s3" class="accordion-collapse collapse {{ $sNum === 1 ? 'show' : '' }}" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">
                    <div class="alert-section-info mb-3">
                        Pour chaque paire, choisissez l'option qui vous correspond le mieux. Laissez vide si aucune ne s'applique clairement.
                    </div>

                    <h6 class="sub-header">Période enfance (avant 12–15 ans)</h6>
                    @foreach(QuestionnaireData::$diathese_col1 as $q)
                    <div class="q-row">
                        <div class="q-num mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1" data-section="s3"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d1-label">D1</strong>{{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2" data-section="s3"
                                       @checked(($answers[$q['id']] ?? '') === 'd2')>
                                <label class="btn btn-outline-secondary btn-sm w-100 text-start" for="{{ $q['id'] }}_d2">
                                    <strong class="d1-label">D2</strong>{{ $q['d2'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4 hr-section">
                    <h6 class="sub-header">Période adulte (aujourd'hui)</h6>
                    @foreach(QuestionnaireData::$diathese_col2 as $q)
                    <div class="q-row">
                        <div class="q-num mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1" data-section="s3"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d1-label">D1</strong>{{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2" data-section="s3"
                                       @checked(($answers[$q['id']] ?? '') === 'd2')>
                                <label class="btn btn-outline-secondary btn-sm w-100 text-start" for="{{ $q['id'] }}_d2">
                                    <strong class="d1-label">D2</strong>{{ $q['d2'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- SECTION 4 — AYURVEDA ────────────────────────────────── --}}
        @if(in_array('ayurveda', $sections))
        @php $sNum++; @endphp
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $sNum > 1 ? 'collapsed' : '' }} fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s4" @if($sNum === 1) aria-expanded="true" @endif>
                    <span class="section-icon"><i class="bi bi-yin-yang"></i></span>
                    {{ $sNum }}. Ayurveda
                    <span class="badge-progress ms-3" id="badge-s4">0 / 59</span>
                </button>
            </h2>
            <div id="s4" class="accordion-collapse collapse {{ $sNum === 1 ? 'show' : '' }}" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">
                    <div class="alert-section-info mb-3">
                        Évaluez chaque affirmation de <strong>1</strong> (pas du tout) à <strong>6</strong> (totalement vrai pour moi).
                    </div>

                    <h6 class="sub-header"><i class="bi bi-water me-1"></i>Vâta <small class="sub-hint">(19 questions)</small></h6>
                    @foreach(QuestionnaireData::$vata as $i => $label)
                    <div class="q-row">
                        <div class="q-label mb-2">{{ $i + 1 }}. {{ $label }}</div>
                        <div class="btn-group btn-group-sm" role="group">
                            @for($v = 1; $v <= 6; $v++)
                            <input type="radio" class="btn-check radio-q" name="v{{ $i }}" value="{{ $v }}"
                                   id="v{{ $i }}_{{ $v }}" data-section="s4"
                                   @checked(($answers['v'.$i] ?? '') == $v)>
                            <label class="btn btn-outline-primary" for="v{{ $i }}_{{ $v }}">{{ $v }}</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4 hr-section">
                    <h6 class="sub-header"><i class="bi bi-fire me-1"></i>Pitta <small class="sub-hint">(20 questions)</small></h6>
                    @foreach(QuestionnaireData::$pitta as $i => $label)
                    <div class="q-row">
                        <div class="q-label mb-2">{{ $i + 1 }}. {{ $label }}</div>
                        <div class="btn-group btn-group-sm" role="group">
                            @for($v = 1; $v <= 6; $v++)
                            <input type="radio" class="btn-check radio-q" name="p{{ $i }}" value="{{ $v }}"
                                   id="p{{ $i }}_{{ $v }}" data-section="s4"
                                   @checked(($answers['p'.$i] ?? '') == $v)>
                            <label class="btn btn-outline-pitta" for="p{{ $i }}_{{ $v }}">{{ $v }}</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4 hr-section">
                    <h6 class="sub-header"><i class="bi bi-cloud me-1"></i>Kapha <small class="sub-hint">(20 questions)</small></h6>
                    @foreach(QuestionnaireData::$kapha as $i => $label)
                    <div class="q-row">
                        <div class="q-label mb-2">{{ $i + 1 }}. {{ $label }}</div>
                        <div class="btn-group btn-group-sm" role="group">
                            @for($v = 1; $v <= 6; $v++)
                            <input type="radio" class="btn-check radio-q" name="k{{ $i }}" value="{{ $v }}"
                                   id="k{{ $i }}_{{ $v }}" data-section="s4"
                                   @checked(($answers['k'.$i] ?? '') == $v)>
                            <label class="btn btn-outline-teal" for="k{{ $i }}_{{ $v }}">{{ $v }}</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- SECTION 5 — GROUPE SANGUIN ──────────────────────────── --}}
        @if(in_array('groupe_sanguin', $sections))
        @php $sNum++; @endphp
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $sNum > 1 ? 'collapsed' : '' }} fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s5" @if($sNum === 1) aria-expanded="true" @endif>
                    <span class="section-icon"><i class="bi bi-droplet-half"></i></span>
                    {{ $sNum }}. Groupe sanguin
                    <span class="badge-progress ms-3" id="badge-s5">0 / 1</span>
                </button>
            </h2>
            <div id="s5" class="accordion-collapse collapse {{ $sNum === 1 ? 'show' : '' }}" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">
                    <div class="alert-section-info mb-3">
                        Sélectionnez votre groupe sanguin.
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['O', 'A', 'B', 'AB', 'Je ne sais pas'] as $gs)
                        <input type="radio" name="groupe_sanguin" value="{{ $gs }}"
                               class="btn-check radio-q" id="gs_{{ $loop->index }}" data-section="s5"
                               @checked(($answers['groupe_sanguin'] ?? '') === $gs)>
                        <label class="btn btn-outline-primary btn-sm" for="gs_{{ $loop->index }}">{{ $gs }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- SECTION 6 — BILAN HORMONAL ──────────────────────────── --}}
        @if(in_array('hormones', $sections))
        @php $sNum++; @endphp
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $sNum > 1 ? 'collapsed' : '' }} fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s6" @if($sNum === 1) aria-expanded="true" @endif>
                    <span class="section-icon"><i class="bi bi-heart-pulse"></i></span>
                    {{ $sNum }}. Bilan Hormonal
                    <span class="badge-progress ms-3" id="badge-s6">0 cochés</span>
                </button>
            </h2>
            <div id="s6" class="accordion-collapse collapse {{ $sNum === 1 ? 'show' : '' }}" data-bs-parent="#questAccordion">
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
                                </div>
                                <div class="card-body py-2 px-3">
                                    @foreach($cat['questions'] as $qi => $question)
                                    <div class="form-check py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <input class="form-check-input" type="checkbox"
                                               name="{{ $cat['id'] }}_{{ $qi }}" value="1"
                                               id="{{ $cat['id'] }}_{{ $qi }}" data-section="s6"
                                               @checked(!empty($answers[$cat['id'].'_'.$qi]))>
                                        <label class="form-check-label form-check-label-navy" for="{{ $cat['id'] }}_{{ $qi }}">{{ $question }}</label>
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
        @endif

        {{-- SECTION 7 — CANARIS ──────────────────────────────────── --}}
        @if(in_array('canaris', $sections))
        @php $sNum++; @endphp
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $sNum > 1 ? 'collapsed' : '' }} fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s7" @if($sNum === 1) aria-expanded="true" @endif>
                    <span class="section-icon"><i class="bi bi-feather"></i></span>
                    {{ $sNum }}. Canaris
                    <span class="badge-progress ms-3" id="badge-s7">0 cochés</span>
                </button>
            </h2>
            <div id="s7" class="accordion-collapse collapse {{ $sNum === 1 ? 'show' : '' }}" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">
                    <div class="alert-section-info mb-3">
                        Cochez les symptômes qui vous correspondent. Les items <span class="badge text-bg-warning text-dark fw-semibold" style="font-size:10px;">×2</span> ont un poids double.
                    </div>

                    <div class="mb-4">
                        <div class="fw-semibold fs-13 mb-2">Ce questionnaire concerne :</div>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach(['adulte' => 'Adulte', 'enfant' => 'Enfant', 'les_deux' => 'Les deux'] as $val => $label)
                            <input type="radio" name="ctx1" value="{{ $val }}"
                                   class="btn-check radio-q" id="ctx1_{{ $val }}"
                                   data-section="s7"
                                   @checked(($answers['ctx1'] ?? 'adulte') === $val)>
                            <label class="btn btn-outline-primary btn-sm" for="ctx1_{{ $val }}">{{ $label }}</label>
                            @endforeach
                        </div>
                    </div>

                    <div id="canaris-adulte" class="canaris-profil-block">
                        <div class="fw-semibold fs-13 mb-2 text-navy">Symptômes adulte</div>
                        <div class="row g-2 mb-3">
                            @foreach(QuestionnaireData::$canaris_adulte as $q)
                            <div class="col-md-6">
                                <div class="form-check py-1 px-3 rounded" style="background:var(--color-bg-tint);">
                                    <input class="form-check-input" type="checkbox"
                                           name="{{ $q['id'] }}" value="1"
                                           id="{{ $q['id'] }}" data-section="s7"
                                           @checked(!empty($answers[$q['id']]))>
                                    <label class="form-check-label form-check-label-navy d-flex align-items-center gap-2" for="{{ $q['id'] }}">
                                        <span class="flex-grow-1">{{ $q['texte'] }}</span>
                                        @if($q['poids'] === 2)
                                        <span class="badge text-bg-warning text-dark fw-semibold flex-shrink-0" style="font-size:10px;">×2</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="canaris-enfant" class="canaris-profil-block" style="display:none;">
                        <div class="fw-semibold fs-13 mb-2 text-navy">Symptômes enfant</div>
                        <div class="row g-2 mb-3">
                            @foreach(QuestionnaireData::$canaris_enfant as $q)
                            <div class="col-md-6">
                                <div class="form-check py-1 px-3 rounded" style="background:var(--color-bg-tint);">
                                    <input class="form-check-input" type="checkbox"
                                           name="{{ $q['id'] }}" value="1"
                                           id="{{ $q['id'] }}" data-section="s7"
                                           @checked(!empty($answers[$q['id']]))>
                                    <label class="form-check-label form-check-label-navy d-flex align-items-center gap-2" for="{{ $q['id'] }}">
                                        <span class="flex-grow-1">{{ $q['texte'] }}</span>
                                        @if($q['poids'] === 2)
                                        <span class="badge text-bg-warning text-dark fw-semibold flex-shrink-0" style="font-size:10px;">×2</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <hr class="my-3">
                    <div class="fw-semibold fs-13 mb-3 text-navy">Questions de contexte</div>
                    <div class="d-flex flex-column gap-3">
                        @foreach(QuestionnaireData::$canaris_contexte as $q)
                        @if($q['id'] === 'ctx1') @continue @endif
                        <div>
                            <div class="fs-13 mb-2" style="color:var(--color-navy);">{{ $q['texte'] }}</div>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach($q['options'] as $val => $label)
                                <input type="radio" name="{{ $q['id'] }}" value="{{ $val }}"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_{{ $val }}"
                                       data-section="s7"
                                       @checked(($answers[$q['id']] ?? '') === $val)>
                                <label class="btn btn-outline-primary btn-sm" for="{{ $q['id'] }}_{{ $val }}">{{ $label }}</label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /accordion --}}

    {{-- Menu (lecture seule si menu_visible_client) ─────────────────── --}}
    @if($questionnaire->menu_visible_client && $questionnaire->menu_text)
    <div class="card mt-3">
        <div class="card-body p-4">
            <div class="fw-semibold fs-13 mb-2 text-navy">
                <i class="bi bi-journal-richtext me-2 text-green-dark"></i>Votre plan alimentaire
            </div>
            <div class="menu-text">{!! $questionnaire->menu_text !!}</div>
        </div>
    </div>
    @endif

    {{-- Aliments préférés (si aliments_visible_client) ──────────────── --}}
    @if($questionnaire->aliments_visible_client)
    <div class="card mt-3">
        <div class="card-body p-4">
            <label class="fw-semibold fs-13 mb-2 d-block text-navy" for="aliments_text">
                <i class="bi bi-heart me-2 text-green-dark"></i>Vos 10 aliments préférés
            </label>
            <p class="fs-12 text-muted-pa mb-2">Listez vos aliments préférés, un par ligne.</p>
            <textarea name="aliments_text" id="aliments_text" rows="6" class="form-control"
                      placeholder="Ex : Saumon, Avocat, Myrtilles…">{{ $answers['aliments_text'] ?? $questionnaire->aliments_text ?? '' }}</textarea>
        </div>
    </div>
    @endif

    {{-- Consentement RGPD ─────────────────────────────────────────── --}}
    <div class="card mt-3" id="rgpdBlock">
        <div class="card-body py-3 px-4">
            <div class="form-check d-flex align-items-start gap-3">
                <input class="form-check-input mt-1 flex-shrink-0" type="checkbox"
                       name="rgpd_consent" id="rgpdConsent" value="1"
                       @checked(!empty($answers['rgpd_consent'])) style="width:20px;height:20px;">
                <label class="form-check-label fs-13" for="rgpdConsent">
                    <span class="fw-semibold">Consentement RGPD <span class="text-danger">*</span></span><br>
                    <span class="text-muted-pa">
                        J'accepte que les données renseignées dans ce questionnaire soient traitées
                        par mon conseiller dans le cadre de mon suivi nutritionnel personnalisé,
                        conformément au Règlement Général sur la Protection des Données (RGPD — UE 2016/679).
                        Ces données ne seront pas transmises à des tiers.
                    </span>
                </label>
            </div>
            <div id="rgpdError" class="text-danger fs-12 mt-2 d-none">
                <i class="bi bi-exclamation-circle me-1"></i>Vous devez accepter le consentement RGPD avant de soumettre.
            </div>
        </div>
    </div>

</form>

{{-- Barre de soumission flottante ─────────────────────────────── --}}
<div class="position-fixed bottom-0 start-0 end-0 submit-bar">
    <div class="submit-bar-inner d-flex align-items-center justify-content-between">

        {{-- Gauche : barre de progression + pourcentage --}}
        <div class="submit-bar-progress">
            <div class="progress on-panel flex-grow-1 w-140">
                <div class="progress-bar" id="floatBar" style="width:0%;transition:width .4s ease;"></div>
            </div>
            <span id="floatStatus" class="submit-bar-status">0% complété</span>
        </div>

        {{-- Droite : bouton soumettre --}}
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('questionnaire.public.submit', $token) }}" id="submitForm">
                @csrf
            </form>
            <button type="button" class="btn btn-primary fw-semibold px-4" id="submitBtn"
                    onclick="submitQuestionnaire()">
                <i class="bi bi-send me-2"></i>Soumettre le questionnaire
            </button>
        </div>
    </div>
</div>

{{-- Modal validation sections incomplètes ──────────────────────── --}}
<div class="modal fade" id="validationModal" tabindex="-1" aria-labelledby="validationModalLabel">
    <div class="modal-dialog">
        <div class="modal-content modal-content-rounded">
            <div class="modal-header modal-header-navy">
                <h5 class="modal-title modal-title-syne" id="validationModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Sections incomplètes
                </h5>
            </div>
            <div class="modal-body modal-body-card">
                <p class="fs-13 text-muted-pa mb-2">
                    Les sections suivantes ne contiennent aucune réponse enregistrée :
                </p>
                <ul id="suspectList" class="fs-13 mb-3 ps-3"></ul>
                <p class="fs-13 text-muted-pa mb-0">
                    Souhaitez-vous revenir compléter ces sections, ou soumettre quand même ?
                </p>
            </div>
            <div class="modal-footer modal-footer-card">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Revenir et compléter
                </button>
                <button type="button" class="btn btn-primary" id="forceSubmitBtn">
                    <i class="bi bi-send me-1"></i>Soumettre quand même
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const TOKEN        = @json($token);
    const SAVE_URL     = '/q/' + TOKEN + '/save';
    const VALIDATE_URL = '/q/' + TOKEN + '/validate';
    const CSRF         = document.querySelector('meta[name="csrf-token"]').content;

    const sectionCfg = {};
    @if(in_array('julia_ross', $sections))
    sectionCfg.s1 = { type: 'check', total: null, badgeId: 'badge-s1', suffix: ' cochés' };
    @endif
    @if(in_array('metabolique', $sections))
    sectionCfg.s2        = { type: 'radio', total: 37,   badgeId: 'badge-s2', suffix: ' / 37' };
    sectionCfg['s2-sym'] = { type: 'check', total: null, badgeId: null,        suffix: '' };
    @endif
    @if(in_array('diathese', $sections))
    sectionCfg.s3 = { type: 'radio', total: 14,   badgeId: 'badge-s3', suffix: ' / 14' };
    @endif
    @if(in_array('ayurveda', $sections))
    sectionCfg.s4 = { type: 'radio', total: 59,   badgeId: 'badge-s4', suffix: ' / 59' };
    @endif
    @if(in_array('groupe_sanguin', $sections))
    sectionCfg.s5 = { type: 'radio', total: 1,    badgeId: 'badge-s5', suffix: ' / 1' };
    @endif
    @if(in_array('hormones', $sections))
    sectionCfg.s6 = { type: 'check', total: null, badgeId: 'badge-s6', suffix: ' cochés' };
    @endif
    @if(in_array('canaris', $sections))
    sectionCfg.s7 = { type: 'check', total: null, badgeId: 'badge-s7', suffix: ' cochés' };
    @endif

    const TOTAL_RADIO = Object.values(sectionCfg)
        .reduce((sum, c) => sum + (c.type === 'radio' && c.total ? c.total : 0), 0);

    function countSection(key) {
        const inputs = document.querySelectorAll(`[data-section="${key}"]`);
        if (sectionCfg[key].type === 'radio') {
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
        let answered = 0;
        Object.entries(sectionCfg).forEach(([key, cfg]) => {
            const count = countSection(key);
            if (cfg.badgeId) {
                const badge = document.getElementById(cfg.badgeId);
                if (badge) badge.textContent = count + cfg.suffix;
            }
            if (cfg.type === 'radio') answered += count;
        });
        const pct = TOTAL_RADIO > 0 ? Math.round((answered / TOTAL_RADIO) * 100) : 0;
        document.getElementById('globalBar').style.width = pct + '%';
        document.getElementById('globalLabel').textContent =
            TOTAL_RADIO > 0 ? answered + ' / ' + TOTAL_RADIO : '—';

        const floatBar = document.getElementById('floatBar');
        if (floatBar) floatBar.style.width = pct + '%';

        const status = document.getElementById('floatStatus');
        if (TOTAL_RADIO > 0 && pct === 100) {
            status.textContent = 'Complet — vous pouvez soumettre !';
            status.style.color = 'var(--color-primary-dark)';
            status.style.fontWeight = '600';
        } else {
            status.textContent = pct + '% complété';
            status.style.color  = 'var(--color-text-muted)';
            status.style.fontWeight = '';
        }
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

        fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: new FormData(document.getElementById('questForm')),
        })
        .then(r => r.json())
        .then(d => {
            spinner.classList.add('d-none');
            if (d.saved) {
                status.innerHTML = '<i class="bi bi-cloud-check me-1" style="color:var(--color-primary-dark);"></i>Dernière sauvegarde : ' + d.time;
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

    function copyFormAndSubmit() {
        const src = document.getElementById('questForm');
        const dst = document.getElementById('submitForm');
        dst.querySelectorAll('.q-field').forEach(el => el.remove());
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
    }

    window.submitQuestionnaire = async function () {
        const rgpd = document.getElementById('rgpdConsent');
        if (!rgpd.checked) {
            document.getElementById('rgpdError').classList.remove('d-none');
            document.getElementById('rgpdBlock').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        document.getElementById('rgpdError').classList.add('d-none');

        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Vérification…';

        // 1. Sauvegarde synchrone avant validation
        try {
            await fetch(SAVE_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF },
                body: new FormData(document.getElementById('questForm')),
            });
        } catch (_) { /* continue anyway */ }

        // 2. Validation des sections
        try {
            const res  = await fetch(VALIDATE_URL);
            const data = await res.json();

            if (data.suspectes && data.suspectes.length > 0) {
                document.getElementById('suspectList').innerHTML =
                    data.suspectes.map(s => `<li>${s}</li>`).join('');

                const modal = new bootstrap.Modal(document.getElementById('validationModal'));
                modal.show();

                document.getElementById('forceSubmitBtn').onclick = function () {
                    modal.hide();
                    copyFormAndSubmit();
                };

                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-2"></i>Soumettre le questionnaire';
                return;
            }
        } catch (_) { /* en cas d'erreur réseau, on soumet quand même */ }

        copyFormAndSubmit();
    };

    // Canaris : afficher/masquer blocs adulte/enfant
    function updateCanarisBlocks() {
        const profil = document.querySelector('input[name="ctx1"]:checked')?.value ?? 'adulte';
        const a = document.getElementById('canaris-adulte');
        const e = document.getElementById('canaris-enfant');
        if (a) a.style.display = (profil === 'adulte' || profil === 'les_deux') ? '' : 'none';
        if (e) e.style.display = (profil === 'enfant'  || profil === 'les_deux') ? '' : 'none';
    }
    document.addEventListener('change', function (ev) {
        if (ev.target.name === 'ctx1') updateCanarisBlocks();
    });

    document.addEventListener('change',          () => { updateBadges(); scheduleAutoSave(); });
    document.addEventListener('DOMContentLoaded', () => { updateBadges(); updateCanarisBlocks(); });

    document.querySelectorAll('.accordion-collapse').forEach(el => {
        el.addEventListener('shown.bs.collapse', function () {
            const btn = this.previousElementSibling.querySelector('button');
            const y   = btn.getBoundingClientRect().top + window.scrollY - 8;
            window.scrollTo({ top: y, behavior: 'instant' });
        });
    });
})();
</script>

@endsection
