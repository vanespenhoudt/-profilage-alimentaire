@extends('layouts.app')

@section('title', 'Comparaison — ' . $client->nom_complet)

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('questionnaire.bilan', $client) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour au bilan
    </a>
    <h1 class="page-title mb-0">
        <i class="bi bi-columns-gap me-2"></i>Comparaison — {{ $client->nom_complet }}
    </h1>
</div>

<div class="row g-4">

    {{-- Session A --}}
    <div class="col-md-6">
        <div class="card mb-3" style="border-top:3px solid var(--color-primary)">
            <div class="card-body py-2 px-3 text-center">
                <div class="fw-semibold">{{ $sessionA->session_label ?? 'Session initiale' }}</div>
                <div class="small text-muted">{{ $sessionA->updated_at?->format('d/m/Y') }}</div>
            </div>
        </div>
        @include('questionnaire.partials.bilan-scores', ['questionnaire' => $sessionA])
    </div>

    {{-- Session B --}}
    <div class="col-md-6">
        <div class="card mb-3" style="border-top:3px solid var(--color-primary-dark)">
            <div class="card-body py-2 px-3 text-center">
                <div class="fw-semibold">{{ $sessionB->session_label ?? 'Session initiale' }}</div>
                <div class="small text-muted">{{ $sessionB->updated_at?->format('d/m/Y') }}</div>
            </div>
        </div>
        @include('questionnaire.partials.bilan-scores', ['questionnaire' => $sessionB])
    </div>

</div>

@endsection
