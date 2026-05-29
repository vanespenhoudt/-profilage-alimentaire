<x-guest-layout>

    <div class="auth-heading">Mot de passe oublié</div>
    <p class="auth-subheading">Renseignez votre email pour recevoir un lien de réinitialisation.</p>

    @if (session('status'))
        <div class="auth-alert auth-alert-info">
            <i class="bi bi-check-circle"></i>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="auth-label">Adresse email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="auth-input @error('email') is-invalid @enderror"
                required
                autofocus
                placeholder="votre@email.com"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="auth-btn">
            <i class="bi bi-envelope"></i>
            Envoyer le lien
        </button>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="auth-link">
                <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
            </a>
        </div>
    </form>

</x-guest-layout>
