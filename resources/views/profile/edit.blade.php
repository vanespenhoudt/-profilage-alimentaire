@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <h1 class="page-title mb-0">
        <i class="bi bi-person-gear me-2"></i>Mon profil
    </h1>
</div>

<div class="row g-3">
    {{-- Informations du compte --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header border-0 pb-0">
                <h6 class="card-sec-title">
                    <i class="bi bi-person-lines-fill me-2 text-green"></i>Informations du compte
                </h6>
            </div>
            <div class="card-body">

                @if(session('status') === 'profile-updated')
                    <div class="alert alert-success-soft py-2 small mb-3">
                        <i class="bi bi-check-circle me-1"></i>Profil mis à jour.
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Nom complet <span class="required-star">*</span></label>
                        <input type="text" name="name" id="name"
                               value="{{ old('name', $user->name) }}"
                               class="form-control @error('name') is-invalid @enderror"
                               required autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-medium">Email <span class="required-star">*</span></label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email', $user->email) }}"
                               class="form-control @error('email') is-invalid @enderror"
                               required autocomplete="email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Enregistrer
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- Changer le mot de passe --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header border-0 pb-0">
                <h6 class="card-sec-title">
                    <i class="bi bi-lock me-2 text-green"></i>Changer le mot de passe
                </h6>
            </div>
            <div class="card-body">

                @if(session('status') === 'password-updated')
                    <div class="alert alert-success-soft py-2 small mb-3">
                        <i class="bi bi-check-circle me-1"></i>Mot de passe mis à jour.
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-medium">Mot de passe actuel <span class="required-star">*</span></label>
                        <input type="password" name="current_password" id="current_password"
                               class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
                               autocomplete="current-password">
                        @if($errors->updatePassword->has('current_password'))
                            <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">Nouveau mot de passe <span class="required-star">*</span></label>
                        <input type="password" name="password" id="password"
                               class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
                               placeholder="Minimum 8 caractères"
                               autocomplete="new-password">
                        @if($errors->updatePassword->has('password'))
                            <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-medium">Confirmer le mot de passe <span class="required-star">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control"
                               autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-lock me-1"></i>Mettre à jour
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
