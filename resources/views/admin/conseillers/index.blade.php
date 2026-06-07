@extends('layouts.app')

@section('title', 'Conseillers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title mb-0">
        <i class="bi bi-person-badge me-2"></i>Conseillers
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inviteModal"
                dusk="btn-open-invite-modal">
            <i class="bi bi-envelope-plus me-1"></i>Inviter un conseiller
        </button>
        <a href="{{ route('admin.conseillers.create') }}" class="btn btn-outline-secondary">
            <i class="bi bi-plus-lg me-1"></i>Créer manuellement
        </a>
    </div>
</div>

{{-- Modal invitation --}}
<div class="modal fade" id="inviteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-content-rounded">
            <div class="modal-header modal-header-navy">
                <h5 class="modal-title"><i class="bi bi-envelope-plus me-2"></i>Inviter un conseiller</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.invitations.store') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Un email d'invitation sera envoyé automatiquement au conseiller avec un lien valable 7 jours.
                    </p>
                    <label class="form-label fw-semibold" for="invite_email">Adresse email du conseiller</label>
                    <input type="email" name="email" id="invite_email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="conseiller@exemple.be"
                           value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Envoyer l'invitation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Lien généré --}}
@if(session('token_generated') || ($invitations->filter(fn($i) => $i->isPending())->isNotEmpty() && session('success')))
@endif

{{-- Panel invitations --}}
@if($invitations->isNotEmpty())
<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2 py-2 modal-header-navy">
        <i class="bi bi-envelope-check text-white"></i>
        <span class="fw-semibold text-white">Invitations</span>
        <span class="badge bg-light text-dark ms-auto">{{ $invitations->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Email invité</th>
                        <th>Invité par</th>
                        <th>Statut</th>
                        <th>Créée le</th>
                        <th>Expire le</th>
                        <th>Lien de secours</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $invitation)
                    <tr class="{{ $invitation->isUsed() || $invitation->isExpired() ? 'table-light text-muted' : '' }}">
                        <td class="ps-3 fw-medium">{{ $invitation->email }}</td>
                        <td class="small text-muted">{{ $invitation->invitedBy?->name ?? '—' }}</td>
                        <td>
                            @if($invitation->isUsed())
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Utilisée le {{ $invitation->used_at->format('d/m/Y') }}
                                </span>
                            @elseif($invitation->isExpired())
                                <span class="badge bg-secondary">
                                    <i class="bi bi-clock-history me-1"></i>Expirée
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-hourglass-split me-1"></i>En attente
                                </span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $invitation->created_at->format('d/m/Y') }}</td>
                        <td class="small text-muted">{{ $invitation->expires_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @if($invitation->isPending())
                                <div class="input-group input-group-sm mw-320">
                                    <input type="text" class="form-control font-monospace"
                                           value="{{ route('invitation.show', $invitation->token) }}"
                                           readonly id="inv-{{ $invitation->id }}">
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="navigator.clipboard.writeText(document.getElementById('inv-{{ $invitation->id }}').value).then(()=>this.innerHTML='<i class=\'bi bi-check\'></i>')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if(!$invitation->isUsed())
                            <form method="POST" action="{{ route('admin.invitations.destroy', $invitation) }}"
                                  onsubmit="return confirm('Supprimer cette invitation ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-secondary btn-danger-outline">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($errors->any() && old('email'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('inviteModal')).show();
});
</script>
@endif

<div class="card">
    <div class="card-body p-0">
        @if($conseillers->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-person-badge fs-2"></i>
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
                                    <span class="badge badge-inactif"><i class="bi bi-x-circle me-1"></i>Inactif</span>
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
                                            title="{{ $conseiller->active ? 'Désactiver' : 'Activer' }}"
                                            dusk="btn-toggle-conseiller-{{ $conseiller->id }}">
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
