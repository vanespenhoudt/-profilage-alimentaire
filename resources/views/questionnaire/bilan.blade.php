@extends('layouts.app')

@section('title', 'Bilan – ' . $client->nom_complet)

@section('content')
@php
use App\Data\QuestionnaireData;
$scores = $questionnaire->scores;
@endphp

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="page-title mb-0">
        <i class="bi bi-bar-chart-line me-2"></i>Bilan nutritionnel
    </h1>
    <span class="badge text-bg-secondary ms-1">{{ $client->nom_complet }}</span>
    <span class="text-muted small ms-2">Enregistré le {{ $questionnaire->updated_at->format('d/m/Y à H:i') }}</span>
</div>

<div class="d-flex gap-2 mb-4">
    <a href="{{ route('questionnaire.show', $client) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil me-1"></i>Modifier le questionnaire
    </a>
    <button class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i>Imprimer / PDF
    </button>
</div>

<div class="row g-4">

    {{-- ════════════════════════════════════════════════════
         CARD 1 — TYPAGE MÉTABOLIQUE
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2" style="background:var(--primary);color:#fff">
                <i class="bi bi-activity fs-5"></i>
                <span class="fw-semibold">1. Typage Métabolique</span>
            </div>
            <div class="card-body">
                @php
                    $met  = $scores['metabolique'];
                    $total = $met['a'] + $met['b'];
                    $pctA  = $total > 0 ? round(($met['a'] / $total) * 100) : 50;
                    $pctB  = $total > 0 ? round(($met['b'] / $total) * 100) : 50;
                    $typeColor = match($met['type']) {
                        'Cueilleur A' => 'primary',
                        'Chasseur B'  => 'danger',
                        default       => 'secondary',
                    };
                @endphp

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="fs-3 fw-bold" style="color:var(--primary)">{{ $met['a'] }}</div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-primary fw-semibold">Cueilleur A ({{ $pctA }}%)</span>
                            <span class="text-danger fw-semibold">Chasseur B ({{ $pctB }}%)</span>
                        </div>
                        <div class="progress" style="height:16px;border-radius:8px">
                            <div class="progress-bar bg-primary" style="width:{{ $pctA }}%"></div>
                            <div class="progress-bar bg-danger" style="width:{{ $pctB }}%"></div>
                        </div>
                    </div>
                    <div class="fs-3 fw-bold text-danger">{{ $met['b'] }}</div>
                </div>

                <div class="text-center">
                    <span class="badge bg-{{ $typeColor }} fs-5 px-4 py-2">
                        <i class="bi bi-person-badge me-2"></i>{{ $met['type'] }}
                    </span>
                    <p class="text-muted small mt-2 mb-0">
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
            <div class="card-header d-flex align-items-center gap-2" style="background:var(--primary);color:#fff">
                <i class="bi bi-yin-yang fs-5"></i>
                <span class="fw-semibold">2. Ayurveda</span>
            </div>
            <div class="card-body">
                @php
                    $ay = $scores['ayurveda'];
                    $doshas = [
                        ['label' => 'Vâta',  'key' => 'vata',  'max' => 114, 'color' => 'primary'],
                        ['label' => 'Pitta', 'key' => 'pitta', 'max' => 120, 'color' => 'warning'],
                        ['label' => 'Kapha', 'key' => 'kapha', 'max' => 120, 'color' => 'success'],
                    ];
                    $maxScore = max($ay['vata'], $ay['pitta'], $ay['kapha']);
                @endphp

                <div class="row g-4">
                    @foreach($doshas as $d)
                    @php
                        $score = $ay[$d['key']];
                        $pct   = $d['max'] > 0 ? round(($score / $d['max']) * 100) : 0;
                        $dominant = $score === $maxScore && $maxScore > 0;
                    @endphp
                    <div class="col-md-4">
                        <div class="card border h-100 {{ $dominant ? 'border-2' : '' }}" style="{{ $dominant ? 'border-color:var(--primary)!important' : '' }}">
                            <div class="card-body text-center">
                                @if($dominant)
                                <div class="badge bg-warning text-dark mb-2">Dominant</div>
                                @endif
                                <div class="fw-bold fs-4" style="color:var(--primary)">{{ $d['label'] }}</div>
                                <div class="display-5 fw-bold my-2">{{ $score }}</div>
                                <div class="text-muted small mb-3">/ {{ $d['max'] }} pts</div>
                                <div class="progress mb-2" style="height:10px">
                                    <div class="progress-bar bg-{{ $d['color'] }}" role="progressbar"
                                         style="width:{{ $pct }}%" aria-valuenow="{{ $pct }}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="small text-muted">{{ $pct }}%</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @php
                    $sorted = collect($doshas)->sortByDesc(fn($d) => $ay[$d['key']]);
                    $dom = $sorted->first();
                    $sec = $sorted->skip(1)->first();
                @endphp
                <div class="alert alert-light border mt-3 mb-0 small">
                    <strong>Profil :</strong> {{ $dom['label'] }}-{{ $sec['label'] }}
                    ({{ $dom['label'] }} {{ $ay[$dom['key']] }} pts · {{ $sec['label'] }} {{ $ay[$sec['key']] }} pts)
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 3 — JULIA ROSS
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2" style="background:var(--primary);color:#fff">
                <i class="bi bi-brain fs-5"></i>
                <span class="fw-semibold">3. Julia Ross — Classes de déséquilibre</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Classe</th>
                                <th class="text-center" style="width:100px">Score</th>
                                <th class="text-center" style="width:100px">Seuil</th>
                                <th class="text-center" style="width:120px">Résultat</th>
                                <th style="width:200px">Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(QuestionnaireData::$julia_ross as $classe)
                            @php
                                $jr  = $scores['julia_ross'][$classe['id']];
                                $max = collect($classe['questions'])->sum('w');
                                $pct = $max > 0 ? min(100, round(($jr['total'] / $max) * 100)) : 0;
                                $seuil_pct = $max > 0 ? min(100, round(($jr['seuil'] / $max) * 100)) : 0;
                            @endphp
                            <tr class="{{ $jr['depasse'] ? 'table-danger' : '' }}">
                                <td class="small">{{ $classe['titre'] }}</td>
                                <td class="text-center fw-bold {{ $jr['depasse'] ? 'text-danger' : '' }}">
                                    {{ $jr['total'] }}
                                </td>
                                <td class="text-center text-muted">{{ $jr['seuil'] }}</td>
                                <td class="text-center">
                                    @if($jr['depasse'])
                                        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Dépassé</span>
                                    @else
                                        <span class="badge bg-success"><i class="bi bi-check me-1"></i>Normal</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="height:8px;position:relative">
                                        <div class="progress-bar {{ $jr['depasse'] ? 'bg-danger' : 'bg-success' }}"
                                             style="width:{{ $pct }}%"></div>
                                    </div>
                                    <div class="small text-muted mt-1">{{ $pct }}% du max</div>
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
            <div class="card-header d-flex align-items-center gap-2" style="background:var(--primary);color:#fff">
                <i class="bi bi-diagram-3 fs-5"></i>
                <span class="fw-semibold">4. Diathèse de Ménétrier</span>
            </div>
            <div class="card-body">
                @php $di = $scores['diathese']; @endphp
                <table class="table table-bordered text-center mb-0">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-primary">D1</th>
                            <th class="text-secondary">D2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start fw-semibold small">Enfance (col. 1)</td>
                            <td class="fs-4 fw-bold text-primary">{{ $di['c1_d1'] }}</td>
                            <td class="fs-4 fw-bold text-secondary">{{ $di['c1_d2'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-start fw-semibold small">Adulte (col. 2)</td>
                            <td class="fs-4 fw-bold text-primary">{{ $di['c2_d1'] }}</td>
                            <td class="fs-4 fw-bold text-secondary">{{ $di['c2_d2'] }}</td>
                        </tr>
                        <tr class="table-light">
                            <td class="text-start fw-semibold small">Total</td>
                            <td class="fw-bold text-primary">{{ $di['c1_d1'] + $di['c2_d1'] }}</td>
                            <td class="fw-bold text-secondary">{{ $di['c1_d2'] + $di['c2_d2'] }}</td>
                        </tr>
                    </tbody>
                </table>
                @php
                    $totalD1 = $di['c1_d1'] + $di['c2_d1'];
                    $totalD2 = $di['c1_d2'] + $di['c2_d2'];
                    $diagTotal = $totalD1 + $totalD2;
                    $diagPct   = $diagTotal > 0 ? round(($totalD1 / $diagTotal) * 100) : 50;
                @endphp
                <div class="mt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-primary fw-semibold">D1 ({{ $diagPct }}%)</span>
                        <span class="text-secondary fw-semibold">D2 ({{ 100 - $diagPct }}%)</span>
                    </div>
                    <div class="progress" style="height:12px;border-radius:6px">
                        <div class="progress-bar bg-primary" style="width:{{ $diagPct }}%"></div>
                        <div class="progress-bar bg-secondary" style="width:{{ 100 - $diagPct }}%"></div>
                    </div>
                </div>
                <div class="mt-3 small text-muted">
                    @if($totalD1 > $totalD2)
                        Tendance <strong class="text-primary">D1</strong> — profil réactif, terrain allergique.
                    @elseif($totalD2 > $totalD1)
                        Tendance <strong class="text-secondary">D2</strong> — profil lent, terrain déficitaire.
                    @else
                        Profil <strong>équilibré D1/D2</strong>.
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
            <div class="card-header d-flex align-items-center gap-2" style="background:var(--primary);color:#fff">
                <i class="bi bi-droplet-half fs-5"></i>
                <span class="fw-semibold">5. Bilan Hormonal</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach(QuestionnaireData::$hormones as $cat)
                    @php
                        $hor = $scores['hormones'][$cat['id']];
                        $pct = $hor['max'] > 0 ? round(($hor['total'] / $hor['max']) * 100) : 0;
                        $alert = $pct >= 60;
                    @endphp
                    <li class="list-group-item py-2 {{ $alert ? 'list-group-item-danger' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small flex-grow-1">{{ $cat['titre'] }}</span>
                            <span class="fw-bold {{ $alert ? 'text-danger' : 'text-muted' }}" style="min-width:60px;text-align:right">
                                {{ $hor['total'] }} / {{ $hor['max'] }}
                            </span>
                            @if($alert)
                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                            @endif
                        </div>
                        <div class="progress mt-1" style="height:6px">
                            <div class="progress-bar {{ $alert ? 'bg-danger' : 'bg-primary' }}"
                                 role="progressbar" style="width:{{ $pct }}%"></div>
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
            <div class="card-header d-flex align-items-center gap-2" style="background:var(--primary);color:#fff">
                <i class="bi bi-journal-richtext fs-5"></i>
                <span class="fw-semibold">Menu 5 jours</span>
            </div>
            <div class="card-body">
                @if($questionnaire->menu_text)
                    <p style="white-space:pre-line">{{ $questionnaire->menu_text }}</p>
                @else
                    <p class="text-muted mb-3">Aucun menu renseigné.</p>
                @endif
                <form method="POST" action="{{ route('questionnaire.store', $client) }}">
                    @csrf
                    {{-- Réinjecter toutes les réponses existantes --}}
                    @foreach($questionnaire->answers ?? [] as $key => $val)
                        @if(is_array($val))
                            @foreach($val as $vk => $vv)
                            <input type="hidden" name="{{ $key }}[{{ $vk }}]" value="{{ $vv }}">
                            @endforeach
                        @else
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <div class="mb-2">
                        <label for="menu_text" class="form-label fw-semibold small">Saisir / modifier le menu</label>
                        <textarea name="menu_text" id="menu_text" class="form-control" rows="6"
                                  placeholder="Ex : Lundi – Petit-déjeuner : …">{{ $questionnaire->menu_text }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i>Enregistrer le menu
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>{{-- /row --}}

@endsection
