@extends('layouts.app')

@section('title', $client->nom_complet)

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="page-title mb-0">
        <i class="bi bi-person me-2"></i>{{ $client->nom_complet }}
    </h1>
    <span class="badge text-bg-secondary ms-2">{{ $client->code }}</span>
    @if($client->rgpd)
        <span class="badge text-bg-success">RGPD OK</span>
    @else
        <span class="badge text-bg-warning text-dark">RGPD en attente</span>
    @endif
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="fw-semibold mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Informations personnelles</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-normal">Prénom</dt>
                    <dd class="col-sm-8">{{ $client->prenom }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Nom</dt>
                    <dd class="col-sm-8">{{ $client->nom }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Téléphone</dt>
                    <dd class="col-sm-8">
                        <a href="tel:{{ $client->tel }}">{{ $client->tel }}</a>
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Email</dt>
                    <dd class="col-sm-8">
                        @if($client->email)
                            <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Adresse</dt>
                    <dd class="col-sm-8">{{ $client->adresse ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Conseiller</dt>
                    <dd class="col-sm-8">{{ $client->conseiller?->name ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Créé le</dt>
                    <dd class="col-sm-8">{{ $client->created_at->format('d/m/Y à H:i') }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Modifié le</dt>
                    <dd class="col-sm-8 mb-0">{{ $client->updated_at->format('d/m/Y à H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="fw-semibold mb-0"><i class="bi bi-clipboard2-pulse me-2 text-primary"></i>Bilan terrain</h6>
            </div>
            <div class="card-body">
                @if($client->bt)
                    <p class="mb-0" style="white-space: pre-line;">{{ $client->bt }}</p>
                @else
                    <p class="text-muted mb-0">Aucun bilan terrain renseigné.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="fw-semibold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Notes</h6>
            </div>
            <div class="card-body">
                @if($client->notes)
                    <p class="mb-0" style="white-space: pre-line;">{{ $client->notes }}</p>
                @else
                    <p class="text-muted mb-0">Aucune note.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('questionnaire.show', $client) }}" class="btn btn-primary">
        <i class="bi bi-clipboard2-pulse me-1"></i>
        {{ $client->questionnaire ? 'Modifier le questionnaire' : 'Remplir le questionnaire' }}
    </a>
    @if($client->questionnaire)
    <a href="{{ route('questionnaire.bilan', $client) }}" class="btn btn-outline-primary">
        <i class="bi bi-bar-chart-line me-1"></i>Voir le bilan
    </a>
    @endif
    <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i>Modifier le profil
    </a>
    <form method="POST" action="{{ route('clients.destroy', $client) }}"
          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-trash me-1"></i>Supprimer
        </button>
    </form>
</div>
@endsection
