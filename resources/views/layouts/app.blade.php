<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Profilage Alimentaire')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}?v={{ filemtime(public_path('css/variables.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}?v={{ filemtime(public_path('css/global.css')) }}">
    <style>
        /* ── Layout spécifique app ─────────────────────────────────── */
        :root { --nav-h: 54px; }

        .green-zone   { background: var(--color-primary); }
        .app-nav      { height: var(--nav-h); display: flex; align-items: center; }
        .app-nav-inner {
            max-width: 1100px; margin: 0 auto; width: 100%;
            padding: 0 20px; display: flex; align-items: center; gap: 24px;
        }
        .nav-brand {
            color: #FFFFFF; font-family: 'Syne', sans-serif;
            font-weight: 700; font-size: 15px; text-decoration: none;
            display: flex; align-items: center; gap: 8px; flex-shrink: 0;
            background: rgba(0,0,0,0.20); border-radius: var(--radius-pill); padding: 5px 14px;
        }
        .nav-brand:hover { color: #FFFFFF; text-decoration: none; background: rgba(0,0,0,0.30); }
        .brand-icon {
            width: 28px; height: 28px; background: transparent;
            border-radius: 9px; display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .nav-links    { display: flex; gap: 4px; margin-left: 16px; }
        .nav-link     { font-family: 'Outfit', sans-serif; font-size: 12px; color: #FFFFFF !important; background: rgba(0,0,0,0.20) !important; text-decoration: none !important; padding: 6px 10px; border-radius: 8px; transition: background .15s; }
        .nav-link:hover  { background: rgba(0,0,0,0.35) !important; }
        .nav-link.active { background: rgba(0,0,0,0.40) !important; font-weight: 600; }
        .nav-user-pill {
            margin-left: auto; background: rgba(0,0,0,0.20);
            border-radius: var(--radius-pill); padding: 5px 14px;
            font-family: 'Outfit', sans-serif; font-size: 13px;
            color: #FFFFFF; font-weight: 500; display: flex; align-items: center; gap: 6px; flex-shrink: 0;
        }
        .content-panel {
            background: var(--color-bg-page);
            min-height: calc(100vh - var(--nav-h));
            padding: 20px; max-width: 1100px; margin: 0 auto;
        }
        .card-header {
            border-radius: var(--radius-card) var(--radius-card) 0 0 !important;
            border-bottom: 1px solid var(--color-border-card);
            background: var(--color-bg-card);
        }

        .nav-user-pill      { flex-shrink: 1; min-width: 0; }
        .nav-user-name-text { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 140px; }

        @media (max-width: 767px) {
            .app-nav-inner     { gap: 6px; padding: 0 10px; }
            .nav-brand         { padding: 5px 9px; }
            .nav-brand-text    { display: none; }
            .nav-links         { margin-left: 4px; gap: 2px; }
            .nav-link          { padding: 5px 9px; font-size: 11px; }
            .nav-user-pill     { display: none; }
            .btn-topbar-logout { padding: 5px 9px; font-size: 0; }
            .btn-topbar-logout i { font-size: 14px; margin: 0 !important; }
        }
    </style>
</head>
<body>

    <!-- Zone verte (nav + slot dynamique) -->
    <div class="green-zone">

        <!-- Navbar horizontale -->
        <nav class="app-nav">
            <div class="app-nav-inner">
                <a class="nav-brand" href="{{ route('dashboard') }}">
                    <span class="brand-icon"><i class="bi bi-heart-pulse"></i></span>
                    <span class="nav-brand-text">Profilage Alimentaire</span>
                </a>

                <div class="nav-links">
                    @if(!auth()->user()->isSuperAdmin())
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        Tableau de bord
                    </a>
                    <a href="{{ route('clients.index') }}"
                       class="nav-link {{ request()->is('clients*') ? 'active' : '' }}">
                        Clients
                    </a>
                    @endif
                    @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.conseillers.index') }}"
                       class="nav-link {{ request()->is('admin/conseillers*') ? 'active' : '' }}">
                        Conseillers
                    </a>
                    @endif
                    <a href="{{ route('profile.edit') }}"
                       class="nav-link {{ request()->is('profile') ? 'active' : '' }}">
                        Mon profil
                    </a>
                </div>

                <div class="nav-user-pill">
                    <i class="bi bi-person-circle"></i>
                    <span class="nav-user-name-text">{{ auth()->user()->name }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button class="btn-topbar-logout" dusk="btn-logout">
                        <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                    </button>
                </form>
            </div>
        </nav>

        <!-- Slot zone verte : breadcrumb, titre client, barre de progression -->
        <div id="green-slot">@yield('green-header')</div>

    </div><!-- /green-zone -->

    <!-- Panel contenu principal -->
    <div class="content-panel">

        @if(session('success'))
            <div class="alert alert-success-soft alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-warning-soft alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')

    </div><!-- /content-panel -->

    @if(auth()->check() && !auth()->user()->isSuperAdmin())
    <footer class="text-center py-3" style="font-size:11px;color:var(--color-text-muted);">
        <a href="{{ route('politique.conseillers') }}" class="link-green-dark" target="_blank">Politique de confidentialité</a>
    </footer>
    @endif

    <!-- Barre bas fixe (optionnelle par vue) -->
    <div id="bottom-bar">@yield('bottom-bar')</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
