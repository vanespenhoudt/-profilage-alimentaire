<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Profilage Alimentaire') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; height: 100%; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--color-bg-page);
            color: var(--color-navy);
            min-height: 100vh;
        }

        /* ── Layout split ─────────────────────────── */
        .auth-wrap {
            display: flex;
            min-height: 100vh;
        }

        /* ── Panneau gauche (brand) ───────────────── */
        .auth-brand {
            width: 420px;
            flex-shrink: 0;
            background: var(--color-navy);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 44px;
            position: relative;
            overflow: hidden;
        }

        /* Cercles décoratifs */
        .auth-brand::before,
        .auth-brand::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(101,204,184,0.08);
        }
        .auth-brand::before {
            width: 340px;
            height: 340px;
            bottom: -80px;
            right: -100px;
        }
        .auth-brand::after {
            width: 180px;
            height: 180px;
            top: -40px;
            left: -60px;
            background: rgba(101,204,184,0.05);
        }

        .brand-top { position: relative; z-index: 1; }
        .brand-logo-wrap {
            width: 52px;
            height: 52px;
            background: rgba(101,204,184,0.15);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
        }
        .brand-logo-wrap i {
            font-size: 26px;
            color: var(--color-primary);
        }
        .brand-name {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 26px;
            color: #fff;
            letter-spacing: -0.03em;
            line-height: 1.1;
            margin-bottom: 12px;
        }
        .brand-tagline {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            color: rgba(255,255,255,0.50);
            line-height: 1.5;
            max-width: 260px;
        }

        /* Pilules features */
        .brand-features {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .brand-feat {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-feat-icon {
            width: 34px;
            height: 34px;
            background: rgba(101,204,184,0.12);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .brand-feat-icon i {
            font-size: 15px;
            color: var(--color-primary);
        }
        .brand-feat-text {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            color: rgba(255,255,255,0.65);
        }

        .brand-footer {
            position: relative;
            z-index: 1;
            font-family: 'Outfit', sans-serif;
            font-size: 11px;
            color: rgba(255,255,255,0.25);
        }

        /* ── Panneau droit (form) ─────────────────── */
        .auth-form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 32px;
            background: var(--color-bg-page);
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 400px;
        }

        .auth-heading {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 22px;
            color: var(--color-navy);
            letter-spacing: -0.03em;
            margin-bottom: 4px;
        }
        .auth-subheading {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            color: var(--color-text-muted);
            margin-bottom: 28px;
        }

        /* Inputs */
        .auth-label {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--color-text-muted);
            margin-bottom: 5px;
            display: block;
        }
        .auth-input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1.5px solid var(--color-border-light);
            border-radius: var(--radius-input);
            background: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            color: var(--color-navy);
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .auth-input::placeholder { color: var(--color-text-muted); }
        .auth-input:focus {
            border-color: var(--color-primary-mid);
            box-shadow: 0 0 0 3px var(--color-focus-ring);
        }
        .auth-input.is-invalid {
            border-color: #dc3545;
        }
        .auth-input.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220,53,69,.12);
        }
        .invalid-feedback {
            font-size: 12px;
            color: #dc3545;
            margin-top: 4px;
        }

        /* Bouton principal */
        .auth-btn {
            width: 100%;
            height: 44px;
            background: var(--color-navy);
            border: none;
            border-radius: var(--radius-input);
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 13px;
            color: var(--color-primary);
            cursor: pointer;
            transition: background .15s, transform .1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .auth-btn:hover {
            background: var(--color-primary-dark);
            color: #fff;
        }
        .auth-btn:active { transform: scale(0.99); }

        /* Remember me */
        .auth-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .auth-check input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--color-primary-dark);
            cursor: pointer;
        }
        .auth-check label {
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
            color: var(--color-text-muted);
            cursor: pointer;
            margin: 0;
        }

        /* Divider */
        .auth-divider {
            height: 1px;
            background: var(--color-border-light);
            margin: 20px 0;
        }

        /* Lien mot de passe */
        .auth-link {
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
            color: var(--color-primary-dark);
            text-decoration: none;
        }
        .auth-link:hover { text-decoration: underline; }

        /* Alertes */
        .auth-alert {
            padding: 10px 14px;
            border-radius: var(--radius-input);
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .auth-alert-error {
            background: #fff5f5;
            border: 1px solid #fecaca;
            color: #dc3545;
        }
        .auth-alert-info {
            background: var(--color-bg-tint);
            border: 1px solid var(--color-border-tint);
            color: var(--color-primary-dark);
        }

        /* ── Responsive mobile ────────────────────── */
        @media (max-width: 768px) {
            .auth-brand { display: none; }
            .auth-form-panel { padding: 32px 20px; }
        }
    </style>
</head>
<body>
<div class="auth-wrap">

    {{-- Panneau gauche brand --}}
    <div class="auth-brand">
        <div class="brand-top">
            <div class="brand-logo-wrap">
                <i class="bi bi-heart-pulse"></i>
            </div>
            <div class="brand-name">Profilage<br>Alimentaire</div>
            <p class="brand-tagline">Accompagnement nutritionnel personnalisé basé sur votre profil unique.</p>
        </div>

        <div class="brand-features">
            <div class="brand-feat">
                <div class="brand-feat-icon"><i class="bi bi-clipboard2-pulse"></i></div>
                <span class="brand-feat-text">Questionnaire nutritionnel complet</span>
            </div>
            <div class="brand-feat">
                <div class="brand-feat-icon"><i class="bi bi-bar-chart-line"></i></div>
                <span class="brand-feat-text">Analyse et bilan personnalisé</span>
            </div>
            <div class="brand-feat">
                <div class="brand-feat-icon"><i class="bi bi-journal-richtext"></i></div>
                <span class="brand-feat-text">Plan alimentaire sur mesure</span>
            </div>
            <div class="brand-feat">
                <div class="brand-feat-icon"><i class="bi bi-people"></i></div>
                <span class="brand-feat-text">Suivi client simplifié</span>
            </div>
        </div>

        <div class="brand-footer">© {{ date('Y') }} Profilage Alimentaire</div>
    </div>

    {{-- Panneau droit form --}}
    <div class="auth-form-panel">
        <div class="auth-form-wrap">
            {{ $slot }}
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
