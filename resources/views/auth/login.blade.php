<x-guest-layout>

    <div class="auth-heading">Connexion</div>
    <p class="auth-subheading">Accédez à votre espace conseiller</p>

    @if (session('status'))
        <div class="auth-alert auth-alert-info">
            <i class="bi bi-info-circle"></i>
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="auth-alert auth-alert-error">
            <i class="bi bi-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="auth-label">Adresse email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="auth-input @error('email') is-invalid @enderror"
                required
                autofocus
                autocomplete="username"
                placeholder="votre@email.com"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="auth-label">Mot de passe</label>
            <input
                id="password"
                type="password"
                name="password"
                class="auth-input @error('password') is-invalid @enderror"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="auth-check">
                <input type="checkbox" name="remember" id="remember_me">
                <label for="remember_me">Se souvenir de moi</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <button type="submit" class="auth-btn">
            <i class="bi bi-box-arrow-in-right"></i>
            Se connecter
        </button>

    </form>

</x-guest-layout>
