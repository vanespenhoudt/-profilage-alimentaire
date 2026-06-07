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
    <span class="chip-code ms-1">{{ $client->code }}</span>
    @if($client->rgpd)
        <span class="badge-rgpd-ok">RGPD OK</span>
    @else
        <span class="badge-rgpd-wait">RGPD en attente</span>
    @endif
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header border-0 pb-0">
                <h6 class="card-sec-title">
                    <i class="bi bi-person-lines-fill me-2 text-green"></i>Informations personnelles
                </h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 dl-info">
                    <dt class="col-sm-4 dt-label">Prénom</dt>
                    <dd class="col-sm-8 dd-value">{{ $client->prenom }}</dd>

                    <dt class="col-sm-4 dt-label">Nom</dt>
                    <dd class="col-sm-8 dd-value">{{ $client->nom }}</dd>

                    <dt class="col-sm-4 dt-label">Téléphone</dt>
                    <dd class="col-sm-8">
                        <a href="tel:{{ $client->tel }}" class="link-green-dark">{{ $client->tel }}</a>
                    </dd>

                    <dt class="col-sm-4 dt-label">Email</dt>
                    <dd class="col-sm-8">
                        @if($client->email)
                            <a href="mailto:{{ $client->email }}" class="link-green-dark">{{ $client->email }}</a>
                        @else
                            <span class="text-muted-pa">-</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 dt-label">Adresse</dt>
                    <dd class="col-sm-8 dd-value">{{ $client->adresse ?? '-' }}</dd>

                    <dt class="col-sm-4 dt-label">Conseiller</dt>
                    <dd class="col-sm-8 dd-value">{{ $client->conseiller?->name ?? '-' }}</dd>

                    <dt class="col-sm-4 dt-label">Créé le</dt>
                    <dd class="col-sm-8 dd-value">{{ $client->created_at->format('d/m/Y à H:i') }}</dd>

                    <dt class="col-sm-4 dt-label">Modifié le</dt>
                    <dd class="col-sm-8 dd-value mb-0">{{ $client->updated_at->format('d/m/Y à H:i') }}</dd>
                </dl>
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label class="form-label">Âge</label>
                        <p class="form-control-plaintext">{{ $client->age ?? '—' }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sexe</label>
                        <p class="form-control-plaintext">{{ $client->sexe ?? '—' }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Taille</label>
                        <p class="form-control-plaintext">{{ $client->taille ? $client->taille . ' cm' : '—' }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Poids</label>
                        <p class="form-control-plaintext">{{ $client->poids ? $client->poids . ' kg' : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $q = $client->questionnaire;
        $allSects = [
            'julia_ross'     => 'Julia Ross — Neurotransmetteurs',
            'metabolique'    => 'Métaboltyping',
            'diathese'       => 'Diathèses',
            'ayurveda'       => 'Ayurveda',
            'groupe_sanguin' => 'Groupe sanguin',
            'hormones'       => 'Bilan Hormonal',
        ];
        $selectedSects = $q?->sections ?? array_keys($allSects);
    @endphp

    {{-- Modal choix des questionnaires --}}
    <div class="modal fade" id="tokenModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content modal-content-rounded">
                <div class="modal-header modal-header-navy">
                    <h5 class="modal-title modal-title-syne">
                        <i class="bi bi-link-45deg me-2"></i>
                        {{ $q?->token ? 'Nouveau lien client' : 'Générer un lien client' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('questionnaire.generate-token', $client) }}">
                    @csrf
                    <div class="modal-body modal-body-card">
                        @if($q?->token)
                            <div class="alert-warning-soft mb-3">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Un nouveau lien invalidera l'ancien.
                            </div>
                        @endif
                        <p class="text-muted-pa fs-13 mb-3">Choisissez les questionnaires à envoyer au client :</p>
                        @foreach($allSects as $key => $label)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox"
                                   name="sections[]" value="{{ $key }}"
                                   id="sect_{{ $key }}"
                                   @checked(in_array($key, $selectedSects))>
                            <label class="form-check-label form-check-label-navy" for="sect_{{ $key }}">{{ $label }}</label>
                        </div>
                        @endforeach

                        <hr class="my-3">
                        <div class="d-flex align-items-start gap-3 p-3 rounded mb-2" style="background:var(--color-bg-tint);">
                            <div class="form-check form-switch mb-0 mt-1">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="menu_visible_client" id="menuVisibleClient" value="1"
                                       @checked($q?->menu_visible_client)>
                            </div>
                            <label class="form-check-label" for="menuVisibleClient">
                                <span class="fw-semibold fs-13">Partager le menu avec le client</span>
                                <span class="d-block fs-12 text-muted-pa mt-1">
                                    Si activé, le plan alimentaire rédigé dans le questionnaire sera visible par le client après soumission.
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-card">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-link-45deg me-1"></i>Générer le lien
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        {{-- Lien client --}}
        <div class="card mb-3 card-primary-border">
            <div class="card-header border-0 pb-0 card-header-tint">
                <h6 class="card-sec-title-green">
                    <i class="bi bi-link-45deg me-2"></i>Lien questionnaire client
                </h6>
            </div>
            <div class="card-body">
                @if(session('token_generated'))
                    <div class="alert-tint mb-3">
                        <i class="bi bi-check-circle me-1"></i>Lien généré ! Copiez-le ci-dessous et envoyez-le à votre client.
                    </div>
                @endif

                @if($q && $q->token)
                    {{-- Statut --}}
                    <div class="mb-3">
                        @if($q->isSubmitted())
                            <span class="q-status-submitted">
                                <i class="bi bi-check-circle me-1"></i>{{ $q->statusLabel() }}
                            </span>
                        @elseif($q->answers)
                            <span class="q-status-inprogress">
                                <i class="bi bi-hourglass-split me-1"></i>En cours de remplissage
                            </span>
                        @else
                            <span class="q-status-pending">
                                <i class="bi bi-clock me-1"></i>En attente du client
                            </span>
                        @endif
                    </div>

                    {{-- Sections envoyées --}}
                    <div class="mb-2 d-flex flex-wrap gap-1">
                        @foreach($allSects as $key => $label)
                            @if(in_array($key, $selectedSects))
                                <span class="chip-sent">{{ $label }}</span>
                            @endif
                        @endforeach
                    </div>

                    {{-- Lien à copier --}}
                    <div class="input-group mb-2">
                        <input type="text" class="form-control form-control-sm font-monospace input-mono-sm"
                               id="clientLink"
                               value="{{ route('questionnaire.public.show', $q->token) }}"
                               readonly>
                        <button class="btn btn-outline-secondary btn-sm" type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('clientLink').value).then(()=>this.innerHTML='<i class=\'bi bi-check\'></i> Copié!')">
                            <i class="bi bi-clipboard"></i> Copier
                        </button>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#tokenModal">
                            <i class="bi bi-arrow-clockwise me-1"></i>Nouveau lien
                        </button>
                        @if($q->isSubmitted())
                        <a href="{{ route('questionnaire.bilan', $client) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-bar-chart me-1"></i>Voir le bilan
                        </a>
                        @endif
                    </div>
                @else
                    <p class="text-muted-pa fs-13 mb-3">Générez un lien unique à envoyer à votre client pour qu'il remplisse son questionnaire en ligne.</p>
                    <button type="button" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#tokenModal">
                        <i class="bi bi-link-45deg me-1"></i>Générer un lien client
                    </button>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0 pb-0">
                <h6 class="card-sec-title">
                    <i class="bi bi-clipboard2-pulse me-2 text-green"></i>Bilan terrain
                </h6>
            </div>
            <div class="card-body">
                @if($client->bt)
                    <p class="mb-0 menu-text">{{ $client->bt }}</p>
                @else
                    <p class="text-muted-pa fs-13 mb-0">Aucun bilan terrain renseigné.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0 pb-0">
                <h6 class="card-sec-title">
                    <i class="bi bi-journal-text me-2 text-green"></i>Notes
                </h6>
            </div>
            <div class="card-body">
                @if($client->notes)
                    <p class="mb-0 menu-text">{{ $client->notes }}</p>
                @else
                    <p class="text-muted-pa fs-13 mb-0">Aucune note.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal nouvelle session --}}
@if($client->questionnaire)
<div class="modal fade" id="nouvelleSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-content-rounded">
            <div class="modal-header modal-header-navy">
                <h5 class="modal-title modal-title-syne">
                    <i class="bi bi-plus-circle me-2"></i>Nouvelle évaluation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('questionnaire.nouvelle-session', $client) }}">
                @csrf
                <div class="modal-body modal-body-card">
                    <p class="text-muted-pa fs-13 mb-3">
                        L'évaluation actuelle sera archivée. Une nouvelle évaluation vierge sera créée pour ce client.
                    </p>
                    <div class="mb-3">
                        <label class="form-label" for="newSessionLabel">Nom de l'évaluation <span class="text-muted-pa">(optionnel)</span></label>
                        <input type="text" class="form-control" id="newSessionLabel"
                               name="session_label" placeholder="ex : Suivi 3 mois, Bilan 2025…">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="previous_answers" value="1" id="prefillAnswers">
                        <label class="form-check-label form-check-label-navy" for="prefillAnswers">
                            Pré-remplir avec les réponses de l'évaluation précédente
                        </label>
                    </div>
                </div>
                <div class="modal-footer modal-footer-card">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary text-center justify-content-center" dusk="btn-submit-nouvelle-session">
                        <i class="bi bi-plus-lg me-1"></i>Créer l'évaluation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="mt-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('questionnaire.show', $client) }}" class="btn btn-primary text-center justify-content-center">
        <i class="bi bi-clipboard2-pulse me-1"></i>
        {{ $client->questionnaire ? 'Modifier le questionnaire' : 'Remplir le questionnaire' }}
    </a>
    @if($client->questionnaire)
    <a href="{{ route('questionnaire.bilan', $client) }}" class="btn btn-outline-secondary text-center justify-content-center">
        <i class="bi bi-bar-chart-line me-1"></i>Voir le bilan
    </a>
    <button type="button" class="btn btn-outline-secondary text-center justify-content-center"
            data-bs-toggle="modal" data-bs-target="#nouvelleSessionModal"
            dusk="btn-open-nouvelle-session">
        <i class="bi bi-plus-circle me-1"></i>Nouvelle évaluation
    </button>
    @endif
    <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-secondary text-center justify-content-center">
        <i class="bi bi-pencil me-1"></i>Modifier le profil
    </a>
    <form method="POST" action="{{ route('clients.destroy', $client) }}"
          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-secondary btn-delete text-center justify-content-center">
            <i class="bi bi-trash me-1"></i>Supprimer
        </button>
    </form>
</div>
@endsection
