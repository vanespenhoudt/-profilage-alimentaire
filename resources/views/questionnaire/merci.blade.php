@extends('layouts.public')

@section('title', 'Questionnaire soumis — Merci !')

@section('content')

<div class="text-center py-5">
    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4 bg-tint"
         style="width:96px;height:96px;">
        <i class="bi bi-check-circle-fill fs-1 text-green-dark"></i>
    </div>

    <h1 class="h2 fw-bold mb-2 text-navy">Merci :)</h1>
    <p class="lead text-muted mb-1">Votre questionnaire a bien été soumis.</p>
    <p class="text-muted small">
        Soumis le {{ $questionnaire->submitted_at->format('d/m/Y à H:i') }}
    </p>

    <div class="card mx-auto mt-4 mw-480">
        <div class="card-body py-4">
            <i class="bi bi-person-heart fs-2 mb-3 d-block text-green"></i>
            <p class="mb-0">Votre conseiller en alimentation va analyser vos réponses et vous contactera prochainement avec vos résultats et recommandations personnalisées.</p>
        </div>
    </div>

    <p class="text-muted small mt-4">Vous pouvez fermer cette page.</p>
</div>

@if($questionnaire->menu_visible_client && $questionnaire->menu_text)
<div class="card mx-auto mt-4" style="max-width:680px;">
    <div class="card-body p-4">
        <h5 class="fw-bold text-navy mb-3">
            <i class="bi bi-journal-richtext me-2 text-green-dark"></i>Votre plan alimentaire
        </h5>
        <div class="menu-text">{!! $questionnaire->menu_text !!}</div>

        @if($questionnaire->menu_file)
        <div class="mt-3 pt-3 border-top d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-text text-green-dark"></i>
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($questionnaire->menu_file) }}"
               target="_blank" class="fw-semibold fs-13">
                {{ $questionnaire->menu_file_name }}
            </a>
        </div>
        @endif
    </div>
</div>
@endif

@endsection
