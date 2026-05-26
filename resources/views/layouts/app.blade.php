<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Profilage Alimentaire')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2f5e;
            --primary-light: #2a4a8e;
            --bg: #f0f2f8;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); }
        .navbar { background: var(--primary) !important; }
        .sidebar { background: var(--primary); min-height: calc(100vh - 56px); }
        .sidebar .nav-link { color: rgba(255,255,255,0.75); padding: .6rem 1rem; border-radius: 6px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.15); color: #fff; }
        .sidebar .nav-link i { width: 20px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(26,47,94,.08); }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-light); border-color: var(--primary-light); }
        .badge-role { background: var(--primary); }
        .stat-card { border-left: 4px solid var(--primary); }
        .table th { font-weight: 600; font-size: .85rem; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; }
        .page-title { font-size: 1.5rem; font-weight: 600; color: var(--primary); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
                <i class="bi bi-heart-pulse me-2"></i>Profilage Alimentaire
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50 small">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Déconnexion</button>
                </form>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar py-3">
                <nav class="nav flex-column gap-1">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->is('clients*') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i>Clients
                    </a>
                    @if(auth()->user()->isSuperAdmin())
                    <hr style="border-color:rgba(255,255,255,.2)">
                    <a href="{{ route('admin.conseillers.index') }}" class="nav-link {{ request()->is('admin/conseillers*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge me-2"></i>Conseillers
                    </a>
                    @endif
                </nav>
            </div>
            <div class="col-md-10 py-4 px-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
