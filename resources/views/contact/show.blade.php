<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Contact</title>
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
        .page-hero {
            background: linear-gradient(135deg, rgba(13,110,253,0.06) 0%, rgba(99,102,241,0.06) 100%);
            border-radius: var(--card-radius);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        .section-card {
            background: white;
            border-radius: var(--card-radius);
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            padding: 2rem;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 0.375rem;
        }
        .form-control, .form-select {
            border-radius: 0.625rem;
            border: 1.5px solid #E2E8F0;
            padding: 0.625rem 0.875rem;
            font-size: 0.9375rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
        }
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #EF4444;
        }
        .btn-submit {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 0.75rem;
            font-size: 1rem;
            box-shadow: 0 8px 20px rgba(13,110,253,0.2);
            transition: transform 0.15s, box-shadow 0.15s;
            cursor: pointer;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(13,110,253,0.28);
            color: white;
        }
        .btn-submit:active { transform: none; }
        .contact-info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .contact-info-item:last-child { border-bottom: none; }
        .contact-icon {
            width: 44px;
            height: 44px;
            background: rgba(13,110,253,0.08);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            flex-shrink: 0;
        }
        .char-count {
            font-size: 0.75rem;
            color: #94A3B8;
            text-align: right;
        }
        footer {
            background: #0F172A;
            color: #94A3B8;
            padding: 40px 0;
            margin-top: 80px;
        }
        .footer-link {
            color: rgba(255,255,255,0.5);
            text-decoration: none;
        }
        .footer-link:hover { color: white; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">ITtshirt3</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Basculer la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="{{ route('products.index') }}">Catalogue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold active" aria-current="page" href="{{ route('contact.show') }}">Contact</a>
                    </li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    @auth
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                            <span class="ms-1 small fw-semibold d-none d-lg-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('account.show') }}">
                                <i class="fa-solid fa-circle-user me-2" aria-hidden="true"></i>Mon compte</a></li>
                            @if(auth()->user()->isAdmin())
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                <i class="fa-solid fa-gauge me-2" aria-hidden="true"></i>Administration</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('auth.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fa-solid fa-right-from-bracket me-2" aria-hidden="true"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @else
                    <a href="{{ route('auth.login') }}" class="btn btn-outline-primary btn-sm rounded-3">Connexion</a>
                    <a href="{{ route('auth.register') }}" class="btn btn-primary btn-sm rounded-3">Créer un compte</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-5" id="main-content">

        <!-- Breadcrumb -->
        <nav aria-label="Fil d'Ariane" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact</li>
            </ol>
        </nav>

        <!-- Hero -->
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div style="width:56px;height:56px;background:rgba(13,110,253,0.1);border-radius:1rem;
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-envelope fa-lg" style="color:var(--primary-color);" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="col">
                    <h1 class="h3 fw-bold mb-1" style="letter-spacing:-0.02em;">Contactez-nous</h1>
                    <p class="text-muted mb-0">
                        Une question sur une commande, nos produits ou un partenariat ?
                        Remplissez le formulaire ci-dessous et nous vous répondrons rapidement.
                    </p>
                </div>
            </div>
        </div>

        {{-- Success flash --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-circle-check fa-lg" aria-hidden="true"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        @endif

        <div class="row g-4">

            <!-- Form -->
            <div class="col-lg-7">
                <div class="section-card">
                    <h2 class="h5 fw-bold mb-4">Envoyer un message</h2>

                    <form method="POST" action="{{ route('contact.store') }}" novalidate
                          aria-label="Formulaire de contact">
                        @csrf

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="contact-name" class="form-label">
                                Nom complet <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="contact-name"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $prefill['name']) }}"
                                   maxlength="100"
                                   required
                                   autocomplete="name"
                                   aria-required="true"
                                   aria-describedby="name-error">
                            @error('name')
                            <div id="name-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="contact-email" class="form-label">
                                Adresse e-mail <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input type="email"
                                   id="contact-email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $prefill['email']) }}"
                                   maxlength="255"
                                   required
                                   autocomplete="email"
                                   aria-required="true"
                                   aria-describedby="email-error">
                            @error('email')
                            <div id="email-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Subject --}}
                        <div class="mb-3">
                            <label for="contact-subject" class="form-label">
                                Sujet <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <select id="contact-subject"
                                    name="subject"
                                    class="form-select @error('subject') is-invalid @enderror"
                                    required
                                    aria-required="true"
                                    aria-describedby="subject-error">
                                <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Sélectionnez un sujet…</option>
                                @foreach([
                                    'Question sur un produit'  => 'Question sur un produit',
                                    'Suivi de commande'        => 'Suivi de commande',
                                    'Retour / remboursement'   => 'Retour / remboursement',
                                    'Partenariat'              => 'Partenariat',
                                    'Problème technique'       => 'Problème technique',
                                    'Autre'                    => 'Autre',
                                ] as $value => $label)
                                <option value="{{ $value }}" {{ old('subject') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('subject')
                            <div id="subject-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Message --}}
                        <div class="mb-4">
                            <label for="contact-message" class="form-label">
                                Message <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <textarea id="contact-message"
                                      name="message"
                                      rows="6"
                                      class="form-control @error('message') is-invalid @enderror"
                                      maxlength="2000"
                                      required
                                      aria-required="true"
                                      aria-describedby="message-error message-count"
                                      oninput="document.getElementById('message-count').textContent=this.value.length+'/2000'"
                            >{{ old('message') }}</textarea>
                            <div class="d-flex justify-content-between align-items-start mt-1">
                                @error('message')
                                <div id="message-error" class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                                @else
                                <div></div>
                                @enderror
                                <span id="message-count" class="char-count" aria-live="polite">
                                    {{ strlen(old('message', '')) }}/2000
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <button type="submit" class="btn-submit">
                                <i class="fa-solid fa-paper-plane me-2" aria-hidden="true"></i>Envoyer le message
                            </button>
                            <p class="small text-muted mb-0">
                                <span class="text-danger" aria-hidden="true">*</span> Champs obligatoires
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact info sidebar -->
            <div class="col-lg-5">
                <div class="section-card">
                    <h2 class="h5 fw-bold mb-4">Informations de contact</h2>

                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <i class="fa-solid fa-envelope fa-sm" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="fw-semibold mb-1" style="font-size:.875rem;">E-mail</div>
                            <p class="text-muted small mb-0">
                                <a href="mailto:contact@ittshirt3.com" style="color:var(--primary-color);">
                                    contact@ittshirt3.com
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <i class="fa-solid fa-clock fa-sm" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="fw-semibold mb-1" style="font-size:.875rem;">Délai de réponse</div>
                            <p class="text-muted small mb-0">
                                Nous répondons généralement dans les <strong>24&nbsp;à&nbsp;48&nbsp;heures</strong>
                                (jours ouvrés).
                            </p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <i class="fa-solid fa-location-dot fa-sm" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="fw-semibold mb-1" style="font-size:.875rem;">Adresse</div>
                            <p class="text-muted small mb-0">
                                ITtshirt3<br>
                                Pace du Textile<br>
                                1000 Bruxelles, Belgique
                            </p>
                        </div>
                    </div>
                </div>

                <div class="section-card mt-3" style="background:rgba(13,110,253,0.03);">
                    <h2 class="h6 fw-bold mb-3">
                        <i class="fa-solid fa-circle-question text-primary me-2" aria-hidden="true"></i>
                        Consulter la FAQ d'abord ?
                    </h2>
                    <p class="small text-muted mb-3">
                        Vous trouverez peut-être une réponse rapide à votre question dans notre centre d'aide.
                    </p>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-primary rounded-3">
                        <i class="fa-solid fa-house me-1" aria-hidden="true"></i>Retour à l'accueil
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="text-center small">
                <span>&copy; {{ date('Y') }} ITtshirt3. Tous droits réservés.</span>
                <span class="mx-2 opacity-30">|</span>
                <a href="{{ route('cookies.show') }}" class="footer-link">Cookies</a>
                <span class="mx-2 opacity-30">|</span>
                <a href="{{ route('contact.show') }}" class="footer-link">Contact</a>
            </div>
        </div>
    </footer>


    <x-cookie-banner />
</body>
</html>
