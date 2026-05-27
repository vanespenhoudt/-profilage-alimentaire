@extends('layouts.public')

@section('title', 'Créer votre compte conseiller')

@section('content')

<div class="row justify-content-center">
    <div class="col-sm-10 col-md-8 col-lg-6">

        {{-- En-tête --}}
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 invitation-icon-wrap">
                <i class="bi bi-person-plus fs-2 text-green"></i>
            </div>
            <h1 class="h3 fw-bold mb-1 text-navy">Créez votre compte conseiller</h1>
            <p class="text-muted mb-0">Vous avez été invité à rejoindre Profilage Alimentaire.</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert-warning-soft mb-4 py-2 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('invitation.register', $token) }}">
                    @csrf

                    {{-- Email (lecture seule) --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adresse email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control bg-light"
                                   value="{{ $invitation->email }}" readonly>
                        </div>
                        <div class="form-text">Votre email est défini par votre invitation.</div>
                    </div>

                    {{-- Nom complet --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nom complet <span class="required-star">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Prénom Nom"
                                   required autofocus autocomplete="name">
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Mot de passe <span class="required-star">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimum 8 caractères"
                                   required autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="const i=document.getElementById('password');i.type=i.type==='password'?'text':'password'">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirmation --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirmer le mot de passe <span class="required-star">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control"
                                   placeholder="Répétez votre mot de passe"
                                   required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                            <i class="bi bi-check-circle me-2"></i>Créer mon compte
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <p class="text-center text-muted small mt-3">
            Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a>
        </p>

    </div>
</div>

@endsection
