<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Finaliser ma commande</title>
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

        .checkout-section {
            padding: 40px 0 80px;
        }

        .checkout-card {
            background: white;
            border-radius: var(--card-radius);
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            padding: 2.5rem;
        }

        .coming-soon-badge {
            display: inline-block;
            background: rgba(13,110,253,0.1);
            color: var(--primary-color);
            padding: 0.375rem 1rem;
            border-radius: 2rem;
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .order-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #F1F5F9;
            font-size: 0.9375rem;
        }

        .order-line:last-of-type { border-bottom: none; }

        .order-total {
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--primary-color);
        }

        footer {
            background: #0F172A;
            color: #94A3B8;
            padding: 60px 0 30px;
        }

        footer h5 { color: white; font-weight: 700; margin-bottom: 1.5rem; }

        .footer-link {
            color: #94A3B8;
            text-decoration: none;
            display: block;
            margin-bottom: 0.625rem;
            font-size: 0.9rem;
            transition: color 0.2s;
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="{{ route('products.index') }}">Catalogue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="{{ route('contact.show') }}">Contact</a>
                    </li>
                </ul>
                <div class="d-flex gap-3 align-items-center">
                    @auth
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-user"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('account.show') }}"><i class="fa-solid fa-circle-user me-2"></i>Mon compte</a></li>
                            <li><a class="dropdown-item" href="{{ route('wishlist.index') }}"><i class="fa-solid fa-heart me-2"></i>Ma liste de souhaits <span class="badge bg-danger ms-1">{{ $wishlistCount }}</span></a></li>
                            @if(auth()->user()->isAdmin())
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
                    @else
                    <a href="{{ route('auth.login') }}" class="btn btn-link text-dark p-0" title="Connexion"><i class="fa-regular fa-user"></i></a>
                    @endauth
                    <a href="{{ route('wishlist.index') }}" class="btn btn-link text-dark p-0 position-relative" title="Ma liste de souhaits">
                        <i class="fa-regular fa-heart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;" data-wishlist-count>{{ $wishlistCount }}</span>
                    </a>
                    <a href="{{ route('cart.index') }}" class="btn btn-link text-dark p-0 position-relative" title="Mon panier">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:0.6rem;">{{ $cartCount }}</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="checkout-section container">

        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cart.index') }}" class="text-decoration-none">Mon panier</a></li>
                <li class="breadcrumb-item active" aria-current="page">Finaliser la commande</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="checkout-card text-center">
                    <h1 class="fw-800 mb-3" style="font-weight:800;font-size:clamp(1.75rem,4vw,2.5rem);letter-spacing:-0.02em;">
                        Récapitulatif de votre commande
                    </h1>

                    <hr class="my-4">

                    <h2 class="fw-700 text-start mb-3" style="font-weight:700;font-size:1.125rem;">Articles</h2>

                    @foreach($items as $item)
                    <div class="order-line text-start">
                        <span>{{ $item->product->name }} <span class="text-muted small">× {{ $item->quantity }}</span></span>
                        <span class="fw-600" style="font-weight:600;">
                            {{ number_format($item->product->price_cents * $item->quantity / 100, 2, ',', ' ') }} {{ $item->product->currency }}
                        </span>
                    </div>
                    @endforeach

                    <div class="order-line text-start mt-2 pt-2" style="border-top:2px solid #E2E8F0 !important;">
                        <span class="fw-700" style="font-weight:700;">Total</span>
                        <span class="order-total">{{ number_format($totalCents / 100, 2, ',', ' ') }} EUR</span>
                    </div>

                    <div class="d-grid gap-2 mt-5">
                        @guest
                        <div class="alert alert-info text-start mb-3" role="alert">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            <strong>Connectez-vous pour finaliser votre commande.</strong><br>
                            <span class="small text-muted">Votre panier est conservé. Créez un compte ou connectez-vous pour continuer.</span>
                        </div>
                        <a href="{{ route('checkout.address') }}" class="btn btn-primary py-3 rounded-3 fw-700" style="font-weight:700;">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Se connecter pour passer la commande
                        </a>
                        <a href="{{ route('auth.register') }}" class="btn btn-outline-primary py-3 rounded-3 fw-700" style="font-weight:700;">
                            <i class="fa-solid fa-user-plus me-2"></i>Créer un compte
                        </a>
                        @else
                        <a href="{{ route('checkout.address') }}" class="btn btn-primary py-3 rounded-3 fw-700" style="font-weight:700;">
                            <i class="fa-solid fa-credit-card me-2"></i>Passer la commande
                        </a>
                        @endguest
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary py-3 rounded-3 fw-700" style="font-weight:700;">
                            <i class="fa-solid fa-arrow-left me-2"></i>Retour au panier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="navbar-brand text-white fs-3 mb-3 d-block" href="#">ITtshirt3</a>
                    <p class="text-white-50 small">La destination n°1 pour les t-shirts tech et style.</p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-2">
                    <h5>Boutique</h5>
                    <a href="{{ route('products.index') }}" class="footer-link">Catalogue</a>
                </div>
                <div class="col-6 col-lg-2">
                    <h5>Support</h5>
                    <a href="{{ route('contact.show') }}" class="footer-link">Contact</a>
                </div>
            </div>
            <hr class="my-5 opacity-10">
            <div class="text-center small opacity-50">
                &copy; {{ date('Y') }} ITtshirt3. Tous droits réservés.
            </div>
        </div>
    </footer>

    <x-cookie-banner />
</body>
</html>
