<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Profilage Alimentaire') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/variables.css">
    <style>
        /* ── Aliases rétrocompatibilité ────────────────────────────── */
        :root {
            --green:      var(--color-primary);
            --green-dark: var(--color-primary-dark);
            --navy:       var(--color-navy);
            --bg:         var(--color-bg-page);
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--color-bg-page);
            color: var(--color-navy);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            color: var(--color-navy);
        }
        .btn-primary {
            background: var(--color-navy);
            border-color: var(--color-navy);
            color: var(--color-primary);
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 13px;
        }
        .btn-primary:hover {
            background: var(--color-primary-dark);
            border-color: var(--color-primary-dark);
            color: var(--color-text-on-green);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: none;
            background: var(--color-bg-card);
        }
        .login-logo { color: var(--color-navy); }
        .form-control {
            border: 1.5px solid var(--color-border-light);
            background: var(--color-bg-input);
            border-radius: var(--radius-input);
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            color: var(--color-navy);
        }
        .form-control:focus {
            border-color: var(--color-primary-mid);
            box-shadow: 0 0 0 3px var(--color-focus-ring);
            background: var(--color-bg-card);
            color: var(--color-navy);
        }
        .form-label {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--color-text-muted);
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
        {{ $slot }}
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
