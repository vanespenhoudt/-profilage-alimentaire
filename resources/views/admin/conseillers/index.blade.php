@extends('layouts.app')

@section('title', 'Conseillers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title mb-0">
        <i class="bi bi-person-badge me-2"></i>Conseillers
    </h1>
    <a href="{{ route('admin.conseillers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nouveau conseiller
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($conseillers->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-person-badge" style="font-size:2rem;"></i>
                <p class="mt-2">Aucun conseiller enregistré.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Nom</th>
                            <th>Email</th>
                            <th>Code</th>
                            <th>Statut</th>
                            <th>Clients</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conseillers as $conseiller)
                        <tr>
                            <td class="ps-3 fw-medium">{{ $conseiller->name }}</td>
                            <td class="text-muted">{{ $conseiller->email }}</td>
                            <td>
                                @if($conseiller->code)
                                    <span class="badge text-bg-secondary">{{ $conseiller->code }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @if($conseiller->active)
                                    <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Actif</span>
                                @else
                                    <span class="badge text-bg-danger"><i class="bi bi-x-circle me-1"></i>Inactif</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-bg-light text-dark border">{{ $conseiller->clients_count }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.conseillers.edit', $conseiller) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.conseillers.toggle', $conseiller) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm {{ $conseiller->active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $conseiller->active ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi bi-{{ $conseiller->active ? 'pause-circle' : 'play-circle' }}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($conseillers->hasPages())
            <div class="px-3 py-2">
                {{ $conseillers->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
