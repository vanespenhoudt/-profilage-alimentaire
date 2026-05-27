@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title mb-0">
        <i class="bi bi-people me-2"></i>Clients
    </h1>
    <a href="{{ route('clients.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nouveau client
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('clients.index') }}" class="d-flex gap-2">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                class="form-control input-search"
                placeholder="Rechercher par nom, prénom ou code..."
            >
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search me-1"></i>Rechercher
            </button>
            @if($search)
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Effacer
                </a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($clients->isEmpty())
            <div class="text-center py-5 text-muted-pa">
                <i class="bi bi-inbox fs-2"></i>
                <p class="mt-2 fs-13 text-muted-pa">
                    @if($search)
                        Aucun client trouvé pour "{{ $search }}".
                    @else
                        Aucun client enregistré.
                    @endif
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Code</th>
                            <th>Prénom</th>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            @if($isSuperAdmin)
                            <th>Conseiller</th>
                            @endif
                            <th>RGPD</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td class="ps-3">
                                <span class="chip-code">{{ $client->code }}</span>
                            </td>
                            <td>{{ $client->prenom }}</td>
                            <td class="fw-medium">{{ $client->nom }}</td>
                            <td>{{ $client->tel }}</td>
                            <td class="text-muted-pa fs-12">{{ $client->email ?? '-' }}</td>
                            @if($isSuperAdmin)
                            <td class="fs-12">{{ $client->conseiller?->name ?? '-' }}</td>
                            @endif
                            <td>
                                @if($client->rgpd)
                                    <span class="badge-rgpd-ok">Accepté</span>
                                @else
                                    <span class="badge-rgpd-wait">En attente</span>
                                @endif
                            </td>
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
            @if($clients->hasPages())
            <div class="px-3 py-2">
                {{ $clients->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
