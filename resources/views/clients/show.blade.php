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
        {{-- Lien client --}}
        <div class="card mb-3 border-2" style="border-color:var(--primary)!important">
            <div class="card-header border-0 pb-0" style="background:#eef1f8">
                <h6 class="fw-semibold mb-0"><i class="bi bi-link-45deg me-2" style="color:var(--primary)"></i>Lien questionnaire client</h6>
            </div>
            <div class="card-body">
                @php $q = $client->questionnaire; @endphp

                @if(session('token_generated'))
                    <div class="alert alert-success py-2 small mb-3">
                        <i class="bi bi-check-circle me-1"></i>Lien généré ! Copiez-le ci-dessous et envoyez-le à votre client.
                    </div>
                @endif

                @if($q && $q->token)
                    {{-- Statut --}}
                    <div class="mb-3">
                        @if($q->isSubmitted())
                            <span class="badge bg-success fs-6 px-3 py-2">
                                <i class="bi bi-check-circle me-1"></i>{{ $q->statusLabel() }}
                            </span>
                        @elseif($q->answers)
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                <i class="bi bi-hourglass-split me-1"></i>En cours de remplissage
                            </span>
                        @else
                            <span class="badge bg-secondary fs-6 px-3 py-2">
                                <i class="bi bi-clock me-1"></i>En attente du client
                            </span>
                        @endif
                    </div>

                    {{-- Lien à copier --}}
                    <div class="input-group mb-2">
                        <input type="text" class="form-control form-control-sm font-monospace"
                               id="clientLink"
                               value="{{ route('questionnaire.public.show', $q->token) }}"
                               readonly>
                        <button class="btn btn-outline-secondary btn-sm" type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('clientLink').value).then(()=>this.innerHTML='<i class=\'bi bi-check\'></i> Copié!')">
                            <i class="bi bi-clipboard"></i> Copier
                        </button>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <form method="POST" action="{{ route('questionnaire.generate-token', $client) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm"
                                    onclick="return confirm('Générer un nouveau lien invalidera l\'ancien. Continuer ?')">
                                <i class="bi bi-arrow-clockwise me-1"></i>Nouveau lien
                            </button>
                        </form>
                        @if($q->isSubmitted())
                        <a href="{{ route('questionnaire.bilan', $client) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-bar-chart me-1"></i>Voir le bilan
                        </a>
                        @endif
                    </div>
                @else
                    <p class="text-muted small mb-3">Générez un lien unique à envoyer à votre client pour qu'il remplisse son questionnaire en ligne.</p>
                    <form method="POST" action="{{ route('questionnaire.generate-token', $client) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-link-45deg me-1"></i>Générer un lien client
                        </button>
                    </form>
                @endif
            </div>
        </div>

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
