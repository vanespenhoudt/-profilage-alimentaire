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
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <style>
        /* ── Layout spécifique app ─────────────────────────────────── */
        :root { --nav-h: 54px; }

        .green-zone   { background: var(--color-primary); }
        .app-nav      { height: var(--nav-h); display: flex; align-items: center; background: var(--color-navy); }
        .app-nav-inner {
            max-width: 1100px; margin: 0 auto; width: 100%;
            padding: 0 20px; display: flex; align-items: center; gap: 24px;
        }
        .nav-brand {
            color: var(--color-text-on-green); font-family: 'Syne', sans-serif;
            font-weight: 700; font-size: 15px; text-decoration: none;
            display: flex; align-items: center; gap: 8px; flex-shrink: 0;
        }
        .nav-brand:hover { color: var(--color-text-on-green); text-decoration: none; }
        .brand-icon {
            width: 30px; height: 30px; background: var(--color-white-20);
            border-radius: 9px; display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .nav-links    { display: flex; gap: 4px; margin-left: 16px; }
        .nav-user-pill {
            margin-left: auto; background: rgba(255,255,255,0.12);
            border-radius: var(--radius-pill); padding: 5px 14px;
            font-family: 'Outfit', sans-serif; font-size: 13px;
            color: #FFFFFF; display: flex; align-items: center; gap: 6px; flex-shrink: 0;
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
                    Profilage Alimentaire
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
                </div>

                <div class="nav-user-pill">
                    <i class="bi bi-person-circle"></i>{{ auth()->user()->name }}
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button class="btn-topbar-logout">
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

    <!-- Barre bas fixe (optionnelle par vue) -->
    <div id="bottom-bar">@yield('bottom-bar')</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
