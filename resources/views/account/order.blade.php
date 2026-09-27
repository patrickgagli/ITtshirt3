<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Commande {{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0D6EFD; --surface-bg: #F8FAFC; --card-radius: 1.25rem; --font-family: 'Inter', sans-serif; }
        body { font-family: var(--font-family); background-color: var(--surface-bg); color: #1E293B; }
        .navbar { backdrop-filter: blur(10px); background: rgba(255,255,255,0.8); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 800; letter-spacing: -0.02em; color: var(--primary-color) !important; }
        .page-section { padding: 40px 0 80px; }
        .detail-card { background: white; border-radius: var(--card-radius); border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 1.5rem; margin-bottom: 1.25rem; }
        .section-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #64748B; margin-bottom: 0.75rem; }
        .order-line { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #F1F5F9; font-size: 0.9rem; }
        .order-line:last-of-type { border: none; }
        .address-block { background: #F8FAFC; border-radius: 0.75rem; padding: 1rem 1.25rem; border: 1px solid #E2E8F0; font-size: 0.9rem; }
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
                <li class="breadcrumb-item"><a href="{{ route('account.orders.index') }}" class="text-decoration-none">Mes commandes</a></li>
                <li class="breadcrumb-item active">{{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}</li>
            </ol>
        </nav>

        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <h1 class="fw-800 mb-0" style="font-weight:800;">
                Commande <span style="font-family:monospace;">{{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}</span>
            </h1>
            <span class="badge {{ $order->statusBadgeClass() }} fs-6">{{ $order->statusLabel() }}</span>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Order items --}}
                <div class="detail-card">
                    <p class="section-label">Articles commandés</p>
                    @foreach($order->items as $item)
                    <div class="order-line">
                        <span>
                            @if($item->product)
                            <a href="{{ route('products.show', $item->product->slug) }}" class="text-decoration-none text-dark fw-600" style="font-weight:600;">{{ $item->product_name }}</a>
                            @else
                            <span class="fw-600" style="font-weight:600;">{{ $item->product_name }}</span>
                            @endif
                            <span class="text-muted ms-1">× {{ $item->quantity }}</span>
                        </span>
                        <span class="fw-600" style="font-weight:600;">
                            {{ number_format($item->lineTotalCents() / 100, 2, ',', ' ') }} €
                        </span>
                    </div>
                    @endforeach
                    <div class="order-line" style="border-top:2px solid #E2E8F0;margin-top:0.5rem;padding-top:0.75rem;">
                        <span class="fw-700" style="font-weight:700;">Total payé</span>
                        <span style="font-size:1.1rem;font-weight:800;color:var(--primary-color);">{{ $order->formattedTotal() }}</span>
                    </div>
                </div>

                {{-- Order timeline --}}
                <div class="detail-card">
                    <p class="section-label">Suivi</p>
                    @php
                        $steps = [
                            'paid'       => ['label' => 'Paiement confirmé',  'icon' => 'fa-credit-card'],
                            'processing' => ['label' => 'En préparation',     'icon' => 'fa-box-open'],
                            'shipped'    => ['label' => 'Expédiée',           'icon' => 'fa-truck'],
                            'delivered'  => ['label' => 'Livrée',             'icon' => 'fa-circle-check'],
                        ];
                        $reached = false;
                        if ($order->status === 'cancelled') { $steps = []; }
                    @endphp
                    @if($order->status === 'cancelled')
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-danger p-2"><i class="fa-solid fa-xmark fa-lg"></i></span>
                            <span class="fw-600" style="font-weight:600;">Commande annulée</span>
                        </div>
                    @else
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($steps as $key => $step)
                        @php
                            $statusOrder = array_keys($steps);
                            $currentIdx  = array_search($order->status, array_merge(['pending'], $statusOrder));
                            $stepIdx     = array_search($key, $statusOrder);
                            $done        = ($currentIdx !== false && $stepIdx <= $currentIdx - 1) || $order->status === $key || in_array($order->status, array_slice($statusOrder, $stepIdx + 1));
                        @endphp
                        <div class="d-flex align-items-center gap-2 me-3 {{ $done ? 'text-success' : 'text-muted' }}">
                            <i class="fa-solid {{ $step['icon'] }}"></i>
                            <span class="small fw-600" style="font-weight:600;">{{ $step['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Shipping address --}}
                <div class="detail-card">
                    <p class="section-label">Livraison</p>
                    <div class="address-block">
                        {{ $order->shipping_name }}<br>
                        {{ $order->shipping_address_line1 }}
                        @if($order->shipping_address_line2), {{ $order->shipping_address_line2 }}@endif<br>
                        {{ $order->shipping_postal_code }} {{ $order->shipping_city }}, {{ $order->shipping_country }}
                    </div>
                </div>

                {{-- Payment summary --}}
                <div class="detail-card">
                    <p class="section-label">Paiement</p>
                    <div class="order-line">
                        <span class="text-muted">Méthode</span>
                        <span class="fw-600" style="font-weight:600;"><i class="fa-solid fa-credit-card me-1"></i>Carte bancaire</span>
                    </div>
                    @if($order->paid_at)
                    <div class="order-line">
                        <span class="text-muted">Date</span>
                        <span class="fw-600" style="font-weight:600;">{{ $order->paid_at->format('d/m/Y à H\hi') }}</span>
                    </div>
                    @endif
                    <div class="order-line">
                        <span class="text-muted">E-mail</span>
                        <span class="fw-600 small" style="font-weight:600;">{{ $order->email }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-2">
            <a href="{{ route('account.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Retour à mes commandes
            </a>
        </div>
    </main>

    <footer>
        <div class="container text-center small opacity-50">&copy; {{ date('Y') }} ITtshirt3. Tous droits réservés.</div>
    </footer>

    <x-cookie-banner />
</body>
</html>
