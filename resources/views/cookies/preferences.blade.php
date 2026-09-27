<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Préférences de cookies</title>
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
        .section-card {
            background: white;
            border-radius: var(--card-radius);
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            padding: 1.75rem;
        }
        .cookie-category-card {
            background: white;
            border-radius: 1rem;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            padding: 1.5rem;
            transition: border-color 0.2s;
        }
        .cookie-category-card:has(.form-check-input:checked) {
            border-color: rgba(13,110,253,0.3);
        }
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
            cursor: pointer;
        }
        .form-switch .form-check-input:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
        .badge-required {
            background: rgba(13,110,253,0.1);
            color: var(--primary-color);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 2rem;
        }
        .badge-optional {
            background: rgba(100,116,139,0.1);
            color: #64748B;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 2rem;
        }
        .page-hero {
            background: linear-gradient(135deg, rgba(13,110,253,0.06) 0%, rgba(99,102,241,0.06) 100%);
            border-radius: var(--card-radius);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        .footer-dark {
            background: #1E293B;
            color: rgba(255,255,255,0.7);
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
                <li class="breadcrumb-item active" aria-current="page">Préférences de cookies</li>
            </ol>
        </nav>

        <!-- Hero -->
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div style="width:56px;height:56px;background:rgba(13,110,253,0.1);border-radius:1rem;
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-shield-halved fa-lg" style="color:var(--primary-color);" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="col">
                    <h1 class="h3 fw-800 mb-1" style="letter-spacing:-0.02em;">Vos préférences de cookies</h1>
                    <p class="text-muted mb-0">
                        Gérez les cookies utilisés par ITtshirt3. Les cookies nécessaires ne peuvent pas être désactivés
                        car ils sont indispensables au fonctionnement du site.
                    </p>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('cookie_consent_saved'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <span>Vos préférences de cookies ont été enregistrées.</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        @endif

        @if(session('cookie_consent_withdrawn'))
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <span>Votre consentement a été retiré. Seuls les cookies nécessaires resteront actifs.</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        @endif

        <div class="row g-4">

            <!-- Preferences form -->
            <div class="col-lg-8">
                <form method="POST" action="{{ route('cookies.consent.store') }}" aria-label="Formulaire de préférences de cookies">
                    @csrf

                    <div class="d-flex flex-column gap-3">

                        {{-- Necessary cookies (always on) --}}
                        <div class="cookie-category-card" role="group" aria-labelledby="label-necessary">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h2 class="h6 fw-700 mb-0" id="label-necessary">
                                            <i class="fa-solid fa-lock fa-sm me-1" aria-hidden="true"></i>
                                            Cookies nécessaires
                                        </h2>
                                        <span class="badge-required">Toujours actifs</span>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Ces cookies sont indispensables au fonctionnement du site : gestion de la session,
                                        protection CSRF, mémorisation de votre langue. Ils ne collectent aucune donnée personnelle
                                        à des fins publicitaires.
                                    </p>
                                </div>
                                <div class="form-check form-switch flex-shrink-0 mt-1" style="padding-left:0;">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="cookie-necessary"
                                           checked disabled
                                           aria-label="Cookies nécessaires – toujours actifs"
                                           aria-describedby="label-necessary">
                                </div>
                            </div>
                        </div>

                        {{-- Analytics cookies --}}
                        <div class="cookie-category-card" role="group" aria-labelledby="label-analytics">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h2 class="h6 fw-700 mb-0" id="label-analytics">
                                            <i class="fa-solid fa-chart-line fa-sm me-1" aria-hidden="true"></i>
                                            Cookies analytiques
                                        </h2>
                                        <span class="badge-optional">Optionnel</span>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Ces cookies nous permettent de comprendre comment vous naviguez sur le site
                                        (pages visitées, durée de session) afin d'améliorer nos contenus.
                                        Aucune donnée n'est partagée avec des tiers.
                                    </p>
                                </div>
                                <div class="form-check form-switch flex-shrink-0 mt-1" style="padding-left:0;">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="cookie-analytics" name="analytics" value="1"
                                           {{ $consent['analytics'] ? 'checked' : '' }}
                                           aria-labelledby="label-analytics">
                                </div>
                            </div>
                        </div>

                        {{-- Marketing cookies --}}
                        <div class="cookie-category-card" role="group" aria-labelledby="label-marketing">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h2 class="h6 fw-700 mb-0" id="label-marketing">
                                            <i class="fa-solid fa-bullhorn fa-sm me-1" aria-hidden="true"></i>
                                            Cookies marketing
                                        </h2>
                                        <span class="badge-optional">Optionnel</span>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Ces cookies sont utilisés pour vous présenter des publicités personnalisées
                                        sur d'autres sites et mesurer l'efficacité de nos campagnes.
                                    </p>
                                </div>
                                <div class="form-check form-switch flex-shrink-0 mt-1" style="padding-left:0;">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="cookie-marketing" name="marketing" value="1"
                                           {{ $consent['marketing'] ? 'checked' : '' }}
                                           aria-labelledby="label-marketing">
                                </div>
                            </div>
                        </div>

                        {{-- Preferences cookies --}}
                        <div class="cookie-category-card" role="group" aria-labelledby="label-preferences">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h2 class="h6 fw-700 mb-0" id="label-preferences">
                                            <i class="fa-solid fa-sliders fa-sm me-1" aria-hidden="true"></i>
                                            Cookies de préférences
                                        </h2>
                                        <span class="badge-optional">Optionnel</span>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Ces cookies mémorisent vos préférences d'affichage (langue, devise, région)
                                        pour personnaliser votre expérience lors de vos prochaines visites.
                                    </p>
                                </div>
                                <div class="form-check form-switch flex-shrink-0 mt-1" style="padding-left:0;">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="cookie-preferences" name="preferences" value="1"
                                           {{ $consent['preferences'] ? 'checked' : '' }}
                                           aria-labelledby="label-preferences">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Save buttons -->
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>Enregistrer mes préférences
                        </button>
                        <button type="button" id="btn-accept-all"
                                class="btn btn-outline-primary px-4"
                                onclick="document.getElementById('cookie-analytics').checked=true;
                                         document.getElementById('cookie-marketing').checked=true;
                                         document.getElementById('cookie-preferences').checked=true;
                                         this.closest('form').submit();">
                            <i class="fa-solid fa-check me-2" aria-hidden="true"></i>Tout accepter
                        </button>
                        <button type="button" id="btn-reject-all"
                                class="btn btn-outline-secondary px-4"
                                onclick="document.getElementById('cookie-analytics').checked=false;
                                         document.getElementById('cookie-marketing').checked=false;
                                         document.getElementById('cookie-preferences').checked=false;
                                         this.closest('form').submit();">
                            <i class="fa-solid fa-xmark me-2" aria-hidden="true"></i>Tout refuser
                        </button>
                    </div>
                </form>

                <!-- Withdraw consent -->
                @if(request()->cookie('cookie_consent') !== null)
                <div class="mt-4 p-3" style="background:#FFF7ED;border-radius:.75rem;border:1px solid #FED7AA;">
                    <p class="small text-muted mb-2">
                        <i class="fa-solid fa-rotate-left me-1" aria-hidden="true"></i>
                        Vous pouvez retirer l'intégralité de votre consentement à tout moment.
                        Seuls les cookies strictement nécessaires resteront actifs.
                    </p>
                    <form method="POST" action="{{ route('cookies.consent.withdraw') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fa-solid fa-trash me-1" aria-hidden="true"></i>Retirer mon consentement
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Info sidebar -->
            <div class="col-lg-4">
                <div class="section-card">
                    <h2 class="h6 fw-700 mb-3">
                        <i class="fa-solid fa-circle-info me-2 text-primary" aria-hidden="true"></i>
                        Vos droits RGPD
                    </h2>
                    <ul class="list-unstyled small text-muted d-flex flex-column gap-2 mb-0">
                        <li class="d-flex gap-2">
                            <i class="fa-solid fa-check-circle text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <span><strong>Droit d'accès</strong> — Vous pouvez demander quelles données nous détenons sur vous.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="fa-solid fa-check-circle text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <span><strong>Droit de rectification</strong> — Vous pouvez corriger des données inexactes.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="fa-solid fa-check-circle text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <span><strong>Droit à l'effacement</strong> — Vous pouvez demander la suppression de vos données.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="fa-solid fa-check-circle text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <span><strong>Droit de retrait</strong> — Vous pouvez retirer votre consentement à tout moment.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="fa-solid fa-check-circle text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <span><strong>Droit à la portabilité</strong> — Vous pouvez exporter vos données personnelles.</span>
                        </li>
                    </ul>
                </div>

                <div class="section-card mt-3">
                    <h2 class="h6 fw-700 mb-3">
                        <i class="fa-solid fa-cookie-bite me-2 text-primary" aria-hidden="true"></i>
                        À propos des cookies
                    </h2>
                    <p class="small text-muted mb-2">
                        Les cookies sont de petits fichiers stockés dans votre navigateur.
                        Ils permettent à un site de mémoriser des informations entre deux visites.
                    </p>
                    <p class="small text-muted mb-0">
                        Votre consentement est conservé pendant <strong>12 mois</strong>.
                        Vous pouvez modifier vos préférences ou retirer votre accord à tout moment depuis cette page.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-dark mt-5 py-4">
        <div class="container">
            <div class="text-center small">
                <span>&copy; {{ date('Y') }} ITtshirt3. Tous droits réservés.</span>
                <span class="mx-2 opacity-30">|</span>
                <a href="{{ route('cookies.show') }}" class="footer-link">Préférences de cookies</a>
            </div>
        </div>
    </footer>

</body>
</html>
