@extends('layouts.app')

@section('title', 'Nouveau client')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="page-title mb-0">
        <i class="bi bi-person-plus me-2"></i>Nouveau client
    </h1>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('clients.store') }}">
            @csrf

            @if($isSuperAdmin)
            <div class="mb-3">
                <label for="conseiller_id" class="form-label fw-medium">Conseiller <span class="text-danger">*</span></label>
                <select name="conseiller_id" id="conseiller_id" class="form-select @error('conseiller_id') is-invalid @enderror">
                    <option value="">-- Sélectionner un conseiller --</option>
                    @foreach($conseillers as $conseiller)
                        <option value="{{ $conseiller->id }}" {{ old('conseiller_id') == $conseiller->id ? 'selected' : '' }}>
                            {{ $conseiller->name }}
                        </option>
                    @endforeach
                </select>
                @error('conseiller_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <hr>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="prenom" class="form-label fw-medium">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" id="prenom" value="{{ old('prenom') }}"
                           class="form-control @error('prenom') is-invalid @enderror"
                           placeholder="Prénom du client">
                    @error('prenom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="nom" class="form-label fw-medium">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="nom" value="{{ old('nom') }}"
                           class="form-control @error('nom') is-invalid @enderror"
                           placeholder="Nom de famille">
                    @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="tel" class="form-label fw-medium">Téléphone <span class="text-danger">*</span></label>
                    <input type="text" name="tel" id="tel" value="{{ old('tel') }}"
                           class="form-control @error('tel') is-invalid @enderror"
                           placeholder="+32 xxx xx xx xx">
                    @error('tel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label fw-medium">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="client@exemple.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="adresse" class="form-label fw-medium">Adresse</label>
                <textarea name="adresse" id="adresse" rows="2"
                          class="form-control @error('adresse') is-invalid @enderror"
                          placeholder="Adresse complète">{{ old('adresse') }}</textarea>
                @error('adresse')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="bt" class="form-label fw-medium">Bilan terrain</label>
                <textarea name="bt" id="bt" rows="4"
                          class="form-control @error('bt') is-invalid @enderror"
                          placeholder="Informations sur le bilan terrain...">{{ old('bt') }}</textarea>
                @error('bt')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label fw-medium">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                          class="form-control @error('notes') is-invalid @enderror"
                          placeholder="Notes internes...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input @error('rgpd') is-invalid @enderror"
                           type="checkbox" name="rgpd" id="rgpd" value="1"
                           {{ old('rgpd') ? 'checked' : '' }}>
                    <label class="form-check-label" for="rgpd">
                        Le client consent au traitement de ses données personnelles conformément au RGPD.
                        Les données collectées sont utilisées exclusivement dans le cadre du suivi alimentaire.
                        <span class="text-danger">*</span>
                    </label>
                    @error('rgpd')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Créer le client
                </button>
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
