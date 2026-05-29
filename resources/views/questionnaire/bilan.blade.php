@extends('layouts.app')

@section('title', 'Bilan – ' . $client->nom_complet)

@section('content')
@php
use App\Data\QuestionnaireData;
$scores = $questionnaire->scores;
@endphp

<style>
    /* ── Palette bilan ──────────────────────────────────────────────── */
    /* Variables héritées de variables.css via app.blade.php */

    /* ── Barres de progression ──────────────────────────────────────── */
    .bar-cueilleur { background: var(--color-navy) !important; }
    .bar-chasseur  { background: var(--color-primary) !important; }
    .bar-vata      { background: var(--color-primary) !important; }
    .bar-pitta     { background: var(--color-primary-mid) !important; }
    .bar-kapha     { background: var(--color-primary-dark) !important; }
    .bar-normal    { background: var(--color-primary-dark) !important; }
    .bar-alerte    { background: #dc3545 !important; }

    /* ── Couleurs texte ─────────────────────────────────────────────── */
    .text-cueilleur { color: var(--color-navy) !important; }
    .text-chasseur  { color: var(--color-primary) !important; }
    .text-vata      { color: var(--color-primary) !important; }
    .text-pitta     { color: var(--color-primary-mid) !important; }
    .text-kapha     { color: var(--color-primary-dark) !important; }
    .text-alerte    { color: #dc3545 !important; }
    .text-normal    { color: var(--color-primary-dark) !important; }

    /* ── En-tête de section ─────────────────────────────────────────── */
    .section-header {
        background: var(--color-navy);
        color: var(--color-text-on-green);
        border-radius: var(--radius-card) var(--radius-card) 0 0 !important;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 13px;
    }
    .section-header i { font-size: 1rem; opacity: .9; }

    /* ── Badges types ───────────────────────────────────────────────── */
    .badge-chasseur {
        background: var(--color-primary);
        color: var(--color-navy);
        border-radius: var(--radius-pill);
        padding: 6px 18px;
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 700;
    }
    .badge-cueilleur {
        background: var(--color-navy);
        color: var(--color-text-on-green);
        border-radius: var(--radius-pill);
        padding: 6px 18px;
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 700;
    }
    .badge-mixte {
        background: var(--color-primary-dark);
        color: var(--color-text-on-green);
        border-radius: var(--radius-pill);
        padding: 6px 18px;
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 700;
    }

    /* ── Badge Dépassé / Normal ─────────────────────────────────────── */
    .badge-depasse {
        background: #dc3545;
        color: #fff;
        border-radius: 6px;
        padding: 3px 10px;
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-normal {
        background: var(--color-primary-dark);
        color: var(--color-text-on-green);
        border-radius: 6px;
        padding: 3px 10px;
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        font-weight: 600;
    }

    /* ── Ligne tableau alerte ───────────────────────────────────────── */
    .row-alerte {
        background: #fff5f5 !important;
    }
    .row-alerte td { color: #dc3545; }

    /* ── Card dosha dominant ────────────────────────────────────────── */
    .card-dosha-dominant {
        border: 2px solid var(--color-primary) !important;
    }

    /* ── Badge dominant ─────────────────────────────────────────────── */
    .badge-dominant {
        background: var(--color-bg-tint);
        color: var(--color-primary-dark);
        border-radius: 8px;
        padding: 3px 12px;
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        font-weight: 600;
    }

    /* ── List group hormone alerte ──────────────────────────────────── */
    .hor-alerte {
        background: var(--color-bg-tint) !important;
        border-color: var(--color-border-tint) !important;
    }

    /* ── Score numéro ───────────────────────────────────────────────── */
    .score-num {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 2rem;
        line-height: 1;
    }

    /* ── En-tête client ─────────────────────────────────────────────── */
    .bilan-header {
        background: var(--color-bg-card);
        border-radius: var(--radius-card);
        padding: 18px 22px;
        box-shadow: none;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .bilan-header .client-avatar {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, var(--color-navy), var(--color-primary));
        border-radius: var(--radius-card);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-text-on-green);
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    /* ── Barre de progression ───────────────────────────────────────── */
    .progress {
        background: var(--color-border-card);
        border-radius: var(--radius-pill);
    }
    .progress-bar {
        border-radius: var(--radius-pill);
        transition: width .4s ease;
    }

    /* ── Profil résumé Ayurveda ─────────────────────────────────────── */
    .ayurveda-summary {
        background: var(--color-bg-tint);
        border: 1px solid var(--color-border-tint);
        border-radius: var(--radius-card);
        padding: 14px 18px;
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        color: var(--color-navy);
    }

    /* ── Boutons du header bilan ────────────────────────────────────── */
    .btn-outline-secondary {
        color: var(--color-navy);
        border: 1.5px solid var(--color-border-light);
        background: transparent;
        font-family: 'Outfit', sans-serif;
        font-size: 12px;
        font-weight: 500;
        height: 30px;
        padding-left: 10px;
        padding-right: 10px;
        border-radius: 8px;
    }
    .btn-outline-secondary:hover {
        border-color: var(--color-primary-mid);
        color: var(--color-primary-dark);
        background: transparent;
    }
    .btn-outline-primary {
        color: var(--color-primary-dark);
        border: 1.5px solid var(--color-border-tint);
        background: transparent;
        font-family: 'Outfit', sans-serif;
        font-size: 12px;
        font-weight: 500;
        height: 30px;
        padding-left: 10px;
        padding-right: 10px;
        border-radius: 8px;
    }
    .btn-outline-primary:hover {
        background: var(--color-bg-tint);
        border-color: var(--color-border-tint);
        color: var(--color-primary-dark);
    }

    /* ── Tableau ────────────────────────────────────────────────────── */
    .table th {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-text-muted);
        border-bottom: 1px solid var(--color-border-card);
    }
    .table td {
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        color: var(--color-navy);
        border-color: var(--color-border-card);
        vertical-align: middle;
    }

    /* ── Champs menu ────────────────────────────────────────────────── */
    .form-label {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-text-muted);
        margin-bottom: 4px;
    }
    .form-control {
        border: 1.5px solid var(--color-border-light);
        background: var(--color-bg-input);
        border-radius: var(--radius-input);
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        color: var(--color-navy);
    }
    .form-control:focus {
        border-color: var(--color-primary-mid);
        box-shadow: 0 0 0 3px var(--color-focus-ring);
        background: var(--color-bg-card);
        color: var(--color-navy);
    }
</style>

{{-- En-tête page ──────────────────────────────────────────────────── --}}
<div class="bilan-header">
    <div class="client-avatar"><i class="bi bi-person"></i></div>
    <div class="flex-grow-1">
        <h1 class="page-title mb-0">
            <i class="bi bi-bar-chart-line me-2"></i>Bilan nutritionnel
        </h1>
        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
            <span class="bilan-client-chip">{{ $client->nom_complet }}</span>
            <span class="bilan-date">
                <i class="bi bi-clock me-1"></i>Enregistré le {{ $questionnaire->updated_at->format('d/m/Y à H:i') }}
            </span>
        </div>
    </div>
    <div class="d-flex gap-2 flex-shrink-0">
        <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
        <a href="{{ route('questionnaire.show', $client) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Modifier
        </a>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Imprimer
        </button>
    </div>
</div>

<div class="row g-3">

    {{-- ════════════════════════════════════════════════════
         CARD 1 — TYPAGE MÉTABOLIQUE
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="section-header">
                <i class="bi bi-activity"></i>
                <span>1. Typage Métabolique</span>
            </div>
            <div class="card-body p-4">
                @php
                    $met   = $scores['metabolique'];
                    $total = $met['a'] + $met['b'];
                    $pctA  = $total > 0 ? round(($met['a'] / $total) * 100) : 50;
                    $pctB  = $total > 0 ? round(($met['b'] / $total) * 100) : 50;
                @endphp

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="score-num text-cueilleur">{{ $met['a'] }}</div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-1 fs-13">
                            <span class="text-cueilleur fw-semibold">Cueilleur A ({{ $pctA }}%)</span>
                            <span class="text-chasseur fw-semibold">Chasseur B ({{ $pctB }}%)</span>
                        </div>
                        <div class="progress progress-18">
                            <div class="progress-bar bar-cueilleur" style="width:{{ $pctA }}%;"></div>
                            <div class="progress-bar bar-chasseur"  style="width:{{ $pctB }}%;"></div>
                        </div>
                    </div>
                    <div class="score-num text-chasseur">{{ $met['b'] }}</div>
                </div>

                <div class="text-center">
                    @if($met['type'] === 'Cueilleur A')
                        <span class="badge-cueilleur">
                            <i class="bi bi-person-badge me-2"></i>{{ $met['type'] }}
                        </span>
                    @elseif($met['type'] === 'Chasseur B')
                        <span class="badge-chasseur">
                            <i class="bi bi-person-badge me-2"></i>{{ $met['type'] }}
                        </span>
                    @else
                        <span class="badge-mixte">
                            <i class="bi bi-person-badge me-2"></i>{{ $met['type'] }}
                        </span>
                    @endif
                    <p class="profil-desc">
                        @if($met['type'] === 'Cueilleur A')
                            Profil Cueilleur dominant — régime plutôt végétalien, faible en graisses saturées.
                        @elseif($met['type'] === 'Chasseur B')
                            Profil Chasseur dominant — régime riche en protéines animales et graisses de qualité.
                        @else
                            Profil Mixte — régime équilibré, adapté aux deux tendances.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 2 — AYURVEDA
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="section-header">
                <i class="bi bi-yin-yang"></i>
                <span>2. Ayurveda</span>
            </div>
            <div class="card-body p-4">
                @php
                    $ay = $scores['ayurveda'];
                    $doshas = [
                        ['label' => 'Vâta',  'key' => 'vata',  'max' => 114, 'bar' => 'bar-vata',  'text' => 'text-vata'],
                        ['label' => 'Pitta', 'key' => 'pitta', 'max' => 120, 'bar' => 'bar-pitta', 'text' => 'text-pitta'],
                        ['label' => 'Kapha', 'key' => 'kapha', 'max' => 120, 'bar' => 'bar-kapha', 'text' => 'text-kapha'],
                    ];
                    $maxScore = max($ay['vata'], $ay['pitta'], $ay['kapha']);
                @endphp

                <div class="row g-3">
                    @foreach($doshas as $d)
                    @php
                        $score    = $ay[$d['key']];
                        $pct      = $d['max'] > 0 ? round(($score / $d['max']) * 100) : 0;
                        $dominant = $score === $maxScore && $maxScore > 0;
                    @endphp
                    <div class="col-md-4">
                        <div class="card h-100 {{ $dominant ? 'card-dosha-dominant' : '' }}">
                            <div class="card-body text-center py-4">
                                @if($dominant)
                                <div class="badge-dominant mb-2 d-inline-block">
                                    <i class="bi bi-star-fill me-1"></i>Dominant
                                </div>
                                @endif
                                <div class="fw-bold fs-5 {{ $d['text'] }} font-syne">{{ $d['label'] }}</div>
                                <div class="fw-bold my-2 score-value">{{ $score }}</div>
                                <div class="mb-3 score-pts">/ {{ $d['max'] }} pts</div>
                                <div class="progress mb-2">
                                    <div class="progress-bar {{ $d['bar'] }}" role="progressbar"
                                         style="width:{{ $pct }}%;"></div>
                                </div>
                                <div class="{{ $d['text'] }} score-pct-lbl">{{ $pct }}%</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @php
                    $sorted = collect($doshas)->sortByDesc(fn($d) => $ay[$d['key']]);
                    $dom    = $sorted->first();
                    $sec    = $sorted->skip(1)->first();
                @endphp
                <div class="mt-3 ayurveda-summary">
                    <strong>Profil dominant :</strong>
                    <span class="{{ $dom['text'] }} fw-semibold">{{ $dom['label'] }}</span>
                    — {{ $dom['label'] }} {{ $ay[$dom['key']] }} pts ·
                    <span class="{{ $sec['text'] }} fw-semibold">{{ $sec['label'] }}</span>
                    {{ $ay[$sec['key']] }} pts
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 3 — JULIA ROSS
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="section-header">
                <i class="bi bi-brain"></i>
                <span>3. Julia Ross — Classes de déséquilibre</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Classe</th>
                                <th class="text-center col-w-100">Score</th>
                                <th class="text-center col-w-100">Seuil</th>
                                <th class="text-center col-w-140">Résultat</th>
                                <th class="col-w-220">Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(QuestionnaireData::$julia_ross as $classe)
                            @php
                                $jr        = $scores['julia_ross'][$classe['id']];
                                $max       = collect($classe['questions'])->sum('w');
                                $pct       = $max > 0 ? min(100, round(($jr['total'] / $max) * 100)) : 0;
                                $seuil_pct = $max > 0 ? min(100, round(($jr['seuil'] / $max) * 100)) : 0;
                            @endphp
                            <tr class="{{ $jr['depasse'] ? 'row-alerte' : '' }}">
                                <td class="ps-4 py-3">{{ $classe['titre'] }}</td>
                                <td class="text-center fw-bold py-3 fs-13 {{ $jr['depasse'] ? 'text-alerte' : '' }}">
                                    {{ $jr['total'] }}
                                </td>
                                <td class="text-center py-3 text-muted-pa">{{ $jr['seuil'] }}</td>
                                <td class="text-center py-3">
                                    @if($jr['depasse'])
                                        <span class="badge-depasse">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Dépassé
                                        </span>
                                    @else
                                        <span class="badge-normal">
                                            <i class="bi bi-check me-1"></i>Normal
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 pe-4">
                                    <div class="progress">
                                        <div class="progress-bar {{ $jr['depasse'] ? 'bar-alerte' : 'bar-normal' }}"
                                             style="width:{{ $pct }}%;"></div>
                                    </div>
                                    <div class="progress-label-sm">{{ $pct }}% du max</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 4 — DIATHÈSE DE MÉNÉTRIER
    ════════════════════════════════════════════════════ --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="section-header">
                <i class="bi bi-diagram-3"></i>
                <span>4. Diathèse de Ménétrier</span>
            </div>
            <div class="card-body p-4">
                @php $di = $scores['diathese']; @endphp
                <table class="table table-bordered text-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-start"></th>
                            <th class="th-d1">D1</th>
                            <th class="th-d2">D2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start fw-semibold td-label">Enfance (col. 1)</td>
                            <td class="fs-4 fw-bold td-d1">{{ $di['c1_d1'] }}</td>
                            <td class="fs-4 fw-bold td-d2">{{ $di['c1_d2'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-start fw-semibold td-label">Adulte (col. 2)</td>
                            <td class="fs-4 fw-bold td-d1">{{ $di['c2_d1'] }}</td>
                            <td class="fs-4 fw-bold td-d2">{{ $di['c2_d2'] }}</td>
                        </tr>
                        @php
                            $totalD1 = $di['c1_d1'] + $di['c2_d1'];
                            $totalD2 = $di['c1_d2'] + $di['c2_d2'];
                        @endphp
                        <tr class="tr-total">
                            <td class="text-start fw-semibold td-label">Total</td>
                            <td class="fs-4 fw-bold td-d1 {{ $totalD1 > $totalD2 ? 'text-green-dark' : '' }}">{{ $totalD1 }}</td>
                            <td class="fs-4 fw-bold td-d2 {{ $totalD2 > $totalD1 ? 'text-green-dark' : '' }}">{{ $totalD2 }}</td>
                        </tr>
                    </tbody>
                </table>
                @php
                    $diagTotal = $totalD1 + $totalD2;
                    $diagPct   = $diagTotal > 0 ? round(($totalD1 / $diagTotal) * 100) : 50;
                @endphp
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1 diag-label-row">
                        <span class="fw-semibold {{ $totalD1 >= $totalD2 ? 'text-green-dark' : 'text-muted-pa' }}">D1 ({{ $diagPct }}%)</span>
                        <span class="fw-semibold {{ $totalD2 > $totalD1 ? 'text-green-dark' : 'text-muted-pa' }}">D2 ({{ 100 - $diagPct }}%)</span>
                    </div>
                    <div class="progress progress-10">
                        <div class="progress-bar bar-cueilleur" style="width:{{ $diagPct }}%;"></div>
                        <div class="progress-bar progress-bar-muted" style="width:{{ 100 - $diagPct }}%;"></div>
                    </div>
                </div>
                <div class="mt-3 diag-tend">
                    @if($totalD1 > $totalD2)
                        Tendance <strong class="text-green-dark">D1</strong> — profil réactif, terrain allergique.
                    @elseif($totalD2 > $totalD1)
                        Tendance <strong class="text-muted-pa">D2</strong> — profil lent, terrain déficitaire.
                    @else
                        Profil <strong class="text-navy">équilibré D1/D2</strong>.
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 5 — BILAN HORMONAL
    ════════════════════════════════════════════════════ --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="section-header">
                <i class="bi bi-droplet-half"></i>
                <span>5. Bilan Hormonal</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach(QuestionnaireData::$hormones as $cat)
                    @php
                        $hor   = $scores['hormones'][$cat['id']];
                        $pct   = $hor['max'] > 0 ? round(($hor['total'] / $hor['max']) * 100) : 0;
                        $alert = $pct >= 60;
                    @endphp
                    <li class="list-group-item py-3 px-4 {{ $alert ? 'hor-alerte' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="flex-grow-1 hormones-cat-name">{{ $cat['titre'] }}</span>
                            <span class="fw-bold hormones-cat-pct {{ $alert ? 'text-alerte' : 'text-muted-pa' }}">
                                {{ $hor['total'] }} / {{ $hor['max'] }}
                            </span>
                            @if($alert)
                            <i class="bi bi-exclamation-triangle-fill text-alerte"></i>
                            @endif
                        </div>
                        <div class="progress mt-2 progress-6">
                            <div class="progress-bar {{ $alert ? 'bar-alerte' : 'bar-normal' }}"
                                 role="progressbar" style="width:{{ $pct }}%;"></div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         MENU 5 JOURS
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="section-header">
                <i class="bi bi-journal-richtext"></i>
                <span>Menu 5 jours</span>
            </div>
            <div class="card-body p-4">

                {{-- Fichier attaché --}}
                @if($questionnaire->menu_file)
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded" style="background:var(--color-bg-tint);">
                    <i class="bi bi-file-earmark-text fs-4 text-green-dark"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold fs-13">{{ $questionnaire->menu_file_name }}</div>
                        <div class="fs-12 text-muted-pa">Fichier attaché</div>
                    </div>
                    <a href="{{ Storage::disk('public')->url($questionnaire->menu_file) }}"
                       target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download me-1"></i>Télécharger
                    </a>
                </div>
                @endif

                <form method="POST" action="{{ route('questionnaire.menu.save', $client) }}"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- Éditeur riche --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Menu / Plan alimentaire</label>
                        <x-tiptap-editor name="menu_text" :value="$questionnaire->menu_text ?? ''" />
                    </div>

                    {{-- Upload fichier --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Joindre un fichier
                            <span class="text-muted-pa fw-normal fs-12 ms-1">(PDF, TXT, DOC, DOCX — max 10 Mo)</span>
                        </label>
                        <input type="file" name="menu_file" id="menu_file"
                               class="form-control form-control-sm"
                               accept=".pdf,.txt,.doc,.docx">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm" id="saveMenuBtn">
                        <i class="bi bi-save me-1"></i>Enregistrer le menu
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>{{-- /row --}}

@vite('resources/js/tiptap-editor.js')

@endsection
