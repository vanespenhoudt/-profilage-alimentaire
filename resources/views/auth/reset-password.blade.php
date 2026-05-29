<x-guest-layout>

    <div class="auth-heading">Nouveau mot de passe</div>
    <p class="auth-subheading">Choisissez un nouveau mot de passe sécurisé.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="auth-label">Adresse email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
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

        <div class="mb-3">
            <label for="password" class="auth-label">Nouveau mot de passe</label>
            <input
                id="password"
                type="password"
                name="password"
                class="auth-input @error('password') is-invalid @enderror"
                required
                autocomplete="new-password"
                placeholder="••••••••"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="auth-label">Confirmer le mot de passe</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="auth-input @error('password_confirmation') is-invalid @enderror"
                required
                autocomplete="new-password"
                placeholder="••••••••"
            >
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="auth-btn">
            <i class="bi bi-shield-check"></i>
            Réinitialiser le mot de passe
        </button>
    </form>

</x-guest-layout>
