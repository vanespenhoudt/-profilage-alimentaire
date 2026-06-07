@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<h1 class="page-title">
    <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
</h1>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center stat-icon-wrap">
                    <i class="bi bi-people-fill fs-icon-stat text-navy"></i>
                </div>
                <div>
                    <div class="text-muted small">Mes clients</div>
                    <div class="fw-bold fs-4 text-navy">{{ $totalClients }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Clients récents</h5>
        <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-primary">Tous les clients</a>
    </div>
    <div class="card-body p-0">
        @if($clientsRecents->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-2"></i>
                <p class="mt-2">Aucun client pour le moment.</p>
                <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter un client
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Code</th>
                            <th>Nom complet</th>
                            <th>Téléphone</th>
                            <th>Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientsRecents as $client)
                        <tr>
                            <td class="ps-3">
                                <span class="badge text-bg-secondary">{{ $client->code }}</span>
                            </td>
                            <td class="fw-medium">{{ $client->nom_complet }}</td>
                            <td>{{ $client->tel }}</td>
                            <td class="text-muted small">{{ $client->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('clients.destroy', $client) }}" class="d-inline"
                                      onsubmit="return confirm('Supprimer ce client ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Section invitation conseiller --}}
<div class="card mt-4">
    <div class="card-header modal-header-navy d-flex align-items-center gap-2 py-2">
        <i class="bi bi-envelope-plus text-white"></i>
        <span class="fw-semibold text-white">Inviter un collègue conseiller</span>
    </div>
    <div class="card-body">
        @if(session('success') && str_contains(session('success'), 'Invitation'))
            <div class="alert alert-success-soft alert-dismissible fade show py-2 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill me-1" style="font-size:1rem;flex-shrink:0;"></i>
                <span class="fw-medium">{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('invitations.store') }}" class="row g-2 align-items-end mb-4">
            @csrf
            <div class="col-sm-8">
                <label class="form-label fw-semibold small" for="invite_email_dash">Adresse email du nouveau conseiller</label>
                <input type="email" name="email" id="invite_email_dash"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="conseiller@exemple.be"
                       value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-send me-1"></i>Envoyer l'invitation
                </button>
            </div>
        </form>

        @if($myInvitations->isNotEmpty())
        <h6 class="text-muted small fw-semibold mb-2 text-uppercase">Mes invitations</h6>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Envoyée le</th>
                        <th>Expire le</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($myInvitations as $inv)
                    <tr class="{{ $inv->isUsed() || $inv->isExpired() ? 'text-muted' : '' }}">
                        <td class="fw-medium">{{ $inv->email }}</td>
                        <td>
                            @if($inv->isUsed())
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Utilisée</span>
                            @elseif($inv->isExpired())
                                <span class="badge bg-secondary"><i class="bi bi-clock-history me-1"></i>Expirée</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>En attente</span>
                            @endif
                        </td>
                        <td class="small">{{ $inv->created_at->format('d/m/Y') }}</td>
                        <td class="small">{{ $inv->expires_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('invitations.destroy', $inv) }}"
                                  onsubmit="return confirm('Supprimer cette invitation ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-secondary btn-delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <p class="text-muted small mb-0">Aucune invitation envoyée pour le moment.</p>
        @endif
    </div>
</div>
@endsection
