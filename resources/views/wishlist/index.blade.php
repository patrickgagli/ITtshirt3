<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Ma liste de souhaits</title>
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

        .wishlist-section {
            padding: 40px 0 80px;
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .wishlist-card {
            background: white;
            border-radius: var(--card-radius);
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .wishlist-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        }

        .card-thumb {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-thumb-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.85);
        }

        .card-thumb-placeholder[data-color="0"] { background: linear-gradient(135deg, #667eea, #764ba2); }
        .card-thumb-placeholder[data-color="1"] { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .card-thumb-placeholder[data-color="2"] { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .card-thumb-placeholder[data-color="3"] { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        .card-thumb-placeholder[data-color="4"] { background: linear-gradient(135deg, #fa709a, #fee140); }
        .card-thumb-placeholder[data-color="5"] { background: linear-gradient(135deg, #a18cd1, #fbc2eb); }

        .card-thumb-placeholder .thumb-icon {
            font-size: 3rem;
            color: rgba(255,255,255,0.3);
        }

        .card-thumb-placeholder .thumb-initials {
            font-size: 1.125rem;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        .card-body-custom {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-category {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.375rem;
        }

        .card-product-name {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.375rem;
            color: #1E293B;
            text-decoration: none;
        }

        .card-product-name:hover { color: var(--primary-color); }

        .card-price {
            font-weight: 800;
            font-size: 1.125rem;
            color: var(--primary-color);
            margin-bottom: auto;
        }

        .card-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 1px solid #F1F5F9;
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
                            <li><a class="dropdown-item active" href="{{ route('wishlist.index') }}"><i class="fa-solid fa-heart me-2"></i>Ma liste de souhaits <span class="badge bg-danger ms-1">{{ $wishlistCount }}</span></a></li>
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
                        <i class="fa-solid fa-heart"></i>
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

    <main class="wishlist-section container">

        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ma liste de souhaits</li>
            </ol>
        </nav>

        <div class="d-flex align-items-center gap-3 mb-4">
            <h1 class="mb-0" style="font-weight:800;letter-spacing:-0.02em;">
                Ma liste de souhaits
            </h1>
            @if($items->isNotEmpty())
            <span class="badge bg-primary rounded-pill fs-6">{{ $items->count() }}</span>
            @endif
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        @endif

        @if($items->isEmpty())
        <div class="empty-state">
            <i class="fa-regular fa-heart fa-4x text-muted mb-4 d-block"></i>
            <h2 class="mb-2" style="font-weight:700;">Votre liste de souhaits est vide</h2>
            <p class="text-muted mb-4">Explorez notre catalogue et sauvegardez vos articles préférés.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary px-5 py-3 rounded-3">
                <i class="fa-solid fa-shirt me-2"></i>Découvrir le catalogue
            </a>
        </div>
        @else
        <div class="wishlist-grid">
            @foreach($items as $item)
            <div class="wishlist-card">
                <!-- Thumbnail -->
                <a href="{{ route('products.show', $item->product->slug) }}" class="card-thumb text-decoration-none">
                    @if($item->product->image_path)
                        <img src="{{ asset('storage/' . $item->product->image_path) }}" alt="{{ $item->product->name }}">
                    @else
                        <div class="card-thumb-placeholder" data-color="{{ $item->product->id % 6 }}" aria-label="{{ $item->product->name }}">
                            <i class="fa-solid fa-shirt thumb-icon" aria-hidden="true"></i>
                            <span class="thumb-initials">{{ mb_strtoupper(mb_substr($item->product->name, 0, 2)) }}</span>
                        </div>
                    @endif
                </a>

                <!-- Info -->
                <div class="card-body-custom">
                    @if($item->product->category)
                    <div class="card-category">{{ $item->product->category->name }}</div>
                    @endif

                    <a href="{{ route('products.show', $item->product->slug) }}" class="card-product-name">
                        {{ $item->product->name }}
                    </a>

                    <div class="card-price mb-3">{{ $item->product->formattedPrice() }}</div>

                    <div class="card-actions">
                        <!-- Move to cart -->
                        <form method="POST" action="{{ route('wishlist.items.move-to-cart', $item) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa-solid fa-cart-plus me-2"></i>Ajouter au panier
                            </button>
                        </form>

                        <!-- Remove from wishlist -->
                        <form method="POST" action="{{ route('wishlist.items.destroy', $item) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="fa-regular fa-trash-can me-2"></i>Retirer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
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

    <x-cookie-banner />
</body>
</html>
