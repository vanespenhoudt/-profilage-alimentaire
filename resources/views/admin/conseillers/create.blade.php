@extends('layouts.app')

@section('title', 'Nouveau conseiller')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.conseillers.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="page-title mb-0">
        <i class="bi bi-person-plus me-2"></i>Nouveau conseiller
    </h1>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.conseillers.store') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label fw-medium">Nom complet <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="form-control @error('name') is-invalid @enderror"
                       placeholder="Prénom Nom">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-medium">Adresse email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="conseiller@exemple.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="code" class="form-label fw-medium">Code conseiller</label>
                <input type="text" name="code" id="code" value="{{ old('code') }}"
                       class="form-control @error('code') is-invalid @enderror"
                       placeholder="ex: CONS-001">
                <div class="form-text">Identifiant optionnel pour ce conseiller.</div>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr>

            <div class="mb-3">
                <label for="password" class="form-label fw-medium">Mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Minimum 8 caractères">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-medium">Confirmer le mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control"
                       placeholder="Répétez le mot de passe">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Créer le conseiller
                </button>
                <a href="{{ route('admin.conseillers.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
