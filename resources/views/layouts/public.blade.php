<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Questionnaire nutritionnel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary: #1a2f5e; --primary-light: #2a4a8e; --bg: #f0f2f8; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: #1c1c1a; min-height: 100vh; }
        .top-bar { background: var(--primary); padding: 14px 0; }
        .top-bar .brand { color: #fff; font-weight: 700; font-size: 1.1rem; text-decoration: none; }
        .top-bar .brand i { color: rgba(255,255,255,.7); }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(26,47,94,.08); }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-light); border-color: var(--primary-light); }
        .accordion-button:not(.collapsed) { background: #eef1f8; color: var(--primary); box-shadow: none; }
        .accordion-button:focus { box-shadow: none; }
        .page-wrap { max-width: 860px; margin: 0 auto; padding: 32px 16px 120px; }
        .save-toast {
            position: fixed; bottom: 100px; right: 24px; z-index: 2000;
            background: #1a3d25; color: #fff; padding: 8px 16px;
            border-radius: 8px; font-size: .85rem; opacity: 0;
            transition: opacity .3s ease;
            pointer-events: none;
        }
        .save-toast.show { opacity: 1; }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container">
            <span class="brand"><i class="bi bi-heart-pulse me-2"></i>Profilage Alimentaire</span>
        </div>
    </div>

    <div class="page-wrap">
        @yield('content')
    </div>

    <div class="save-toast" id="saveToast"><i class="bi bi-check-circle me-2"></i>Progression sauvegardée</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
