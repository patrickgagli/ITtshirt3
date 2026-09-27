<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Admin — @yield('title', 'Tableau de bord')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0D6EFD;
            --surface-bg: #F8FAFC;
            --card-radius: 1rem;
            --font-family: 'Inter', sans-serif;
        }
        body {
            font-family: var(--font-family);
            background-color: var(--surface-bg);
            color: #1E293B;
            min-height: 100vh;
        }
        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid rgba(0,0,0,0.07);
        }
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--primary-color) !important;
        }
        .admin-pill {
            background: rgba(220,38,38,0.1);
            color: #dc2626;
            padding: 0.2rem 0.6rem;
            border-radius: 2rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            vertical-align: middle;
        }
        .nav-link.active {
            color: var(--primary-color) !important;
            font-weight: 700;
        }
        .admin-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748B;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
        }
        .admin-table td {
            vertical-align: middle;
            border-color: #f1f5f9;
        }
        .admin-table tr:hover td {
            background: #f8fafc;
        }
        .card {
            border-radius: var(--card-radius);
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        }
        .form-control, .form-select {
            border-radius: 0.65rem;
            border-color: rgba(0,0,0,0.12);
            padding: 0.6rem 0.9rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.12);
        }
        .btn { border-radius: 0.65rem; }
        .page-heading {
            font-weight: 800;
            letter-spacing: -0.02em;
        }
    </style>
    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand me-3" href="{{ route('home') }}">
                ITtshirt3 <span class="admin-pill">Admin</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto gap-1">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                           href="{{ route('admin.dashboard') }}">
                            <i class="fa-solid fa-gauge me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                           href="{{ route('admin.products.index') }}">
                            <i class="fa-solid fa-shirt me-1"></i>Produits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                           href="{{ route('admin.categories.index') }}">
                            <i class="fa-solid fa-tags me-1"></i>Catégories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                           href="{{ route('admin.orders.index') }}">
                            <i class="fa-solid fa-bag-shopping me-1"></i>Commandes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                           href="{{ route('admin.settings.edit') }}">
                            <i class="fa-solid fa-sliders me-1"></i>Paramètres
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-muted" href="{{ route('products.index') }}" target="_blank">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Voir la vitrine
                        </a>
                    </li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted small fw-semibold">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('auth.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container-fluid px-4">

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')

        </div>
    </main>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</body>
</html>
