@extends('layouts.app')

@section('title', 'Questionnaire – ' . $client->nom_complet)

@section('content')
@php use App\Data\QuestionnaireData; @endphp

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="page-title mb-0">
        <i class="bi bi-clipboard2-pulse me-2"></i>Questionnaire nutritionnel
    </h1>
    <span class="badge text-bg-secondary ms-1">{{ $client->nom_complet }}</span>
    @if($questionnaire)
        <span class="badge text-bg-success ms-1">
            <i class="bi bi-check-circle me-1"></i>Enregistré le {{ $questionnaire->updated_at->format('d/m/Y à H:i') }}
        </span>
    @endif
</div>

<form method="POST" action="{{ route('questionnaire.store', $client) }}" id="questForm">
    @csrf

    {{-- Progression globale --}}
    <div class="card mb-3">
        <div class="card-body py-2 d-flex align-items-center gap-3">
            <span class="small fw-semibold text-muted">Progression globale</span>
            <div class="progress flex-grow-1" style="height:8px">
                <div class="progress-bar" id="globalBar" role="progressbar" style="width:0%;background:var(--primary)"></div>
            </div>
            <span class="small fw-semibold" id="globalLabel" style="min-width:60px">0 / 0</span>
        </div>
    </div>

    <div class="accordion" id="questAccordion">

        {{-- ══════════════════════════════════════════════════════════
             SECTION 1 — TYPAGE MÉTABOLIQUE
        ══════════════════════════════════════════════════════════ --}}
        <div class="accordion-item mb-2 border rounded shadow-sm" id="wrap-s1">
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

                    {{-- 37 questions binaires --}}
                    @foreach(QuestionnaireData::$metabolique_binaire as $q)
                    <div class="mb-3 p-3 rounded" style="background:#f8f9fc">
                        <div class="small fw-semibold text-muted mb-2">{{ $loop->iteration }}. {{ $q['label'] }}</div>
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
                                <label class="btn btn-outline-danger btn-sm w-100 text-start" for="{{ $q['id'] }}_b">
                                    <strong class="me-1">B</strong>{{ $q['b'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4">
                    <p class="fw-semibold mb-3"><i class="bi bi-check2-square me-2"></i>Symptômes unilatéraux (Chasseur B) — cochez ce qui vous correspond</p>

                    <div class="row g-2">
                        @foreach(QuestionnaireData::$metabolique_symptomes as $q)
                        <div class="col-md-6">
                            <div class="form-check form-check-card p-3 rounded" style="background:#f8f9fc">
                                <input class="form-check-input" type="checkbox"
                                       name="{{ $q['id'] }}" value="1"
                                       id="{{ $q['id'] }}"
                                       data-section="s1-sym"
                                       @checked(!empty($answers[$q['id']])) >
                                <label class="form-check-label small" for="{{ $q['id'] }}">{{ $q['label'] }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SECTION 2 — AYURVEDA
        ══════════════════════════════════════════════════════════ --}}
        <div class="accordion-item mb-2 border rounded shadow-sm" id="wrap-s2">
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
                        Évaluez chaque affirmation de <strong>1</strong> (peu ou pas du tout) à <strong>6</strong> (totalement vrai pour moi).
                    </div>

                    {{-- Vâta --}}
                    <h6 class="fw-bold mb-3" style="color:var(--primary)">Vâta <small class="text-muted fw-normal">(19 questions · max 114 pts)</small></h6>
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
                    {{-- Pitta --}}
                    <h6 class="fw-bold mb-3" style="color:var(--primary)">Pitta <small class="text-muted fw-normal">(20 questions · max 120 pts)</small></h6>
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
                    {{-- Kapha --}}
                    <h6 class="fw-bold mb-3" style="color:var(--primary)">Kapha <small class="text-muted fw-normal">(20 questions · max 120 pts)</small></h6>
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

        {{-- ══════════════════════════════════════════════════════════
             SECTION 3 — JULIA ROSS
        ══════════════════════════════════════════════════════════ --}}
        <div class="accordion-item mb-2 border rounded shadow-sm" id="wrap-s3">
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
                        Cochez les affirmations qui vous correspondent. Chaque réponse positive contribue au score pondéré de sa classe.
                    </div>

                    @foreach(QuestionnaireData::$julia_ross as $classe)
                    <div class="card mb-3 border">
                        <div class="card-header d-flex justify-content-between align-items-center py-2" style="background:#eef1f8">
                            <span class="fw-semibold small" style="color:var(--primary)">{{ $classe['titre'] }}</span>
                            <span class="badge bg-secondary">Seuil : {{ $classe['seuil'] }}</span>
                        </div>
                        <div class="card-body py-2">
                            @foreach($classe['questions'] as $qi => $q)
                            <div class="form-check py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <input class="form-check-input" type="checkbox"
                                       name="{{ $classe['id'] }}_{{ $qi }}" value="1"
                                       id="{{ $classe['id'] }}_{{ $qi }}"
                                       data-section="s3"
                                       @checked(!empty($answers[$classe['id'].'_'.$qi]))>
                                <label class="form-check-label small d-flex justify-content-between" for="{{ $classe['id'] }}_{{ $qi }}">
                                    <span>{{ $q['t'] }}</span>
                                    <span class="badge bg-light text-muted border ms-2 flex-shrink-0">+{{ $q['w'] }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SECTION 4 — DIATHÈSE DE MÉNÉTRIER
        ══════════════════════════════════════════════════════════ --}}
        <div class="accordion-item mb-2 border rounded shadow-sm" id="wrap-s4">
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
                        Pour chaque paire, choisissez l'option qui vous correspond le mieux. Laissez vide si aucune des deux ne s'applique clairement.
                    </div>

                    {{-- Colonne 1 : Enfance --}}
                    <h6 class="fw-bold mb-3" style="color:var(--primary)"><i class="bi bi-person-standing me-1"></i>Période enfance (avant 12–15 ans)</h6>
                    @foreach(QuestionnaireData::$diathese_col1 as $q)
                    <div class="mb-3 p-3 rounded" style="background:#f8f9fc">
                        <div class="small text-muted fw-semibold mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1"
                                       data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d-block mb-1 text-primary" style="font-size:.7rem">D1</strong>
                                    {{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2"
                                       data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd2')>
                                <label class="btn btn-outline-secondary btn-sm w-100 text-start" for="{{ $q['id'] }}_d2">
                                    <strong class="d-block mb-1 text-secondary" style="font-size:.7rem">D2</strong>
                                    {{ $q['d2'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-4">
                    {{-- Colonne 2 : Adulte --}}
                    <h6 class="fw-bold mb-3" style="color:var(--primary)"><i class="bi bi-person me-1"></i>Période adulte (aujourd'hui)</h6>
                    @foreach(QuestionnaireData::$diathese_col2 as $q)
                    <div class="mb-3 p-3 rounded" style="background:#f8f9fc">
                        <div class="small text-muted fw-semibold mb-2">Question {{ $loop->iteration }}</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d1"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d1"
                                       data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd1')>
                                <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $q['id'] }}_d1">
                                    <strong class="d-block mb-1 text-primary" style="font-size:.7rem">D1</strong>
                                    {{ $q['d1'] }}
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" name="{{ $q['id'] }}" value="d2"
                                       class="btn-check radio-q" id="{{ $q['id'] }}_d2"
                                       data-section="s4"
                                       @checked(($answers[$q['id']] ?? '') === 'd2')>
                                <label class="btn btn-outline-secondary btn-sm w-100 text-start" for="{{ $q['id'] }}_d2">
                                    <strong class="d-block mb-1 text-secondary" style="font-size:.7rem">D2</strong>
                                    {{ $q['d2'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SECTION 5 — BILAN HORMONAL
        ══════════════════════════════════════════════════════════ --}}
        <div class="accordion-item mb-2 border rounded shadow-sm" id="wrap-s5">
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
                                <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background:#eef1f8">
                                    <span class="fw-semibold small" style="color:var(--primary)">{{ $cat['titre'] }}</span>
                                    <span class="badge bg-secondary">/ {{ $cat['max'] }}</span>
                                </div>
                                <div class="card-body py-2">
                                    @foreach($cat['questions'] as $qi => $question)
                                    <div class="form-check py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <input class="form-check-input" type="checkbox"
                                               name="{{ $cat['id'] }}_{{ $qi }}" value="1"
                                               id="{{ $cat['id'] }}_{{ $qi }}"
                                               data-section="s5"
                                               @checked(!empty($answers[$cat['id'].'_'.$qi]))>
                                        <label class="form-check-label small" for="{{ $cat['id'] }}_{{ $qi }}">
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

    {{-- Espace en bas pour le bouton flottant --}}
    <div style="height:80px"></div>
</form>

{{-- Bouton flottant --}}
<div class="position-fixed bottom-0 end-0 p-4" style="z-index:1050">
    <div class="d-flex gap-2 align-items-center bg-white rounded shadow-lg p-2 border">
        <span class="small text-muted px-2" id="floatStatus">Prêt à enregistrer</span>
        <button type="submit" form="questForm" class="btn btn-primary px-4">
            <i class="bi bi-save me-2"></i>Enregistrer
        </button>
        @if($questionnaire)
        <a href="{{ route('questionnaire.bilan', $client) }}" class="btn btn-outline-secondary">
            <i class="bi bi-bar-chart me-1"></i>Voir bilan
        </a>
        @endif
    </div>
</div>

<script>
(function () {
    // Compte les groupes radio répondus et les checkboxes cochées par section
    const sections = {
        s1:     { type: 'radio',    total: 37,  badgeId: 'badge-s1',  suffix: ' / 37 questions' },
        's1-sym':{ type: 'check',   total: 11,  badgeId: null,        suffix: '' },
        s2:     { type: 'radio',    total: 59,  badgeId: 'badge-s2',  suffix: ' / 59 questions' },
        s3:     { type: 'check',    total: null, badgeId: 'badge-s3', suffix: ' cochés' },
        s4:     { type: 'radio',    total: 14,  badgeId: 'badge-s4',  suffix: ' / 14 questions' },
        s5:     { type: 'check',    total: null, badgeId: 'badge-s5', suffix: ' cochés' },
    };

    function countSection(sectionKey) {
        const cfg = sections[sectionKey];
        const inputs = document.querySelectorAll(`[data-section="${sectionKey}"]`);
        if (cfg.type === 'radio') {
            const groups = {};
            inputs.forEach(el => {
                if (!groups[el.name]) groups[el.name] = false;
                if (el.checked) groups[el.name] = true;
            });
            return Object.values(groups).filter(Boolean).length;
        } else {
            return Array.from(inputs).filter(el => el.checked).length;
        }
    }

    function updateBadges() {
        let totalAnswered = 0;
        let totalAll = 37 + 59 + 14; // radio-based totals

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

        const status = document.getElementById('floatStatus');
        if (status) {
            if (pct === 100) {
                status.textContent = '✓ Complet';
                status.className = 'small text-success px-2 fw-semibold';
            } else {
                status.textContent = pct + '% complété';
                status.className = 'small text-muted px-2';
            }
        }
    }

    document.addEventListener('change', updateBadges);
    document.addEventListener('DOMContentLoaded', updateBadges);
})();
</script>

@endsection
