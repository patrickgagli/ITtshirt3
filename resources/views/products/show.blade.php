<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteSetting->site_name }} | {{ $product->name }}</title>
    <meta name="description" content="{{ $product->description ? Str::limit($product->description, 160) : ($siteSetting->meta_description ?? $siteSetting->site_name) }}">
    @if($siteSetting->meta_keywords)
    <meta name="keywords" content="{{ $siteSetting->meta_keywords }}">
    @endif
    {{-- Open Graph --}}
    <meta property="og:type"        content="product">
    <meta property="og:site_name"   content="{{ $siteSetting->site_name }}">
    <meta property="og:title"       content="{{ $product->name }}">
    <meta property="og:description" content="{{ $product->description ? Str::limit($product->description, 200) : ($siteSetting->meta_description ?? $siteSetting->site_name) }}">
    <meta property="og:image"       content="{{ $product->image_path ? asset('storage/' . $product->image_path) : ($siteSetting->og_image_url ?? '') }}">
    <meta property="og:url"         content="{{ route('products.show', $product->slug) }}">
    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    @if($siteSetting->twitter_handle)
    <meta name="twitter:site"        content="@{{ $siteSetting->twitter_handle }}">
    @endif
    <meta name="twitter:title"       content="{{ $product->name }}">
    <meta name="twitter:description" content="{{ $product->description ? Str::limit($product->description, 200) : ($siteSetting->meta_description ?? $siteSetting->site_name) }}">
    <meta name="twitter:image"       content="{{ $product->image_path ? asset('storage/' . $product->image_path) : ($siteSetting->og_image_url ?? '') }}">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ── Design tokens ──────────────────────────────────── */
        :root {
            --page-bg:       #050A14;
            --surface-1:     rgba(15,23,42,0.70);
            --surface-2:     rgba(15,23,42,0.90);
            --border-dim:    rgba(255,255,255,0.07);
            --border-glow:   rgba(59,130,246,0.28);
            --text-1:        #E2E8F0;
            --text-2:        #94A3B8;
            --text-3:        #475569;
            --accent-blue:   #3B82F6;
            --accent-purple: #8B5CF6;
            --accent-green:  #10B981;
            --card-radius:   1.15rem;
            --font-family:   'Inter', system-ui, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: var(--font-family);
            background-color: var(--page-bg);
            color: var(--text-1);
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none; z-index: 0;
        }

        /* ── Navbar ─────────────────────────────────────────── */
        .navbar {
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            background: rgba(5,10,20,0.85) !important;
            border-bottom: 1px solid var(--border-dim);
        }
        .navbar-brand {
            font-weight: 800; letter-spacing: -0.03em;
            background: linear-gradient(135deg, #60A5FA, #A78BFA);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent; color: transparent !important;
        }
        .nav-link { color: var(--text-2) !important; font-weight: 600; transition: color 0.2s; }
        .nav-link:hover { color: var(--text-1) !important; }
        .navbar-toggler { border-color: var(--border-dim); }
        .navbar-toggler-icon { filter: invert(0.7); }

        .nav-auth-btn-outline {
            padding: 6px 16px; border-radius: 99px;
            border: 1px solid var(--border-glow); color: #93C5FD !important;
            font-size: 0.85rem; font-weight: 600; text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }
        .nav-auth-btn-outline:hover { background: rgba(59,130,246,0.12); color: #fff !important; }
        .nav-auth-btn-solid {
            padding: 6px 16px; border-radius: 99px;
            background: linear-gradient(135deg, #2563EB, #7C3AED);
            color: #fff !important; font-size: 0.85rem; font-weight: 700;
            text-decoration: none; box-shadow: 0 0 18px rgba(59,130,246,0.35);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .nav-auth-btn-solid:hover { box-shadow: 0 0 30px rgba(59,130,246,0.5); transform: translateY(-1px); color: #fff !important; }

        .btn-user-toggle {
            background: none; border: none; color: var(--text-2);
            font-weight: 600; font-size: 0.88rem;
            display: inline-flex; align-items: center; gap: 6px;
            cursor: pointer; transition: color 0.2s; padding: 0;
            text-decoration: none;
        }
        .btn-user-toggle:hover { color: var(--text-1); }

        .dropdown-menu {
            background: var(--surface-2) !important;
            border: 1px solid var(--border-dim) !important;
            backdrop-filter: blur(18px);
        }
        .dropdown-item { color: var(--text-2) !important; font-size: 0.88rem; }
        .dropdown-item:hover, .dropdown-item:focus { background: rgba(255,255,255,0.06) !important; color: var(--text-1) !important; }
        .dropdown-divider { border-color: var(--border-dim) !important; }

        /* ── Breadcrumb ──────────────────────────────────────── */
        .breadcrumb { --bs-breadcrumb-divider-color: var(--text-3); }
        .breadcrumb-item a { color: var(--text-3); text-decoration: none; transition: color 0.2s; }
        .breadcrumb-item a:hover { color: var(--text-1); }
        .breadcrumb-item.active { color: var(--text-2); }
        .breadcrumb-item + .breadcrumb-item::before { color: var(--text-3); }

        /* ── Product detail layout ───────────────────────────── */
        .product-detail-container { padding: 40px 0 80px; position: relative; z-index: 1; }

        /* ── FRAMEGEN product image box ──────────────────────── */
        .product-image-box {
            background: var(--surface-1);
            border-radius: var(--card-radius);
            border: 1px solid var(--border-dim);
            display: flex; align-items: center; justify-content: center;
            min-height: 420px;
            position: relative; overflow: hidden;
            backdrop-filter: blur(8px);
        }

        /* FRAMEGEN corner reticles */
        .product-image-box .fg-tic {
            position: absolute; width: 20px; height: 20px; z-index: 4;
        }
        .product-image-box .fg-tic.tl { top: 14px; left: 14px; border-top: 2px solid var(--accent-blue); border-left: 2px solid var(--accent-blue); }
        .product-image-box .fg-tic.tr { top: 14px; right: 14px; border-top: 2px solid var(--accent-blue); border-right: 2px solid var(--accent-blue); }
        .product-image-box .fg-tic.bl { bottom: 14px; left: 14px; border-bottom: 2px solid var(--accent-blue); border-left: 2px solid var(--accent-blue); }
        .product-image-box .fg-tic.br { bottom: 14px; right: 14px; border-bottom: 2px solid var(--accent-blue); border-right: 2px solid var(--accent-blue); }

        /* FRAMEGEN scanlines */
        .product-image-box::before {
            content: '';
            position: absolute; inset: 0; z-index: 2; pointer-events: none;
            background: repeating-linear-gradient(
                0deg, transparent, transparent 3px,
                rgba(0,0,0,0.06) 3px, rgba(0,0,0,0.06) 4px
            );
        }

        /* FRAMEGEN sweep animation */
        @keyframes imgSweep {
            from { transform: translateY(-4px); }
            to   { transform: translateY(calc(100% + 4px)); }
        }
        .product-image-box .fg-sweep {
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, transparent, rgba(59,130,246,0.5), transparent);
            z-index: 3;
            animation: imgSweep 4s ease-in-out infinite;
        }

        /* LUMNI halo — purple/blue glow behind image */
        .lumni-halo {
            position: absolute; inset: -20%; z-index: 0; pointer-events: none;
            background: radial-gradient(ellipse at 50% 50%,
                rgba(139,92,246,0.18) 0%,
                rgba(59,130,246,0.12) 35%,
                transparent 70%
            );
            filter: blur(36px);
            animation: lumniPulse 5s ease-in-out infinite;
        }
        @keyframes lumniPulse {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50%       { opacity: 1;   transform: scale(1.08); }
        }

        /* Out-of-stock overlay */
        .product-image-box.stock-oos::after {
            content: ''; position: absolute; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 5; pointer-events: none;
        }
        .product-image-box.stock-oos .product-img,
        .product-image-box.stock-oos .product-placeholder-art { filter: grayscale(55%); opacity: 0.7; }

        .product-stock-badge {
            position: absolute; bottom: 1.25rem; left: 1.25rem; z-index: 6;
            font-size: 0.875rem; font-weight: 700; padding: 0.35rem 0.85rem;
            border-radius: 2rem; letter-spacing: 0.02em; pointer-events: none;
        }
        .product-stock-badge.stock-oos { background: rgba(220,53,69,0.88); color: #fff; }
        .product-stock-badge.stock-low { background: rgba(255,193,7,0.88); color: #000; }

        .product-img {
            max-width: 100%; height: auto;
            border-radius: 0.75rem;
            padding: 1.5rem;
            position: relative; z-index: 1;
        }

        /* Placeholder art — dark glassmorphism */
        .product-placeholder-art {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 1rem;
            z-index: 1;
        }
        .product-placeholder-art[data-color="0"] { background: linear-gradient(135deg, rgba(102,126,234,0.25), rgba(118,75,162,0.25)); }
        .product-placeholder-art[data-color="1"] { background: linear-gradient(135deg, rgba(240,147,251,0.25), rgba(245,87,108,0.25)); }
        .product-placeholder-art[data-color="2"] { background: linear-gradient(135deg, rgba(79,172,254,0.25), rgba(0,242,254,0.25)); }
        .product-placeholder-art[data-color="3"] { background: linear-gradient(135deg, rgba(67,233,123,0.25), rgba(56,249,215,0.25)); }
        .product-placeholder-art[data-color="4"] { background: linear-gradient(135deg, rgba(250,112,154,0.25), rgba(254,225,64,0.25)); }
        .product-placeholder-art[data-color="5"] { background: linear-gradient(135deg, rgba(161,140,209,0.25), rgba(251,194,235,0.25)); }
        .product-placeholder-art .placeholder-icon { font-size: 8rem; color: rgba(255,255,255,0.2); }
        .product-placeholder-art .placeholder-initials { font-size: 2.5rem; font-weight: 800; color: rgba(255,255,255,0.7); letter-spacing: 0.08em; }

        /* ── Detail panel ────────────────────────────────────── */
        .detail-badge {
            background: rgba(59,130,246,0.1);
            color: var(--accent-blue);
            border: 1px solid rgba(59,130,246,0.22);
            padding: 0.35rem 0.85rem;
            border-radius: 2rem; font-weight: 600; font-size: 0.8rem;
            display: inline-block; margin-bottom: 1rem; margin-right: 0.5rem;
            letter-spacing: 0.05em;
        }
        .detail-badge--featured {
            background: rgba(139,92,246,0.1);
            color: var(--accent-purple);
            border-color: rgba(139,92,246,0.22);
        }

        .product-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800; letter-spacing: -0.02em;
            margin-bottom: 1rem; line-height: 1.1;
            color: var(--text-1);
        }

        .price-tag {
            font-size: 2rem; font-weight: 800;
            color: var(--accent-blue);
            margin-bottom: 2rem; display: block;
        }

        .product-description-text {
            color: var(--text-2); font-size: 1rem; line-height: 1.65; margin-bottom: 1.75rem;
        }

        .product-meta-card {
            background: var(--surface-1);
            border-radius: 0.85rem;
            padding: 1.2rem 1.4rem;
            border: 1px solid var(--border-dim);
            margin-bottom: 1rem;
            backdrop-filter: blur(6px);
        }
        .meta-label {
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.12em;
            color: var(--text-3); font-weight: 700; display: block; margin-bottom: 0.2rem;
        }
        .meta-value { font-weight: 600; color: var(--text-1); }

        /* Stock text colours */
        .text-success { color: #34D399 !important; }
        .text-warning { color: #FBBF24 !important; }
        .text-danger  { color: #F87171 !important; }

        /* Low-stock urgency banner */
        .alert-warning {
            background: rgba(251,191,36,0.1) !important;
            border: 1px solid rgba(251,191,36,0.25) !important;
            color: #FBBF24 !important;
            border-radius: 0.75rem;
        }

        /* ── Footer ──────────────────────────────────────────── */
        footer {
            background: #030711;
            color: var(--text-2);
            padding: 60px 0 30px;
            margin-top: 0;
            border-top: 1px solid var(--border-dim);
        }
        footer h5 { color: var(--text-1); font-weight: 700; margin-bottom: 1.5rem; }
        .footer-link {
            color: var(--text-3); text-decoration: none;
            transition: color 0.3s; display: block; margin-bottom: 0.75rem;
        }
        .footer-link:hover { color: var(--text-1); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">ITtshirt3</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.index') }}">Catalogue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact.show') }}">Contact</a>
                    </li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    @auth
                    <div class="dropdown">
                        <button class="btn-user-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-user"></i>
                            <span class="d-none d-lg-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('account.show') }}"><i class="fa-solid fa-circle-user me-2"></i>Mon compte</a></li>
                            <li><a class="dropdown-item" href="{{ route('wishlist.index') }}"><i class="fa-solid fa-heart me-2"></i>Ma liste de souhaits</a></li>
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
                    <a href="{{ route('auth.login') }}" class="nav-auth-btn-outline">Connexion</a>
                    <a href="{{ route('auth.register') }}" class="nav-auth-btn-solid">Créer un compte</a>
                    @endauth
                    <a href="{{ route('wishlist.index') }}" class="btn-user-toggle position-relative ms-2" title="Ma liste de souhaits">
                        <i class="fa-regular fa-heart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;" data-wishlist-count>{{ $wishlistCount }}</span>
                    </a>
                    <a href="{{ route('cart.index') }}" class="btn-user-toggle position-relative ms-1" title="Mon panier">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:0.6rem;" data-cart-count>{{ $cartCount }}</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="product-detail-container container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Catalogue</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-lg-6">
            {{-- FRAMEGEN image box with LUMNI halo --}}
            <div class="product-image-box{{ $product->isOutOfStock() ? ' is-out-of-stock stock-oos' : '' }}">
                {{-- LUMNI halo --}}
                <div class="lumni-halo" aria-hidden="true"></div>
                {{-- FRAMEGEN corner reticles --}}
                <span class="fg-tic tl" aria-hidden="true"></span>
                <span class="fg-tic tr" aria-hidden="true"></span>
                <span class="fg-tic bl" aria-hidden="true"></span>
                <span class="fg-tic br" aria-hidden="true"></span>
                {{-- FRAMEGEN sweep --}}
                <div class="fg-sweep" aria-hidden="true"></div>
                @if ($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-img">
                @else
                    <div class="product-placeholder-art" data-color="{{ $product->id % 6 }}" aria-label="{{ $product->name }}">
                        <i class="fa-solid fa-shirt placeholder-icon" aria-hidden="true"></i>
                        <span class="placeholder-initials">{{ mb_strtoupper(mb_substr($product->name, 0, 2)) }}</span>
                    </div>
                @endif
                @if($product->isOutOfStock())
                    <span class="product-stock-badge is-out-of-stock stock-oos"><i class="fa-solid fa-ban me-1"></i>Rupture</span>
                @elseif($product->isLowStock())
                    <span class="product-stock-badge is-low-stock stock-low"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $product->stock_quantity }} restant(s)</span>
                @endif
            </div>
            </div>
            <div class="col-lg-6">
                @if($product->is_featured)
                    <span class="detail-badge detail-badge--featured">Mis en avant</span>
                @endif
                @if($product->category)
                    <span class="detail-badge">{{ $product->category->name }}</span>
                @else
                    <span class="detail-badge">Collection ITtshirt3</span>
                @endif
                
                <h1 class="product-title">{{ $product->name }}</h1>
                <span class="price-tag">{{ $product->formattedPrice() }}</span>
                
                <p class="product-description-text mb-4">
                    {{ $product->description }}
                </p>

                <div class="row g-3 mb-5">
                    <div class="col-sm-6">
                        <div class="product-meta-card">
                            <span class="meta-label">Référence</span>
                            <span class="meta-value">{{ $product->slug }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-meta-card">
                            <span class="meta-label">Disponibilité</span>
                            <span class="meta-value {{ $product->stockBadgeClass() }}">
                                @if($product->isOutOfStock())
                                    <i class="fa-solid fa-circle-xmark me-1"></i>
                                @elseif($product->isLowStock())
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                @else
                                    <i class="fa-solid fa-check-circle me-1"></i>
                                @endif
                                {{ $product->stockLabel() }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Low-stock urgency banner --}}
                @if($product->isLowStock())
                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation flex-shrink-0"></i>
                    <span>Plus que <strong>{{ $product->stock_quantity }}</strong> article(s) disponible(s) — commandez vite !</span>
                </div>
                @endif

                {{-- Vue: add-to-cart island --}}
                @if($product->isInStock())
                <div
                    data-vue="add-to-cart"
                    data-props="{{ json_encode([
                        'productId'     => $product->id,
                        'authenticated' => auth()->check(),
                        'storeUrl'      => route('cart.items.store'),
                        'loginUrl'      => route('auth.login'),
                    ]) }}"
                ></div>
                @else
                <button class="btn btn-secondary w-100 mb-3" disabled>
                    <i class="fa-solid fa-ban me-2"></i>Rupture de stock
                </button>
                @endif

                {{-- Vue: wishlist-toggle island --}}
                <div
                    data-vue="wishlist-toggle"
                    data-props="{{ json_encode([
                        'productId'      => $product->id,
                        'inWishlist'     => (bool) $wishlistItem,
                        'wishlistItemId' => $wishlistItem?->id,
                        'authenticated'  => auth()->check(),
                        'storeUrl'       => route('wishlist.items.store'),
                        'destroyBaseUrl' => url('wishlist/items'),
                        'loginUrl'       => route('auth.login'),
                    ]) }}"
                ></div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a href="{{ route('home') }}" class="navbar-brand d-block fs-4 mb-3" style="background:linear-gradient(135deg,#60A5FA,#A78BFA);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;">ITtshirt3</a>
                    <p style="color:var(--text-3);font-size:0.875rem;">
                        La destination n°1 pour les t-shirts tech et style.<br>
                        Qualité, confort et design au rendez-vous.
                    </p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-2">
                    <h5>Boutique</h5>
                    <a href="{{ route('products.index') }}" class="footer-link">Catalogue</a>
                    <a href="#" class="footer-link">Nouveautés</a>
                    <a href="#" class="footer-link">Promotions</a>
                </div>
                <div class="col-6 col-lg-2">
                    <h5>Support</h5>
                    <a href="{{ route('contact.show') }}" class="footer-link">Contact</a>
                    <a href="#" class="footer-link">Livraison</a>
                    <a href="#" class="footer-link">Retours</a>
                </div>
                <div class="col-lg-2">
                    <h5>Social</h5>
                    <div class="d-flex gap-3">
                        <a href="{{ $siteSetting->social_instagram ?? '#' }}" style="color:var(--text-3);font-size:1.2rem;" class="footer-link d-inline mb-0"><i class="fa-brands fa-instagram"></i></a>
                        <a href="{{ $siteSetting->social_twitter ?? '#' }}" style="color:var(--text-3);font-size:1.2rem;" class="footer-link d-inline mb-0"><i class="fa-brands fa-twitter"></i></a>
                        <a href="{{ $siteSetting->social_facebook ?? '#' }}" style="color:var(--text-3);font-size:1.2rem;" class="footer-link d-inline mb-0"><i class="fa-brands fa-facebook"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color:var(--border-dim);">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small mb-0" style="color:var(--text-3);">&copy; {{ date('Y') }} ITtshirt3. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <a href="{{ route('cookies.show') }}" class="small text-decoration-none me-3" style="color:var(--text-3);">Cookies</a>
                    <a href="#" class="small text-decoration-none" style="color:var(--text-3);">CGV</a>
                </div>
            </div>
        </div>
    </footer>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-cookie-banner />
</body>
</html>
