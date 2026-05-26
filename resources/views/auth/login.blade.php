<x-guest-layout>
    <div style="width: 100%; max-width: 420px;">
        <div class="card p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-heart-pulse login-logo" style="font-size: 2.5rem;"></i>
                <h1 class="h4 mt-2 fw-bold" style="color: #1a2f5e;">Profilage Alimentaire</h1>
                <p class="text-muted small">Connectez-vous pour accéder à votre espace</p>
            </div>

            @if (session('status'))
                <div class="alert alert-info mb-3">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-medium">Adresse email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="votre@email.com"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-medium">Mot de passe</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                        <label class="form-check-label text-muted small" for="remember_me">
                            Se souvenir de moi
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-medium py-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
