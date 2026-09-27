<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Commande confirmée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root { --primary-color: #0D6EFD; --surface-bg: #F8FAFC; --card-radius: 1.25rem; --font-family: 'Inter', sans-serif; }
        body { font-family: var(--font-family); background-color: var(--surface-bg); color: #1E293B; }
        .navbar { backdrop-filter: blur(10px); background: rgba(255,255,255,0.8); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 800; letter-spacing: -0.02em; color: var(--primary-color) !important; }
        .checkout-section { padding: 40px 0 80px; }
        .step-bar { display: flex; gap: 0; margin-bottom: 2.5rem; }
        .step { flex: 1; text-align: center; padding: 0.75rem 0.5rem; font-size: 0.8125rem; font-weight: 700; border-bottom: 3px solid #E2E8F0; color: #94A3B8; }
        .step.done { border-bottom-color: #22C55E; color: #22C55E; }
        .confirm-card { background: white; border-radius: var(--card-radius); border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 2rem; }
        .success-icon { width: 72px; height: 72px; background: linear-gradient(135deg, #22C55E, #16A34A); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        .order-ref { font-family: monospace; background: #F1F5F9; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.85rem; letter-spacing: 0.05em; }
        .section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #64748B; margin-bottom: 0.75rem; }
        .address-block { background: #F8FAFC; border-radius: 0.75rem; padding: 1rem 1.25rem; border: 1px solid #E2E8F0; font-size: 0.9rem; }
        .order-line { display: flex; justify-content: space-between; padding: 0.625rem 0; border-bottom: 1px solid #F1F5F9; font-size: 0.9rem; }
        .order-line:last-of-type { border: none; }
        footer { background: #0F172A; color: #94A3B8; padding: 40px 0 20px; margin-top: 60px; }
        .footer-link { color: #94A3B8; text-decoration: none; font-size: 0.875rem; }
        .footer-link:hover { color: white; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">ITtshirt3</a>
            <div class="d-flex gap-3 ms-auto align-items-center">
                @auth
                <div class="dropdown">
                    <button class="btn btn-link text-dark p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-circle fa-lg"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('account.show') }}">Mon compte</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Déconnexion</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="checkout-section container">
        <!-- Progress bar -->
        <div class="step-bar mb-4">
            <div class="step done"><i class="fa-solid fa-check me-1"></i>Livraison</div>
            <div class="step done"><i class="fa-solid fa-check me-1"></i>Paiement</div>
            <div class="step done"><i class="fa-solid fa-check me-1"></i>Confirmation</div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="confirm-card text-center mb-4">
                    <div class="success-icon">
                        <i class="fa-solid fa-check fa-xl text-white"></i>
                    </div>
                    <h1 style="font-size:1.75rem;font-weight:800;">Merci pour votre commande&nbsp;!</h1>
                    <p class="text-muted mt-2 mb-3">Un récapitulatif a été envoyé à <strong>{{ $order->email }}</strong>.</p>
                    <p class="mb-1 text-muted small">Référence de commande</p>
                    <span class="order-ref">{{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}</span>
                </div>

                <!-- Order items -->
                <div class="confirm-card mb-4">
                    <p class="section-title">Articles commandés</p>
                    @foreach($order->items as $item)
                    <div class="order-line">
                        <span>{{ $item->product_name }} <span class="text-muted">× {{ $item->quantity }}</span></span>
                        <span>{{ number_format($item->lineTotalCents() / 100, 2, ',', ' ') }} €</span>
                    </div>
                    @endforeach
                    <div class="order-line" style="border-top: 2px solid #E2E8F0; margin-top: 0.5rem; padding-top:0.75rem;">
                        <span class="fw-700" style="font-weight:700;">Total payé</span>
                        <span style="font-size:1.1rem;font-weight:800;color:var(--primary-color);">
                            {{ number_format($order->total_cents / 100, 2, ',', ' ') }} EUR
                        </span>
                    </div>
                </div>

                <!-- Shipping address -->
                <div class="confirm-card mb-4">
                    <p class="section-title">Adresse de livraison</p>
                    <div class="address-block text-start">
                        {{ $order->shipping_name }}<br>
                        {{ $order->shipping_address_line1 }}
                        @if($order->shipping_address_line2)
                            , {{ $order->shipping_address_line2 }}
                        @endif
                        <br>
                        {{ $order->shipping_postal_code }} {{ $order->shipping_city }}, {{ $order->shipping_country }}
                    </div>
                </div>

                <!-- CTAs -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="{{ route('products.index') }}" class="btn btn-primary px-4 py-3 rounded-3 fw-700" style="font-weight:700;">
                        <i class="fa-solid fa-shirt me-2"></i>Continuer mes achats
                    </a>
                    @auth
                    <a href="{{ route('account.show') }}" class="btn btn-outline-secondary px-4 py-3 rounded-3">
                        <i class="fa-solid fa-user me-2"></i>Mon compte
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container text-center small opacity-50">
            &copy; {{ date('Y') }} ITtshirt3. Tous droits réservés.
        </div>
    </footer>

    <x-cookie-banner />
</body>
</html>
