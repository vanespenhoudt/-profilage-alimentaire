@extends('layouts.public')

@section('title', 'Questionnaire soumis — Merci !')

@section('content')

<div class="text-center py-5">
    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4"
         style="width:96px;height:96px;background:#d4edda">
        <i class="bi bi-check-circle-fill fs-1" style="color:#1a3d25"></i>
    </div>

    <h1 class="h2 fw-bold mb-2" style="color:var(--primary)">Merci !</h1>
    <p class="lead text-muted mb-1">Votre questionnaire a bien été soumis.</p>
    <p class="text-muted small">
        Soumis le {{ $questionnaire->submitted_at->format('d/m/Y à H:i') }}
    </p>

    <div class="card mx-auto mt-4" style="max-width:480px">
        <div class="card-body py-4">
            <i class="bi bi-person-heart fs-2 mb-3 d-block" style="color:var(--primary)"></i>
            <p class="mb-0">Votre conseiller en alimentation va analyser vos réponses et vous contactera prochainement avec vos résultats et recommandations personnalisées.</p>
        </div>
    </div>

    <p class="text-muted small mt-4">Vous pouvez fermer cette page.</p>
</div>

@endsection
