@php $q = $client->questionnaire; @endphp
@if($q?->submitted_at)
    <i class="bi bi-check-circle-fill text-success" title="Soumis le {{ $q->submitted_at->format('d/m/Y') }}"></i>
@elseif($q?->getRawOriginal('answers'))
    <i class="bi bi-circle-half text-warning" title="Commencé, pas encore soumis"></i>
@else
    <i class="bi bi-x-circle-fill text-danger" title="Pas répondu"></i>
@endif
