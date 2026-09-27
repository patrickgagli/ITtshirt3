<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Connexion</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        .auth-card {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.05);
            padding: 2.5rem;
        }
        .auth-title {
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .btn-primary {
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border-color: rgba(0,0,0,0.12);
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
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
                    <a href="{{ route('auth.register') }}" class="btn btn-outline-secondary btn-sm rounded-3">Créer un compte</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-5">
                    <div class="auth-card">
                        <h1 class="auth-title fs-3 mb-1">Connexion</h1>
                        <p class="text-muted mb-4">Bon retour parmi nous.</p>

                        @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('auth.login.store') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Adresse e-mail</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                >
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Mot de passe</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    required
                                    autocomplete="current-password"
                                >
                            </div>

                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label text-muted" for="remember">Se souvenir de moi</label>
                                </div>
                                <a href="{{ route('password.request') }}" class="text-muted small text-decoration-none">Mot de passe oublié ?</a>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                        </form>

                        <hr class="my-4">
                        <p class="text-center text-muted mb-0">
                            Pas encore de compte ?
                            <a href="{{ route('auth.register') }}" class="text-primary fw-semibold">Créer un compte</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
