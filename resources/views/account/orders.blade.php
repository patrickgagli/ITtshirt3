<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Mes commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0D6EFD; --surface-bg: #F8FAFC; --card-radius: 1.25rem; --font-family: 'Inter', sans-serif; }
        body { font-family: var(--font-family); background-color: var(--surface-bg); color: #1E293B; }
        .navbar { backdrop-filter: blur(10px); background: rgba(255,255,255,0.8); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 800; letter-spacing: -0.02em; color: var(--primary-color) !important; }
        .page-section { padding: 40px 0 80px; }
        .content-card { background: white; border-radius: var(--card-radius); border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; }
        .order-row { display: flex; align-items: center; gap: 1rem; padding: 1rem 1.5rem; border-bottom: 1px solid #F1F5F9; text-decoration: none; color: inherit; transition: background 0.15s; }
        .order-row:last-child { border-bottom: none; }
        .order-row:hover { background: #F8FAFC; }
        .order-ref { font-family: monospace; font-size: 0.85rem; font-weight: 700; color: #0F172A; }
        .order-date { font-size: 0.8rem; color: #94A3B8; }
        .order-total { font-size: 0.95rem; font-weight: 700; color: var(--primary-color); white-space: nowrap; }
        footer { background: #0F172A; color: #94A3B8; padding: 40px 0 20px; margin-top: 60px; }
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
                    <li class="nav-item"><a class="nav-link fw-semibold" href="{{ route('products.index') }}">Catalogue</a></li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('cart.index') }}" class="btn btn-link text-dark p-0 position-relative" title="Mon panier">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:0.6rem;">{{ $cartCount }}</span>
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="btn btn-link text-dark p-0 position-relative" title="Ma liste de souhaits">
                        <i class="fa-regular fa-heart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;" data-wishlist-count>{{ $wishlistCount }}</span>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fa-regular fa-user"></i>
                            <span class="ms-1 small fw-semibold d-none d-lg-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('account.show') }}"><i class="fa-solid fa-circle-user me-2"></i>Mon compte</a></li>
                            <li><a class="dropdown-item active" href="{{ route('account.orders.index') }}"><i class="fa-solid fa-bag-shopping me-2"></i>Mes commandes</a></li>
                            <li><a class="dropdown-item" href="{{ route('wishlist.index') }}">
                                <i class="fa-solid fa-heart me-2"></i>Ma liste de souhaits
                                @if($wishlistCount > 0)<span class="badge bg-primary rounded-pill ms-1">{{ $wishlistCount }}</span>@endif
                            </a></li>
                            @if(auth()->user()->isAdmin())
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge me-2"></i>Administration</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('auth.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Déconnexion</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="page-section container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('account.show') }}" class="text-decoration-none">Mon compte</a></li>
                <li class="breadcrumb-item active">Mes commandes</li>
            </ol>
        </nav>

        <h1 class="fw-800 mb-4" style="font-weight:800;">Mes commandes</h1>

        @if($orders->isEmpty())
        <div class="content-card p-5 text-center">
            <i class="fa-solid fa-bag-shopping fa-3x text-muted opacity-25 mb-3 d-block"></i>
            <h4 class="fw-700" style="font-weight:700;">Aucune commande</h4>
            <p class="text-muted mb-4">Vous n'avez pas encore passé de commande.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary px-4">Découvrir le catalogue</a>
        </div>
        @else
        <div class="content-card mb-4">
            {{-- Table header --}}
            <div class="d-none d-md-flex px-4 py-2" style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#94A3B8;border-bottom:1px solid #F1F5F9;">
                <span style="flex:2;">Référence</span>
                <span style="flex:2;">Date</span>
                <span style="flex:2;">Statut</span>
                <span style="flex:1;text-align:right;">Total</span>
            </div>
            @foreach($orders as $order)
            <a href="{{ route('account.orders.show', $order->uuid) }}" class="order-row">
                <div style="flex:2;">
                    <span class="order-ref">{{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}</span>
                </div>
                <div style="flex:2;">
                    <span class="order-date">{{ $order->created_at->format('d/m/Y à H\hi') }}</span>
                </div>
                <div style="flex:2;">
                    <span class="badge {{ $order->statusBadgeClass() }}" style="font-size:0.7rem;">{{ $order->statusLabel() }}</span>
                </div>
                <div style="flex:1;text-align:right;">
                    <span class="order-total">{{ $order->formattedTotal() }}</span>
                </div>
                <div class="ms-2"><i class="fa-solid fa-chevron-right text-muted" style="font-size:0.75rem;"></i></div>
            </a>
            @endforeach
        </div>

        {{ $orders->links() }}
        @endif
    </main>

    <footer>
        <div class="container text-center small opacity-50">&copy; {{ date('Y') }} ITtshirt3. Tous droits réservés.</div>
    </footer>

    <x-cookie-banner />
</body>
</html>
