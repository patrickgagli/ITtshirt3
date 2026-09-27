<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Mon panier</title>
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

        .cart-section {
            padding: 40px 0 80px;
        }

        .cart-table-card {
            background: white;
            border-radius: var(--card-radius);
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .cart-table thead th {
            background: #F8FAFC;
            border-bottom: 2px solid #E2E8F0;
            font-weight: 700;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748B;
            padding: 1rem 1.25rem;
        }

        .cart-table td {
            vertical-align: middle;
            padding: 1.25rem;
            border-color: #F1F5F9;
        }

        .product-thumb {
            width: 72px;
            height: 72px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-thumb-placeholder {
            width: 72px;
            height: 72px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.85);
            font-weight: 800;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .product-thumb-placeholder[data-color="0"] { background: linear-gradient(135deg, #667eea, #764ba2); }
        .product-thumb-placeholder[data-color="1"] { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .product-thumb-placeholder[data-color="2"] { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .product-thumb-placeholder[data-color="3"] { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        .product-thumb-placeholder[data-color="4"] { background: linear-gradient(135deg, #fa709a, #fee140); }
        .product-thumb-placeholder[data-color="5"] { background: linear-gradient(135deg, #a18cd1, #fbc2eb); }

        .qty-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .qty-input {
            width: 70px;
            text-align: center;
            border-radius: 0.5rem;
            border: 1.5px solid #E2E8F0;
            padding: 0.375rem 0.5rem;
            font-weight: 600;
        }

        .qty-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
        }

        .summary-card {
            background: white;
            border-radius: var(--card-radius);
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            padding: 2rem;
            position: sticky;
            top: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.625rem 0;
            border-bottom: 1px solid #F1F5F9;
            font-size: 0.9375rem;
        }

        .summary-row:last-of-type {
            border-bottom: none;
        }

        .summary-total {
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--primary-color);
        }

        .empty-state {
            text-align: center;
            padding: 80px 0;
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
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:0.6rem;" data-cart-count>{{ $cartCount }}</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="cart-section container">

        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Mon panier</li>
            </ol>
        </nav>

        <h1 class="fw-800 mb-4" style="font-weight:800;letter-spacing:-0.02em;">Mon panier</h1>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        @endif

        @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        @endif

        @if($items->isEmpty())
        <div class="empty-state">
            <i class="fa-solid fa-cart-shopping fa-4x text-muted mb-4 d-block"></i>
            <h2 class="fw-700 mb-2" style="font-weight:700;">Votre panier est vide</h2>
            <p class="text-muted mb-4">Ajoutez des produits depuis notre catalogue pour commencer.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary px-5 py-3 rounded-3">
                <i class="fa-solid fa-shirt me-2"></i>Découvrir le catalogue
            </a>
        </div>
        @else
        <div class="row g-4">
            <!-- Items -->
            <div class="col-lg-8">
                <div class="cart-table-card">
                    <table class="table cart-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Produit</th>
                                <th scope="col" class="text-center">Quantité</th>
                                <th scope="col" class="text-end">Prix unitaire</th>
                                <th scope="col" class="text-end">Sous-total</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr
                                data-cart-row="{{ $item->id }}"
                                data-subtotal-cents="{{ $item->product->price_cents * $item->quantity }}"
                                data-item-qty="{{ $item->quantity }}"
                            >
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($item->product->image_path)
                                        <div class="product-thumb">
                                            <img src="{{ asset('storage/' . $item->product->image_path) }}" alt="{{ $item->product->name }}">
                                        </div>
                                        @else
                                        <div class="product-thumb-placeholder" data-color="{{ $item->product->id % 6 }}" aria-label="{{ $item->product->name }}">
                                            {{ mb_strtoupper(mb_substr($item->product->name, 0, 2)) }}
                                        </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('products.show', $item->product->slug) }}" class="fw-700 text-dark text-decoration-none" style="font-weight:700;">
                                                {{ $item->product->name }}
                                            </a>
                                            @if($item->product->category)
                                            <div class="text-muted small mt-1">{{ $item->product->category->name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Quantity + remove (Vue island) -->
                                <td class="text-center">
                                    {{-- Vue takes over when JS loads; fallback form stays for non-JS --}}
                                    <div
                                        data-vue="cart-item-control"
                                        data-props="{{ json_encode([
                                            'itemId'         => $item->id,
                                            'quantity'       => $item->quantity,
                                            'updateUrl'      => route('cart.items.update', $item),
                                            'removeUrl'      => route('cart.items.destroy', $item),
                                            'unitPriceCents' => $item->product->price_cents,
                                            'currency'       => $item->product->currency,
                                            'productName'    => $item->product->name,
                                        ]) }}"
                                    >
                                        {{-- Non-JS fallback --}}
                                        <form method="POST" action="{{ route('cart.items.update', $item) }}" class="qty-form justify-content-center">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number"
                                                   name="quantity"
                                                   value="{{ $item->quantity }}"
                                                   min="1"
                                                   max="99"
                                                   class="qty-input form-control"
                                                   aria-label="Quantité pour {{ $item->product->name }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Mettre à jour">
                                                <i class="fa-solid fa-rotate"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                                <td class="text-end fw-600" style="font-weight:600;">
                                    {{ $item->product->formattedPrice() }}
                                </td>

                                <td class="text-end fw-700" style="font-weight:700;color:var(--primary-color);" data-subtotal>
                                    {{ number_format($item->product->price_cents * $item->quantity / 100, 2, ',', ' ') }} {{ $item->product->currency }}
                                </td>

                                <!-- Remove (fallback; Vue hides this once cart-item-control mounts) -->
                                <td class="text-center" data-delete-fallback>
                                    <form method="POST" action="{{ route('cart.items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-link text-danger p-0"
                                                title="Retirer du panier"
                                                onclick="return confirm('Retirer ce produit du panier ?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-2"></i>Continuer mes achats
                    </a>
                </div>
            </div>

            <!-- Summary -->
            <div class="col-lg-4">
                <div class="summary-card">
                    <h2 class="fw-700 mb-4" style="font-weight:700;font-size:1.25rem;">Récapitulatif</h2>

                    <div class="summary-row">
                        <span class="text-muted" id="cart-items-count">Articles ({{ $items->sum('quantity') }})</span>
                        <span>{{ number_format($totalCents / 100, 2, ',', ' ') }} EUR</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Livraison</span>
                        <span class="text-success fw-600" style="font-weight:600;">Offerte</span>
                    </div>
                    <div class="summary-row mt-2 pt-2" style="border-top:2px solid #E2E8F0 !important;">
                        <span class="fw-700" style="font-weight:700;">Total</span>
                        <span class="summary-total" id="cart-total-amount">{{ number_format($totalCents / 100, 2, ',', ' ') }} EUR</span>
                    </div>

                    <a href="{{ route('cart.checkout') }}" class="btn btn-primary w-100 py-3 rounded-3 mt-4 fw-700" style="font-weight:700;">
                        <i class="fa-solid fa-lock me-2"></i>Passer la commande
                    </a>

                    <p class="text-center text-muted small mt-3 mb-0">
                        <i class="fa-solid fa-shield-halved me-1"></i>Paiement 100 % sécurisé
                    </p>
                </div>
            </div>
        </div>
        @endif
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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-cookie-banner />
</body>
</html>
