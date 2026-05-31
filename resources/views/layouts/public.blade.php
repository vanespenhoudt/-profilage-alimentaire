<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Questionnaire nutritionnel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}?v={{ filemtime(public_path('css/variables.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}?v={{ filemtime(public_path('css/global.css')) }}">
    <style>
        /* ── Layout spécifique public ──────────────────────────────── */
        body { min-height: 100vh; }

        .top-bar { background: var(--color-primary); height: 54px; display: flex; align-items: center; }
        .top-bar .brand { color: var(--color-text-on-green); font-family: 'Syne', sans-serif; font-weight: 700; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .top-bar .brand:hover { color: var(--color-text-on-green); }
        .top-bar .brand-icon { width: 28px; height: 28px; background: var(--color-white-20); border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }

        .page-wrap  { max-width: 860px; margin: 0 auto; padding: 20px 16px 130px; }
        .submit-bar { background: var(--color-bg-card); border-top: 1px solid var(--color-border-card); z-index: 1050; padding: 12px 20px; }

        .card-header {
            border-radius: var(--radius-card) var(--radius-card) 0 0 !important;
            border-bottom: 1px solid var(--color-border-card);
            background: var(--color-bg-card);
        }

        /* barre de progression publique — fond transparent sur vert */
        .progress { background: var(--color-white-30); }
        .progress-bar { background: var(--color-bg-card); }
        .progress.on-panel { background: var(--color-border-card); }
        .progress.on-panel .progress-bar { background: var(--color-primary-dark); }
    </style>
</head>
<body>

    <!-- Topbar publique -->
    <div class="top-bar">
        <div class="container">
            <span class="brand">
                <span class="brand-icon"><i class="bi bi-heart-pulse"></i></span>
                Profilage Alimentaire
            </span>
        </div>
    </div>

    <div class="page-wrap">
        @yield('content')
    </div>

    <div class="save-toast" id="saveToast">
        <i class="bi bi-check-circle me-2"></i>Progression sauvegardée
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
