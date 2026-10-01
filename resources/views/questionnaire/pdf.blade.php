@php
use App\Data\QuestionnaireData;

$a       = $answers;
$checked = fn (string $key) => !empty($a[$key]);
$mark    = fn (bool $on) => $on ? '<span class="yes">&#10004;</span>' : '<span class="no">—</span>';
$has     = fn (string $section) => in_array($section, $completed, true);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Questionnaire – {{ $client->nom_complet }}</title>
<style>
    @page { margin: 18mm 15mm 18mm 15mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #1f2937; line-height: 1.35; }
    h1 { font-size: 16pt; color: #1e3a5f; margin: 0 0 2mm; }
    h2 { font-size: 12pt; color: #fff; background: #1e3a5f; padding: 2mm 3mm; margin: 6mm 0 2mm; page-break-after: avoid; }
    h3 { font-size: 10pt; color: #1e3a5f; margin: 4mm 0 1.5mm; border-bottom: 1px solid #cbd5e1; padding-bottom: 1mm; page-break-after: avoid; }
    .meta { color: #64748b; font-size: 8.5pt; margin-bottom: 4mm; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 2mm; }
    th, td { border: 1px solid #e2e8f0; padding: 1.2mm 2mm; vertical-align: top; text-align: left; }
    th { background: #f1f5f9; font-size: 8.5pt; color: #334155; }
    tr { page-break-inside: avoid; }
    td.num { width: 7mm; color: #94a3b8; text-align: right; }
    td.c { width: 14mm; text-align: center; }
    .yes { color: #15803d; font-weight: bold; }
    .no { color: #cbd5e1; }
    .sel { background: #ecfdf5; font-weight: bold; }
    .muted { color: #94a3b8; font-style: italic; }
    .pill { display: inline-block; padding: 1mm 3mm; background: #1e3a5f; color: #fff; font-weight: bold; }
    .footer { position: fixed; bottom: -10mm; left: 0; right: 0; text-align: center; font-size: 7.5pt; color: #94a3b8; }
</style>
</head>
<body>

<div class="footer">Profilage Alimentaire® — {{ $client->nom_complet }} — document confidentiel</div>

<h1>Questionnaire — réponses</h1>
<div class="meta">
    {{ $client->nom_complet }}
    @if($questionnaire->session_label) · {{ $questionnaire->session_label }} @endif
    · Dernière mise à jour : {{ $questionnaire->updated_at?->format('d/m/Y à H:i') ?? '—' }}
    · Généré le {{ now()->format('d/m/Y à H:i') }}
</div>

{{-- ══ Identité ══ --}}
<h2>Identité</h2>
<table>
    <tr><th style="width:35%">Nom</th><td>{{ $client->nom ?? '—' }}</td></tr>
    <tr><th>Prénom</th><td>{{ $client->prenom ?? '—' }}</td></tr>
    <tr><th>Âge</th><td>{{ $client->age ?? '—' }}</td></tr>
    <tr><th>Sexe</th><td>{{ $client->sexe ?? '—' }}</td></tr>
    <tr><th>Taille</th><td>{{ $client->taille ? $client->taille . ' cm' : '—' }}</td></tr>
    <tr><th>Poids</th><td>{{ $client->poids ? $client->poids . ' kg' : '—' }}</td></tr>
    <tr><th>Sentinelles</th><td>{!! $client->sentinelles ? nl2br(e($client->sentinelles)) : '—' !!}</td></tr>
</table>

{{-- ══ 1. Groupe sanguin ══ --}}
@if($has('groupe_sanguin'))
<h2>1. Groupe sanguin</h2>
<p>Groupe sanguin : <span class="pill">{{ $a['groupe_sanguin'] }}</span></p>
@endif

{{-- ══ 2. Diathèses ══ --}}
@if($has('diathese'))
<h2>2. Diathèses</h2>
@foreach(['Période enfance (avant 12–15 ans)' => QuestionnaireData::$diathese_col1, 'Période adulte (aujourd\'hui)' => QuestionnaireData::$diathese_col2] as $titre => $rows)
<h3>{{ $titre }}</h3>
<table>
    <tr><th></th><th>Diathèse 1</th><th>Diathèse 2</th></tr>
    @foreach($rows as $q)
    @php $v = $a[$q['id']] ?? null; @endphp
    <tr>
        <td class="num">{{ $loop->iteration }}.</td>
        <td class="{{ $v === 'd1' ? 'sel' : '' }}">{!! $v === 'd1' ? '<span class="yes">&#10004;</span> ' : '' !!}{{ $q['d1'] }}</td>
        <td class="{{ $v === 'd2' ? 'sel' : '' }}">{!! $v === 'd2' ? '<span class="yes">&#10004;</span> ' : '' !!}{{ $q['d2'] }}</td>
    </tr>
    @endforeach
</table>
@endforeach
@endif

{{-- ══ 3. Ayurveda ══ --}}
@if($has('ayurveda'))
<h2>3. Ayurveda</h2>
<p class="muted">Échelle de 1 (ne s'applique pas) à 6 (beaucoup).</p>
@foreach(['Vâta' => ['v', QuestionnaireData::$vata], 'Pitta' => ['p', QuestionnaireData::$pitta], 'Kapha' => ['k', QuestionnaireData::$kapha]] as $dosha => [$prefix, $items])
@php
    $total = 0;
    foreach ($items as $i => $_) { $total += (int) ($a[$prefix . $i] ?? 0); }
@endphp
<h3>{{ $dosha }} — total : {{ $total }} / {{ count($items) * 6 }}</h3>
<table>
    @foreach($items as $i => $label)
    <tr>
        <td class="num">{{ $i + 1 }}.</td>
        <td>{{ $label }}</td>
        <td class="c">{!! isset($a[$prefix . $i]) && $a[$prefix . $i] !== '' ? '<strong>' . e($a[$prefix . $i]) . '</strong>' : '<span class="no">—</span>' !!}</td>
    </tr>
    @endforeach
</table>
@endforeach
@endif

{{-- ══ 4. Métabolique ══ --}}
@if($has('metabolique'))
<h2>4. Type métabolique</h2>
@php
    $tot = ['A' => 0, 'B' => 0, 'M' => 0];
    foreach (QuestionnaireData::$metabolique as $q) {
        foreach ($tot as $col => $_) { if ($checked($q['id'] . '_' . $col)) { $tot[$col]++; } }
    }
@endphp
<p>Totaux — Cueilleur : <strong>{{ $tot['A'] }}</strong> · Chasseur : <strong>{{ $tot['B'] }}</strong> · Mixte : <strong>{{ $tot['M'] }}</strong></p>
<table>
    <tr><th></th><th style="width:22%">Thème</th><th>Cueilleur</th><th>Chasseur</th><th>Mixte</th></tr>
    @foreach(QuestionnaireData::$metabolique as $q)
    <tr>
        <td class="num">{{ $loop->iteration }}.</td>
        <td>{{ $q['label'] }}</td>
        @foreach(['A', 'B', 'M'] as $col)
            @if($q['options'][$col] === null)
            <td></td>
            @else
            @php $on = $checked($q['id'] . '_' . $col); @endphp
            <td class="{{ $on ? 'sel' : '' }}">{!! $on ? '<span class="yes">&#10004;</span> ' : '' !!}{{ $q['options'][$col] }}</td>
            @endif
        @endforeach
    </tr>
    @endforeach
</table>
@endif

{{-- ══ 5. Julia Ross ══ --}}
@if($has('julia_ross'))
<h2>5. Julia Ross — Neurotransmetteurs</h2>
@foreach(QuestionnaireData::$julia_ross as $classe)
@php
    $total = 0;
    foreach ($classe['questions'] as $qi => $q) { if ($checked($classe['id'] . '_' . $qi)) { $total += $q['w']; } }
@endphp
<h3>{{ $classe['titre'] }} — total : {{ $total }} (seuil {{ $classe['seuil'] }})</h3>
<table>
    @foreach($classe['questions'] as $qi => $q)
    @php $on = $checked($classe['id'] . '_' . $qi); @endphp
    <tr class="{{ $on ? 'sel' : '' }}">
        <td class="num">{{ $qi + 1 }}.</td>
        <td>
            {{ $q['t'] }}
            @if($classe['id'] === 'jr3' && $qi === 3 && !empty($a['jr_3_4_heures']))
                <br><span class="muted">Heures : {{ $a['jr_3_4_heures'] }}</span>
            @endif
            @if($classe['id'] === 'jr5' && $qi === 9)
                @if(!empty($a['jr_5_10_type']))<br><span class="muted">Type : {{ $a['jr_5_10_type'] }}</span>@endif
                @if(!empty($a['jr_5_10_diagnostic']))<br><span class="muted">Diagnostic : {{ $a['jr_5_10_diagnostic'] }}</span>@endif
            @endif
        </td>
        <td class="c">+{{ $q['w'] }}</td>
        <td class="c">{!! $mark($on) !!}</td>
    </tr>
    @endforeach
</table>
@endforeach
@endif

{{-- ══ 6. Hormones ══ --}}
@if($has('hormones'))
<h2>6. Hormones</h2>
@foreach(QuestionnaireData::$hormones as $cat)
@php
    $total = 0;
    foreach ($cat['questions'] as $qi => $_) { if ($checked($cat['id'] . '_' . $qi)) { $total++; } }
@endphp
<h3>{{ $cat['titre'] }} — {{ $total }} / {{ $cat['max'] }}</h3>
<table>
    @foreach($cat['questions'] as $qi => $label)
    @php $on = $checked($cat['id'] . '_' . $qi); @endphp
    <tr class="{{ $on ? 'sel' : '' }}">
        <td class="num">{{ $qi + 1 }}.</td>
        <td>{{ $label }}</td>
        <td class="c">{!! $mark($on) !!}</td>
    </tr>
    @endforeach
</table>
@endforeach
@endif

{{-- ══ 7. Canaris ══ --}}
@if($has('canaris'))
<h2>7. Canaris</h2>
<h3>Contexte</h3>
<table>
    @foreach(QuestionnaireData::$canaris_contexte as $q)
    <tr>
        <td style="width:65%">{{ $q['texte'] }}</td>
        <td><strong>{{ $q['options'][$a[$q['id']] ?? ''] ?? '—' }}</strong></td>
    </tr>
    @endforeach
</table>
@foreach(['Adulte' => QuestionnaireData::$canaris_adulte, 'Enfant' => QuestionnaireData::$canaris_enfant] as $titre => $items)
@php
    $total = 0;
    foreach ($items as $q) { if ($checked($q['id'])) { $total += $q['poids']; } }
@endphp
<h3>{{ $titre }} — score : {{ $total }}</h3>
<table>
    @foreach($items as $q)
    @php $on = $checked($q['id']); @endphp
    <tr class="{{ $on ? 'sel' : '' }}">
        <td class="num">{{ $loop->iteration }}.</td>
        <td>{{ $q['texte'] }}</td>
        <td class="c">+{{ $q['poids'] }}</td>
        <td class="c">{!! $mark($on) !!}</td>
    </tr>
    @endforeach
</table>
@endforeach
@endif

</body>
</html>
