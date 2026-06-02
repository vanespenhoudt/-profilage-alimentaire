{{--
  Partial : résumé des scores pour la vue comparaison.
  Variable attendue : $questionnaire (Questionnaire model)
--}}
@php
use App\Data\QuestionnaireData;
$sc = $questionnaire->scores ?? [];
@endphp

{{-- ── Typage Métabolique ─────────────────────────────── --}}
@if(!empty($sc['metabolique']))
@php
    $met   = $sc['metabolique'];
    $total = $met['a'] + $met['b'];
    $pctA  = $total > 0 ? round(($met['a'] / $total) * 100) : 50;
    $pctB  = 100 - $pctA;
@endphp
<div class="card mb-3">
    <div class="card-header py-2 px-3 fw-semibold small d-flex align-items-center gap-2">
        <i class="bi bi-activity"></i>Typage Métabolique
    </div>
    <div class="card-body py-3 px-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="text-cueilleur fw-bold" style="min-width:28px">{{ $met['a'] }}</span>
            <div class="flex-grow-1 progress" style="height:10px">
                <div class="progress-bar bar-cueilleur" style="width:{{ $pctA }}%"></div>
                <div class="progress-bar bar-chasseur"  style="width:{{ $pctB }}%"></div>
            </div>
            <span class="text-chasseur fw-bold" style="min-width:28px">{{ $met['b'] }}</span>
        </div>
        <div class="text-center">
            @if($met['type'] === 'Cueilleur A')
                <span class="badge-cueilleur"><i class="bi bi-person-badge me-1"></i>{{ $met['type'] }}</span>
            @elseif($met['type'] === 'Chasseur B')
                <span class="badge-chasseur"><i class="bi bi-person-badge me-1"></i>{{ $met['type'] }}</span>
            @else
                <span class="badge-mixte"><i class="bi bi-person-badge me-1"></i>{{ $met['type'] }}</span>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── Ayurveda ────────────────────────────────────────── --}}
@if(!empty($sc['ayurveda']))
@php
    $ay      = $sc['ayurveda'];
    $maxAy   = max($ay['vata'], $ay['pitta'], $ay['kapha']);
    $doshas  = [
        ['label'=>'Vâta',  'key'=>'vata',  'max'=>114, 'bar'=>'bar-vata',  'text'=>'text-vata'],
        ['label'=>'Pitta', 'key'=>'pitta', 'max'=>120, 'bar'=>'bar-pitta', 'text'=>'text-pitta'],
        ['label'=>'Kapha', 'key'=>'kapha', 'max'=>120, 'bar'=>'bar-kapha', 'text'=>'text-kapha'],
    ];
@endphp
<div class="card mb-3">
    <div class="card-header py-2 px-3 fw-semibold small d-flex align-items-center gap-2">
        <i class="bi bi-yin-yang"></i>Ayurveda
    </div>
    <div class="card-body py-3 px-3">
        @foreach($doshas as $d)
        @php
            $score = $ay[$d['key']];
            $pct   = $d['max'] > 0 ? round(($score / $d['max']) * 100) : 0;
            $dom   = $score === $maxAy && $maxAy > 0;
        @endphp
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="{{ $d['text'] }} fw-semibold small {{ $dom ? 'fw-bold' : '' }}" style="width:44px">
                {{ $d['label'] }}@if($dom) ★@endif
            </span>
            <div class="flex-grow-1 progress" style="height:8px">
                <div class="progress-bar {{ $d['bar'] }}" style="width:{{ $pct }}%"></div>
            </div>
            <span class="small text-muted" style="width:36px;text-align:right">{{ $score }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Julia Ross ──────────────────────────────────────── --}}
@if(!empty($sc['julia_ross']))
<div class="card mb-3">
    <div class="card-header py-2 px-3 fw-semibold small d-flex align-items-center gap-2">
        <i class="bi bi-brain"></i>Julia Ross
    </div>
    <div class="card-body py-2 px-3">
        @foreach(QuestionnaireData::$julia_ross as $classe)
        @php
            $jr  = $sc['julia_ross'][$classe['id']] ?? ['total' => 0, 'seuil' => 0, 'depasse' => false];
            $max = collect($classe['questions'])->sum('w');
            $pct = $max > 0 ? min(100, round(($jr['total'] / $max) * 100)) : 0;
        @endphp
        <div class="d-flex align-items-center gap-2 mb-1 py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
            <span class="small {{ $jr['depasse'] ? 'text-alerte fw-semibold' : 'text-muted' }}"
                  style="width:180px;font-size:11px">{{ $classe['titre'] }}</span>
            <div class="flex-grow-1 progress" style="height:6px">
                <div class="progress-bar {{ $jr['depasse'] ? 'bar-alerte' : 'bar-normal' }}"
                     style="width:{{ $pct }}%"></div>
            </div>
            <span class="small fw-semibold {{ $jr['depasse'] ? 'text-alerte' : 'text-muted' }}"
                  style="width:28px;text-align:right">{{ $jr['total'] }}</span>
            @if($jr['depasse'])
            <i class="bi bi-exclamation-triangle-fill text-alerte" style="font-size:11px"></i>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Diathèse ────────────────────────────────────────── --}}
@if(!empty($sc['diathese']))
@php
    $di      = $sc['diathese'];
    $totalD1 = ($di['c1_d1'] ?? 0) + ($di['c2_d1'] ?? 0);
    $totalD2 = ($di['c1_d2'] ?? 0) + ($di['c2_d2'] ?? 0);
    $diagTot = $totalD1 + $totalD2;
    $pctD1   = $diagTot > 0 ? round(($totalD1 / $diagTot) * 100) : 50;
@endphp
<div class="card mb-3">
    <div class="card-header py-2 px-3 fw-semibold small d-flex align-items-center gap-2">
        <i class="bi bi-diagram-3"></i>Diathèse
    </div>
    <div class="card-body py-3 px-3">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold small {{ $totalD1 >= $totalD2 ? 'text-green-dark' : 'text-muted-pa' }}">D1 ({{ $pctD1 }}%)</span>
            <div class="flex-grow-1 progress" style="height:8px">
                <div class="progress-bar bar-cueilleur" style="width:{{ $pctD1 }}%"></div>
                <div class="progress-bar progress-bar-muted" style="width:{{ 100 - $pctD1 }}%"></div>
            </div>
            <span class="fw-semibold small {{ $totalD2 > $totalD1 ? 'text-green-dark' : 'text-muted-pa' }}">D2 ({{ 100 - $pctD1 }}%)</span>
        </div>
        <div class="d-flex justify-content-between mt-2 small text-muted">
            <span>Total D1 : {{ $totalD1 }}</span>
            <span>Total D2 : {{ $totalD2 }}</span>
        </div>
    </div>
</div>
@endif
