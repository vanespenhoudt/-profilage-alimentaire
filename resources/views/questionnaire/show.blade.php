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
@php
use App\Data\QuestionnaireData;
$totalJuliaRoss  = collect(QuestionnaireData::$julia_ross)->sum(fn($c) => count($c['questions']));
$totalHormones   = collect(QuestionnaireData::$hormones)->sum(fn($c)  => count($c['questions']));
$totalCanaris    = count(QuestionnaireData::$canaris_adulte)
                 + count(QuestionnaireData::$canaris_enfant)
                 + count(QuestionnaireData::$canaris_contexte); // ctx1 profil inclus dans contexte
@endphp

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

        {{-- ══ SECTION 1 — JULIA ROSS — NEUROTRANSMETTEURS ══ --}}
        <div class="accordion-item" id="wrap-s1">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s1" aria-expanded="true">
                    <span class="section-icon"><i class="bi bi-brain"></i></span>
                    1. Julia Ross — Neurotransmetteurs
                    <span class="badge-progress ms-3" id="badge-s1">0 / {{ $totalJuliaRoss }}</span>
                </button>
            </h2>
            <div id="s1" class="accordion-collapse collapse show" data-bs-parent="#questAccordion">
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
                                       data-section="s1"
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

        {{-- ══ SECTION 2 — MÉTABOLTYPING ══ --}}
        <div class="accordion-item" id="wrap-s2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s2">
                    <span class="section-icon"><i class="bi bi-activity"></i></span>
                    2. Métaboltyping
                    <span class="badge-progress ms-3" id="badge-s2">0 / 37</span>
                </button>
            </h2>
            <div id="s2" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
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
                                       data-section="s2"
                                       @checked(($answers[$q['id']] ?? '') === 'a')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_a">
                                    <strong class="me-1">A</strong>{{ $q['a'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="b"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_b"
                                       data-section="s2"
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
                                       data-section="s2-sym"
                                       @checked(!empty($answers[$q['id']])) >
                                <label class="form-check-label form-check-label-navy" for="{{ $q['id'] }}">{{ $q['label'] }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        {{-- ══ SECTION 3 — DIATHÈSES ══ --}}
        <div class="accordion-item" id="wrap-s3">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s3">
                    <span class="section-icon"><i class="bi bi-diagram-3"></i></span>
                    3. Diathèses
                    <span class="badge-progress ms-3" id="badge-s3">0 / 14</span>
                </button>
            </h2>
            <div id="s3" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">

                    <div class="alert-section-info mb-3">
                        Pour chaque paire, choisissez l'option qui vous correspond le mieux. Laissez vide si aucune des deux ne s'applique clairement.
                    </div>

                    <h6 class="sub-header"><i class="bi bi-person-standing me-1"></i>Période enfance (avant 12–15 ans)</h6>
                    @foreach(QuestionnaireData::$diathese_col1 as $q)
                    <div class="q-row">
                        <div class="q-num mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1"
                                       data-section="s3"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d1-label">D1</strong>
                                    {{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2"
                                       data-section="s3"
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
                    <h6 class="sub-header"><i class="bi bi-person me-1"></i>Période adulte (aujourd'hui)</h6>
                    @foreach(QuestionnaireData::$diathese_col2 as $q)
                    <div class="q-row">
                        <div class="q-num mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1"
                                       data-section="s3"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d1-label">D1</strong>
                                    {{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2"
                                       data-section="s3"
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

        {{-- ══ SECTION 4 — AYURVEDA ══ --}}
        <div class="accordion-item" id="wrap-s4">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s4">
                    <span class="section-icon"><i class="bi bi-yin-yang"></i></span>
                    4. Ayurveda
                    <span class="badge-progress ms-3" id="badge-s4">0 / 59</span>
                </button>
            </h2>
            <div id="s4" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
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
                                   id="v{{ $i }}_{{ $v }}" data-section="s4"
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
                                   id="p{{ $i }}_{{ $v }}" data-section="s4"
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

        {{-- ══ SECTION 5 — GROUPE SANGUIN ══ --}}
        <div class="accordion-item" id="wrap-s5">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s5">
                    <span class="section-icon"><i class="bi bi-droplet-half"></i></span>
                    5. Groupe sanguin
                    <span class="badge-progress ms-3" id="badge-s5">0 / 1</span>
                </button>
            </h2>
            <div id="s5" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">

                    <div class="alert-section-info mb-3">
                        Sélectionnez le groupe sanguin du client.
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['O', 'A', 'B', 'AB', 'Je ne sais pas'] as $gs)
                        <input type="radio" name="groupe_sanguin" value="{{ $gs }}"
                               class="btn-check radio-q" id="gs_{{ $loop->index }}"
                               data-section="s5"
                               @checked(($answers['groupe_sanguin'] ?? '') === $gs)>
                        <label class="btn btn-outline-primary btn-sm" for="gs_{{ $loop->index }}">{{ $gs }}</label>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        {{-- ══ SECTION 6 — BILAN HORMONAL ══ --}}
        <div class="accordion-item" id="wrap-s6">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s6">
                    <span class="section-icon"><i class="bi bi-heart-pulse"></i></span>
                    6. Bilan Hormonal
                    <span class="badge-progress ms-3" id="badge-s6">0 / {{ $totalHormones }}</span>
                </button>
            </h2>
            <div id="s6" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
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
                                               data-section="s6"
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

        {{-- ══ SECTION 7 — CANARIS ══ --}}
        <div class="accordion-item" id="wrap-s7">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#s7">
                    <span class="section-icon"><i class="bi bi-feather"></i></span>
                    7. Canaris
                    <span class="badge-progress ms-3" id="badge-s7">0 / {{ $totalCanaris }}</span>
                </button>
            </h2>
            <div id="s7" class="accordion-collapse collapse" data-bs-parent="#questAccordion">
                <div class="accordion-body pt-2 pb-4">

                    {{-- Profil --}}
                    <div class="alert-section-info mb-3">
                        Cochez les symptômes présents. Les items marqués <span class="badge text-bg-warning text-dark fw-semibold" style="font-size:10px;">×2</span> ont un poids double.
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

                    {{-- Symptômes adulte --}}
                    <div id="canaris-adulte" class="canaris-profil-block">
                        <div class="fw-semibold fs-13 mb-2 text-navy">Symptômes adulte</div>
                        <div class="row g-2 mb-3">
                            @foreach(QuestionnaireData::$canaris_adulte as $q)
                            <div class="col-md-6">
                                <div class="form-check py-1 px-3 rounded" style="background:var(--color-bg-tint);">
                                    <input class="form-check-input" type="checkbox"
                                           name="{{ $q['id'] }}" value="1"
                                           id="{{ $q['id'] }}"
                                           data-section="s7"
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

                    {{-- Symptômes enfant --}}
                    <div id="canaris-enfant" class="canaris-profil-block" style="display:none;">
                        <div class="fw-semibold fs-13 mb-2 text-navy">Symptômes enfant</div>
                        <div class="row g-2 mb-3">
                            @foreach(QuestionnaireData::$canaris_enfant as $q)
                            <div class="col-md-6">
                                <div class="form-check py-1 px-3 rounded" style="background:var(--color-bg-tint);">
                                    <input class="form-check-input" type="checkbox"
                                           name="{{ $q['id'] }}" value="1"
                                           id="{{ $q['id'] }}"
                                           data-section="s7"
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

                    {{-- Séparateur contexte --}}
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

    </div>{{-- /accordion --}}

    <div class="spacer-bottom"></div>
</form>

{{-- MENU 5 JOURS ──────────────────────────────────────────────── --}}
<div class="mt-3 mb-3">
    <div class="card">
        <div class="section-header">
            <i class="bi bi-journal-richtext"></i>
            <span>Menu 5 jours</span>
        </div>
        <div class="card-body p-4">

            @if($questionnaire?->menu_file)
            <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded" style="background:var(--color-bg-tint);">
                <i class="bi bi-file-earmark-text fs-4 text-green-dark"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold fs-13">{{ $questionnaire->menu_file_name }}</div>
                    <div class="fs-12 text-muted-pa">Fichier attaché</div>
                </div>
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($questionnaire->menu_file) }}"
                   target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-1"></i>Télécharger
                </a>
            </div>
            @endif

            <form method="POST" action="{{ route('questionnaire.menu.save', $client) }}"
                  enctype="multipart/form-data" id="menuForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Menu / Plan alimentaire</label>
                    <x-tiptap-editor name="menu_text" :value="$questionnaire?->menu_text ?? ''" />
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Joindre un fichier
                        <span class="text-muted-pa fw-normal fs-12 ms-1">(PDF, TXT, DOC, DOCX, JPG — max 10 Mo)</span>
                    </label>
                    <input type="file" name="menu_file" class="form-control form-control-sm"
                           accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg">
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i>Enregistrer le menu
                </button>
            </form>
        </div>
    </div>
</div>

{{-- 10 ALIMENTS PRÉFÉRÉS ─────────────────────────────────────────── --}}
<div class="mt-3 mb-3">
    <div class="card">
        <div class="section-header">
            <i class="bi bi-heart"></i>
            <span>10 aliments préférés</span>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('questionnaire.aliments.save', $client) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Quels sont vos 10 aliments préférés ?</label>
                    <textarea name="aliments_text" rows="6"
                              class="form-control"
                              placeholder="Listez les aliments préférés du client, un par ligne...">{{ $questionnaire?->aliments_text ?? '' }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i>Enregistrer
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Barre flottante bas ──────────────────────────────────────────── --}}
<div class="position-fixed bottom-0 end-0 p-4 zi-1050">
    <div class="d-flex gap-2 align-items-center float-bar">
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
        s1:      { type: 'check', total: @json($totalJuliaRoss),      badgeId: 'badge-s1' },
        s2:      { type: 'radio', total: 37,                          badgeId: 'badge-s2' },
        's2-sym':{ type: 'check', total: 11,                          badgeId: null },
        s3:      { type: 'radio', total: 14,                          badgeId: 'badge-s3' },
        s4:      { type: 'radio', total: 59,                          badgeId: 'badge-s4' },
        s5:      { type: 'radio', total: 1,                           badgeId: 'badge-s5' },
        s6:      { type: 'check', total: @json($totalHormones),        badgeId: 'badge-s6' },
        s7:      { type: 'mixed', total: @json($totalCanaris),         badgeId: 'badge-s7' },
    };

    // Canaris : show/hide adulte / enfant blocks
    function updateCanarisBlocks() {
        const profil = document.querySelector('input[name="ctx1"]:checked')?.value ?? 'adulte';
        document.getElementById('canaris-adulte').style.display = (profil === 'adulte' || profil === 'les_deux') ? '' : 'none';
        document.getElementById('canaris-enfant').style.display = (profil === 'enfant' || profil === 'les_deux') ? '' : 'none';
    }
    document.addEventListener('change', function (e) {
        if (e.target.name === 'ctx1') updateCanarisBlocks();
    });
    document.addEventListener('DOMContentLoaded', updateCanarisBlocks);

    function countSection(key) {
        const cfg    = sections[key];
        const inputs = document.querySelectorAll(`[data-section="${key}"]`);
        if (cfg.type === 'radio' || cfg.type === 'mixed') {
            const groups = {};
            inputs.forEach(el => {
                if (el.type === 'radio') {
                    if (!groups[el.name]) groups[el.name] = false;
                    if (el.checked) groups[el.name] = true;
                }
            });
            const radioCount = Object.values(groups).filter(Boolean).length;
            if (cfg.type === 'mixed') {
                const checkCount = Array.from(inputs).filter(el => el.type === 'checkbox' && el.checked).length;
                return radioCount + checkCount;
            }
            return radioCount;
        }
        return Array.from(inputs).filter(el => el.checked).length;
    }

    function updateBadges() {
        let totalAnswered = 0;
        const totalAll   = 37 + 59 + 14 + 1;

        Object.entries(sections).forEach(([key, cfg]) => {
            const count = countSection(key);
            if (cfg.badgeId) {
                const badge = document.getElementById(cfg.badgeId);
                if (badge) badge.textContent = count + ' / ' + cfg.total;
            }
            if (cfg.type === 'radio') totalAnswered += count;
        });

        const pct = totalAll > 0 ? Math.round((totalAnswered / totalAll) * 100) : 0;
        document.getElementById('globalBar').style.width = pct + '%';
        document.getElementById('globalLabel').textContent = totalAnswered + ' / ' + totalAll;
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

    // Allow deselecting radio buttons by clicking the already-selected option again
    document.addEventListener('mousedown', function(e) {
        const label = e.target.closest('label.btn');
        if (!label) return;
        const input = document.getElementById(label.getAttribute('for'));
        if (input && input.type === 'radio') {
            label.dataset.wasChecked = input.checked ? 'true' : 'false';
        }
    });
    document.addEventListener('click', function(e) {
        const label = e.target.closest('label.btn');
        if (!label) return;
        const input = document.getElementById(label.getAttribute('for'));
        if (!input || input.type !== 'radio') return;
        if (label.dataset.wasChecked === 'true') {
            e.preventDefault(); // block browser from forwarding click to input (would re-check it)
            input.checked = false;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        delete label.dataset.wasChecked;
    });

    document.addEventListener('change', function () { updateBadges(); triggerSave(); });
    document.addEventListener('input',  function (e) {
        if (e.target.matches('input[type="text"],input[type="number"],textarea')) triggerSave();
    });
    document.addEventListener('DOMContentLoaded', updateBadges);
})();
</script>

@endsection
