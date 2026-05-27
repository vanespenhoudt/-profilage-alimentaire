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

    @isset($totalConseillers)
    <div class="col-md-4">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center stat-icon-wrap">
                    <i class="bi bi-person-badge-fill fs-icon-stat text-navy"></i>
                </div>
                <div>
                    <div class="text-muted small">Conseillers actifs</div>
                    <div class="fw-bold fs-4 text-navy">{{ $totalConseillers }}</div>
                </div>
            </div>
        </div>
    </div>
    @endisset
</div>

<div class="card">
    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Clients récents</h5>
        <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-primary">Voir tous</a>
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
                            @isset($totalConseillers)
                            <th>Conseiller</th>
                            @endisset
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
                            @isset($totalConseillers)
                            <td>{{ $client->conseiller?->name ?? '-' }}</td>
                            @endisset
                            <td class="text-muted small">{{ $client->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
