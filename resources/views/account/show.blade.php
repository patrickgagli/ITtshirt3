<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Mon compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0D6EFD;
            --surface-bg: #F8FAFC;
            --card-radius: 1.25rem;
            --font-family: 'Inter', sans-serif;
        }
        body {
            font-family: var(--font-family);
            background-color: var(--surface-bg);
            color: #1E293B;
        }
        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--primary-color) !important;
        }
        .account-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #6366f1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.75rem;
            font-weight: 800;
            flex-shrink: 0;
        }
        .section-card {
            background: white;
            border-radius: var(--card-radius);
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        }
        .section-card .card-header-custom {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .section-card .card-body-custom {
            padding: 1.5rem;
        }
        .form-control, .form-select {
            border-radius: 0.75rem;
            border-color: rgba(0,0,0,0.12);
            padding: 0.65rem 1rem;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.12);
        }
        .btn { border-radius: 0.75rem; }
        .meta-row {
            display: flex;
            justify-content: space-between;
            padding: 0.65rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .meta-row:last-child { border-bottom: none; }
        .meta-label { color: #64748B; font-size: 0.875rem; font-weight: 600; }
        .meta-value { font-weight: 600; }
        .role-badge-customer {
            background: rgba(13,110,253,0.1);
            color: var(--primary-color);
            padding: 0.2rem 0.65rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .role-badge-admin {
            background: rgba(220,38,38,0.1);
            color: #dc2626;
            padding: 0.2rem 0.65rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
        }
        footer {
            background: #0F172A;
            color: #94A3B8;
            padding: 40px 0;
            margin-top: 80px;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">ITtshirt3</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="{{ route('products.index') }}">Catalogue</a>
                    </li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-user"></i>
                            <span class="ms-1 small fw-semibold d-none d-lg-inline">{{ $user->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="{{ route('account.show') }}"><i class="fa-solid fa-circle-user me-2"></i>Mon compte</a></li>
                            @if($user->isAdmin())
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge me-2"></i>Administration</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('auth.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">

            {{-- Page heading --}}
            <div class="d-flex align-items-center gap-4 mb-5">
                <div class="account-avatar">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="fw-bold fs-2 mb-0">{{ $user->name }}</h1>
                    <p class="text-muted mb-1">{{ $user->email }}</p>
                    <span class="{{ $user->isAdmin() ? 'role-badge-admin' : 'role-badge-customer' }}">
                        {{ $user->isAdmin() ? 'Administrateur' : 'Client' }}
                    </span>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">

                    {{-- Account summary --}}
                    <div class="section-card mb-4">
                        <div class="card-header-custom">
                            <h6 class="fw-bold mb-0"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Informations</h6>
                        </div>
                        <div class="card-body-custom">
                            <div class="meta-row">
                                <span class="meta-label">Membre depuis</span>
                                <span class="meta-value">{{ $user->created_at->translatedFormat('d F Y') }}</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Rôle</span>
                                <span class="meta-value">{{ $user->isAdmin() ? 'Administrateur' : 'Client' }}</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">E-mail</span>
                                <span class="meta-value small">{{ $user->email }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Order history --}}
                    <div class="section-card">
                        <div class="card-header-custom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fa-solid fa-bag-shopping me-2 text-primary"></i>Mes commandes</h6>
                            @if($recentOrders->isNotEmpty())
                            <a href="{{ route('account.orders.index') }}" class="btn btn-outline-primary btn-sm">Tout voir</a>
                            @endif
                        </div>
                        <div class="card-body-custom">
                        @if($recentOrders->isEmpty())
                            <div class="text-center py-3">
                                <i class="fa-solid fa-clock-rotate-left fa-2x text-muted opacity-25 mb-3 d-block"></i>
                                <p class="text-muted small mb-0">Aucune commande pour le moment.</p>
                            </div>
                        @else
                            @foreach($recentOrders as $order)
                            <a href="{{ route('account.orders.show', $order->uuid) }}" class="text-decoration-none">
                                <div class="meta-row align-items-center" style="gap:0.5rem;">
                                    <div>
                                        <span class="meta-value d-block" style="font-size:0.8rem;">{{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}</span>
                                        <span class="text-muted" style="font-size:0.75rem;">{{ $order->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="ms-auto text-end">
                                        <span class="d-block fw-600" style="font-size:0.85rem;font-weight:600;">{{ $order->formattedTotal() }}</span>
                                        <span class="badge {{ $order->statusBadgeClass() }}" style="font-size:0.65rem;">{{ $order->statusLabel() }}</span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                            <div class="text-center mt-2">
                                <a href="{{ route('account.orders.index') }}" class="small text-primary text-decoration-none">Voir toutes mes commandes →</a>
                            </div>
                        @endif
                        </div>
                    </div>

                </div>

                <div class="col-lg-8">

                    {{-- Profile update form --}}
                    <div class="section-card mb-4">
                        <div class="card-header-custom">
                            <h6 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Modifier le profil</h6>
                        </div>
                        <div class="card-body-custom">

                            @if (session('success_profile'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success_profile') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif

                            @if ($errors->profile->any())
                            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->profile->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif

                            <form method="POST" action="{{ route('account.profile.update') }}" novalidate>
                                @csrf
                                @method('PATCH')

                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="name" class="form-label fw-semibold">Nom complet</label>
                                        <input type="text" id="name" name="name"
                                               class="form-control @if($errors->profile->has('name')) is-invalid @endif"
                                               value="{{ old('name', $user->name) }}"
                                               required autocomplete="name">
                                        @if ($errors->profile->has('name'))
                                        <div class="invalid-feedback">{{ $errors->profile->first('name') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="email" class="form-label fw-semibold">Adresse e-mail</label>
                                        <input type="email" id="email" name="email"
                                               class="form-control @if($errors->profile->has('email')) is-invalid @endif"
                                               value="{{ old('email', $user->email) }}"
                                               required autocomplete="email">
                                        @if ($errors->profile->has('email'))
                                        <div class="invalid-feedback">{{ $errors->profile->first('email') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <label for="current_password" class="form-label fw-semibold">
                                            Mot de passe actuel <span class="text-muted fw-normal small">(requis pour valider)</span>
                                        </label>
                                        <input type="password" id="current_password" name="current_password"
                                               class="form-control @if($errors->profile->has('current_password')) is-invalid @endif"
                                               required autocomplete="current-password">
                                        @if ($errors->profile->has('current_password'))
                                        <div class="invalid-feedback">{{ $errors->profile->first('current_password') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fa-solid fa-floppy-disk me-1"></i>Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Password change form --}}
                    <div class="section-card">
                        <div class="card-header-custom">
                            <h6 class="fw-bold mb-0"><i class="fa-solid fa-lock me-2 text-primary"></i>Changer le mot de passe</h6>
                        </div>
                        <div class="card-body-custom">

                            @if (session('success_password'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success_password') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif

                            @if ($errors->password->any())
                            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->password->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif

                            <form method="POST" action="{{ route('account.password.update') }}" novalidate>
                                @csrf
                                @method('PATCH')

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="current_password_pw" class="form-label fw-semibold">Mot de passe actuel</label>
                                        <input type="password" id="current_password_pw" name="current_password"
                                               class="form-control @if($errors->password->has('current_password')) is-invalid @endif"
                                               required autocomplete="current-password">
                                        @if ($errors->password->has('current_password'))
                                        <div class="invalid-feedback">{{ $errors->password->first('current_password') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="new_password" class="form-label fw-semibold">Nouveau mot de passe</label>
                                        <input type="password" id="new_password" name="password"
                                               class="form-control @if($errors->password->has('password')) is-invalid @endif"
                                               required autocomplete="new-password">
                                        <div class="form-text">8 caractères minimum.</div>
                                        @if ($errors->password->has('password'))
                                        <div class="invalid-feedback">{{ $errors->password->first('password') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="password_confirmation" class="form-label fw-semibold">Confirmer</label>
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                               class="form-control"
                                               required autocomplete="new-password">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fa-solid fa-key me-1"></i>Mettre à jour le mot de passe
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <footer>
        <div class="container text-center">
            <p class="mb-0 small">&copy; {{ date('Y') }} ITtshirt3. Tous droits réservés.</p>
        </div>
    </footer>


    <x-cookie-banner />
</body>
</html>
