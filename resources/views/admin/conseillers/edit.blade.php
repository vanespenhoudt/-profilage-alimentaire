@extends('layouts.app')

@section('title', 'Modifier ' . $user->name)

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.conseillers.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="page-title mb-0">
        <i class="bi bi-pencil-square me-2"></i>Modifier {{ $user->name }}
    </h1>
    @if($user->active)
        <span class="badge text-bg-success ms-2">Actif</span>
    @else
        <span class="badge badge-inactif ms-2">Inactif</span>
    @endif
</div>

<div class="card mw-600">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.conseillers.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label fw-medium">Nom complet <span class="required-star">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-medium">Adresse email <span class="required-star">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="code" class="form-label fw-medium">Code conseiller</label>
                <input type="text" name="code" id="code" value="{{ old('code', $user->code) }}"
                       class="form-control @error('code') is-invalid @enderror">
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr>

            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Laissez les champs mot de passe vides pour ne pas le modifier.
            </p>

            <div class="mb-3">
                <label for="password" class="form-label fw-medium">Nouveau mot de passe</label>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Laisser vide pour conserver l'actuel">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-medium">Confirmer le nouveau mot de passe</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control"
                       placeholder="Laisser vide pour conserver l'actuel">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                </button>
                <a href="{{ route('admin.conseillers.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
