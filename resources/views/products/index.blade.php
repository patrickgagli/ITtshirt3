<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteSetting->site_name }} | Catalogue</title>
    @if($siteSetting->meta_description)
    <meta name="description" content="{{ $siteSetting->meta_description }}">
    @endif
    @if($siteSetting->meta_keywords)
    <meta name="keywords" content="{{ $siteSetting->meta_keywords }}">
    @endif
    {{-- Open Graph --}}
    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="{{ $siteSetting->site_name }}">
    <meta property="og:title"       content="{{ $siteSetting->site_name }} | Catalogue">
    <meta property="og:description" content="{{ $siteSetting->meta_description ?? $siteSetting->tagline ?? $siteSetting->site_name }}">
    @if($siteSetting->og_image_url)
    <meta property="og:image"       content="{{ $siteSetting->og_image_url }}">
    @endif
    <meta property="og:url"         content="{{ url('/catalogue') }}">
    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    @if($siteSetting->twitter_handle)
    <meta name="twitter:site"        content="@{{ $siteSetting->twitter_handle }}">
    @endif
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

        /* Subtle dot-grid ambient */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Navbar ─────────────────────────────────────────── */
        .navbar {
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            background: rgba(5,10,20,0.85) !important;
            border-bottom: 1px solid var(--border-dim);
        }
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #60A5FA, #A78BFA);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent !important;
        }
        .nav-link { color: var(--text-2) !important; font-weight: 600; transition: color 0.2s; }
        .nav-link:hover, .nav-link.active { color: var(--text-1) !important; }
        .navbar-toggler { border-color: var(--border-dim); }
        .navbar-toggler-icon { filter: invert(0.7); }

        .nav-auth-btn-outline {
            padding: 6px 16px; border-radius: 99px;
            border: 1px solid var(--border-glow);
            color: #93C5FD !important;
            font-size: 0.85rem; font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }
        .nav-auth-btn-outline:hover { background: rgba(59,130,246,0.12); color: #fff !important; }
        .nav-auth-btn-solid {
            padding: 6px 16px; border-radius: 99px;
            background: linear-gradient(135deg, #2563EB, #7C3AED);
            color: #fff !important;
            font-size: 0.85rem; font-weight: 700;
            text-decoration: none;
            box-shadow: 0 0 18px rgba(59,130,246,0.35);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .nav-auth-btn-solid:hover { box-shadow: 0 0 30px rgba(59,130,246,0.5); transform: translateY(-1px); color: #fff !important; }

        .btn-user-toggle {
            background: none; border: none;
            color: var(--text-2); font-weight: 600; font-size: 0.88rem;
            display: inline-flex; align-items: center; gap: 6px;
            cursor: pointer; transition: color 0.2s; padding: 0;
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

        /* ── Hero mini ───────────────────────────────────────── */
        .hero-mini {
            padding: 72px 0 52px;
            position: relative;
        }
        .hero-mini::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-glow), transparent);
        }

        .hero-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
            color: var(--text-1);
        }
        .hero-title span {
            background: linear-gradient(135deg, #60A5FA, #A78BFA);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-badge {
            background: rgba(59,130,246,0.1);
            color: var(--accent-blue);
            border: 1px solid rgba(59,130,246,0.22);
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 1rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        /* ── Filter bar overrides ─────────────────────────── */
        .btn-outline-secondary {
            border-color: var(--border-dim) !important;
            color: var(--text-2) !important;
        }
        .btn-outline-secondary:hover {
            background: rgba(255,255,255,0.06) !important;
            color: var(--text-1) !important;
        }
        .btn-primary { background-color: var(--accent-blue) !important; border-color: var(--accent-blue) !important; }
        .btn-dark { background: rgba(255,255,255,0.08) !important; border-color: var(--border-dim) !important; color: var(--text-1) !important; }

        .form-control, .form-select {
            background: var(--surface-1) !important;
            border-color: var(--border-dim) !important;
            color: var(--text-1) !important;
        }
        .form-control::placeholder { color: var(--text-3) !important; }
        .form-control:focus, .form-select:focus {
            background: var(--surface-2) !important;
            border-color: var(--border-glow) !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.10) !important;
        }
        .form-select option { background: #0F172A; color: var(--text-1); }

        /* ── Product Cards — FRAMEGEN treatment ─────────────── */
        .product-card {
            background: var(--surface-1);
            border-radius: var(--card-radius);
            overflow: visible;
            border: 1px solid var(--border-dim);
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: var(--text-1);
            position: relative;
            backdrop-filter: blur(8px);
            overflow: hidden;
        }

        /* FRAMEGEN: L-shaped corner reticles — all four corners via inner spans */
        .card-tic {
            position: absolute;
            width: 16px; height: 16px;
            z-index: 4; opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .card-tic.tl { top: 8px; left: 8px; border-top: 2px solid var(--accent-blue); border-left: 2px solid var(--accent-blue); }
        .card-tic.tr { top: 8px; right: 8px; border-top: 2px solid var(--accent-blue); border-right: 2px solid var(--accent-blue); }
        .card-tic.bl { bottom: 8px; left: 8px; border-bottom: 2px solid var(--accent-blue); border-left: 2px solid var(--accent-blue); }
        .card-tic.br { bottom: 8px; right: 8px; border-bottom: 2px solid var(--accent-blue); border-right: 2px solid var(--accent-blue); }
        .product-card:hover .card-tic { opacity: 1; }

        /* FRAMEGEN: sweep-line on hover */
        .card-sweep {
            position: absolute;
            inset: 0; z-index: 5;
            pointer-events: none;
            overflow: hidden;
        }
        .card-sweep::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, rgba(59,130,246,0.55), transparent);
            transform: translateY(-100%);
        }
        .product-card:hover .card-sweep::after {
            transform: translateY(420px);
            transition: transform 1.1s ease-in-out;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 30px rgba(59,130,246,0.12), 0 20px 50px rgba(0,0,0,0.4);
            border-color: var(--border-glow);
        }

        /* LUMNI: glow for featured products */
        .product-card.is-featured {
            border-color: rgba(139,92,246,0.18);
            box-shadow: 0 0 18px rgba(139,92,246,0.08);
        }
        .product-card.is-featured:hover {
            border-color: rgba(139,92,246,0.4);
            box-shadow: 0 0 40px rgba(139,92,246,0.22), 0 20px 50px rgba(0,0,0,0.4);
        }

        .product-img-wrapper {
            height: 250px;
            background: rgba(15,23,42,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* FRAMEGEN: scanline overlay on hover */
        .product-img-wrapper::before {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(
                0deg, transparent, transparent 3px,
                rgba(0,0,0,0.07) 3px, rgba(0,0,0,0.07) 4px
            );
            pointer-events: none; z-index: 2;
            opacity: 0; transition: opacity 0.3s ease;
        }
        .product-card:hover .product-img-wrapper::before { opacity: 1; }

        .product-img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .product-card:hover .product-img { transform: scale(1.05); }

        /* Placeholder art — dark glassmorphism */
        .product-placeholder-art {
            width: 100%; height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 0.75rem;
        }
        .product-placeholder-art[data-color="0"] { background: linear-gradient(135deg, rgba(102,126,234,0.28), rgba(118,75,162,0.28)); }
        .product-placeholder-art[data-color="1"] { background: linear-gradient(135deg, rgba(240,147,251,0.28), rgba(245,87,108,0.28)); }
        .product-placeholder-art[data-color="2"] { background: linear-gradient(135deg, rgba(79,172,254,0.28), rgba(0,242,254,0.28)); }
        .product-placeholder-art[data-color="3"] { background: linear-gradient(135deg, rgba(67,233,123,0.28), rgba(56,249,215,0.28)); }
        .product-placeholder-art[data-color="4"] { background: linear-gradient(135deg, rgba(250,112,154,0.28), rgba(254,225,64,0.28)); }
        .product-placeholder-art[data-color="5"] { background: linear-gradient(135deg, rgba(161,140,209,0.28), rgba(251,194,235,0.28)); }
        .product-placeholder-art .placeholder-icon { font-size: 4rem; color: rgba(255,255,255,0.2); }
        .product-placeholder-art .placeholder-initials { font-size: 1.25rem; font-weight: 800; color: rgba(255,255,255,0.7); letter-spacing: 0.06em; }

        /* Badges */
        .product-badge {
            position: absolute; top: 1rem; right: 1rem;
            background: linear-gradient(135deg, var(--accent-purple), var(--accent-blue));
            color: white; padding: 0.25rem 0.75rem;
            border-radius: 2rem; font-size: 0.72rem; font-weight: 700; z-index: 3;
        }
        .product-category-badge {
            position: absolute; top: 1rem; left: 1rem;
            background: rgba(5,10,20,0.7); backdrop-filter: blur(6px);
            color: var(--text-2); padding: 0.22rem 0.65rem;
            border-radius: 2rem; font-size: 0.72rem; font-weight: 600; z-index: 3;
            border: 1px solid var(--border-dim);
        }
        .product-stock-badge {
            position: absolute; bottom: 0.75rem; left: 1rem;
            padding: 0.22rem 0.65rem; border-radius: 2rem;
            font-size: 0.72rem; font-weight: 600; z-index: 3;
        }
        .product-stock-badge.stock-oos { background: rgba(220,53,69,0.88); color: #fff; }
        .product-stock-badge.stock-low { background: rgba(255,193,7,0.88); color: #000; }

        .product-img-wrapper.stock-oos::after {
            content: ''; position: absolute; inset: 0;
            background: rgba(0,0,0,0.42); z-index: 2;
        }
        .product-img-wrapper.stock-oos .product-img,
        .product-img-wrapper.stock-oos .product-placeholder-art { filter: grayscale(55%); opacity: 0.72; }

        .product-body { padding: 1.4rem; flex: 1; display: flex; flex-direction: column; }
        .product-title { font-weight: 700; font-size: 1.05rem; margin-bottom: 0.35rem; color: var(--text-1); }
        .product-description {
            font-size: 0.85rem; color: var(--text-2); margin-bottom: 0.9rem; line-height: 1.5;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .product-footer {
            margin-top: auto; display: flex; justify-content: space-between; align-items: center;
            padding-top: 0.9rem; border-top: 1px solid var(--border-dim);
        }
        .product-price { font-weight: 800; font-size: 1.15rem; color: var(--accent-blue); }
        .product-card.is-featured .product-price { color: var(--accent-purple); }

        /* ── Empty state ─────────────────────────────────── */
        .empty-state {
            padding: 80px 0; text-align: center;
            background: var(--surface-1);
            border: 1px solid var(--border-dim);
            border-radius: var(--card-radius);
        }

        /* ── Footer ──────────────────────────────────────── */
        footer {
            background: #030711;
            color: var(--text-2);
            padding: 60px 0 30px;
            margin-top: 80px;
            border-top: 1px solid var(--border-dim);
        }
        footer h5 { color: var(--text-1); font-weight: 700; margin-bottom: 1.5rem; }
        .footer-link {
            color: var(--text-3); text-decoration: none;
            transition: color 0.3s ease; display: block; margin-bottom: 0.75rem;
        }
        .footer-link:hover { color: var(--text-1); }

        @keyframes catalogReveal {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .reveal-card { animation: catalogReveal 0.5s ease both; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                        <a class="nav-link active" href="{{ route('products.index') }}">Catalogue</a>
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

    <!-- Header Section -->
    <header class="hero-mini">
        <div class="container" style="position:relative;z-index:1;">
            <div class="row">
                <div class="col-lg-8">
                    <span class="hero-badge">Notre Collection</span>
                    <h1 class="hero-title">Explorez le <span>catalogue</span> complet.</h1>
                    <p style="color:var(--text-2);font-size:1.05rem;">
                        @if($search !== '')
                            {{ $products->total() }} résultat(s) pour &laquo;&nbsp;<strong style="color:var(--text-1);">{{ $search }}</strong>&nbsp;&raquo;
                            @if($activeCategory) dans <strong style="color:var(--text-1);">{{ $activeCategory->name }}</strong>@endif.
                        @elseif($activeCategory)
                            {{ $products->total() }} modèle(s) dans la catégorie <strong style="color:var(--text-1);">{{ $activeCategory->name }}</strong>.
                        @else
                            Trouvez le t-shirt parfait parmi nos <strong style="color:var(--accent-blue);">{{ $products->total() }}</strong> modèles disponibles.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </header>

    <!-- Product Grid -->
    <main class="container py-4">

        {{-- Category filter bar --}}
        @php
            $filterParams = array_filter([
                'q'            => $search ?: null,
                'sort'         => $sort !== 'featured' ? $sort : null,
                'availability' => $availability !== 'all' ? $availability : null,
            ]);
        @endphp
        @if($categories->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
            <a href="{{ route('products.index', $filterParams) }}"
               class="btn btn-sm rounded-pill {{ $activeCategory === null ? 'btn-primary' : 'btn-outline-secondary' }}">
                <i class="fa-solid fa-layer-group me-1"></i>Toutes les catégories
            </a>
            @foreach($categories as $category)
            <a href="{{ route('products.index', array_merge($filterParams, ['category' => $category->slug])) }}"
               class="btn btn-sm rounded-pill {{ $activeCategory?->id === $category->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $category->name }}
                <span class="badge {{ $activeCategory?->id === $category->id ? 'bg-white text-primary' : 'bg-secondary bg-opacity-10 text-secondary' }} ms-1 rounded-pill">
                    {{ $category->products_count }}
                </span>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Search / availability / sort toolbar --}}
        <div class="row g-2 mb-4 align-items-center">
            {{-- Search --}}
            <div class="col-12 col-md-5">
                <form method="GET" action="{{ route('products.index') }}" class="d-flex gap-2">
                    @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->slug }}">@endif
                    @if($sort !== 'featured')<input type="hidden" name="sort" value="{{ $sort }}">@endif
                    @if($availability !== 'all')<input type="hidden" name="availability" value="{{ $availability }}">@endif
                    <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un produit…"
                           class="form-control form-control-sm rounded-pill">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3" title="Rechercher">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if($search !== '')
                    <a href="{{ route('products.index', array_merge($filterParams, $activeCategory ? ['category' => $activeCategory->slug] : [])) }}"
                       class="btn btn-outline-secondary btn-sm rounded-pill" title="Effacer la recherche">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    @endif
                </form>
            </div>

            {{-- Availability filter --}}
            <div class="col-12 col-md-4">
                @php
                    $availParams = array_filter([
                        'q'        => $search ?: null,
                        'sort'     => $sort !== 'featured' ? $sort : null,
                        'category' => $activeCategory?->slug,
                    ]);
                @endphp
                <div class="d-flex flex-wrap gap-1">
                    <a href="{{ route('products.index', array_merge($availParams, ['availability' => 'all'])) }}"
                       class="btn btn-sm rounded-pill {{ $availability === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                        Tous
                        <span class="badge ms-1 {{ $availability === 'all' ? 'bg-white text-dark' : 'bg-secondary bg-opacity-25 text-secondary' }} rounded-pill">{{ $stockCounts['all'] }}</span>
                    </a>
                    <a href="{{ route('products.index', array_merge($availParams, ['availability' => 'in_stock'])) }}"
                       class="btn btn-sm rounded-pill {{ $availability === 'in_stock' ? 'btn-success' : 'btn-outline-success' }}">
                        Disponible
                        <span class="badge ms-1 {{ $availability === 'in_stock' ? 'bg-white text-success' : 'bg-success bg-opacity-10 text-success' }} rounded-pill">{{ $stockCounts['in_stock'] }}</span>
                    </a>
                    <a href="{{ route('products.index', array_merge($availParams, ['availability' => 'low_stock'])) }}"
                       class="btn btn-sm rounded-pill {{ $availability === 'low_stock' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                        Stock limité
                        @if($stockCounts['low_stock'] > 0)
                        <span class="badge ms-1 {{ $availability === 'low_stock' ? 'bg-white text-warning' : 'bg-warning bg-opacity-15 text-warning' }} rounded-pill">{{ $stockCounts['low_stock'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('products.index', array_merge($availParams, ['availability' => 'out_of_stock'])) }}"
                       class="btn btn-sm rounded-pill {{ $availability === 'out_of_stock' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Épuisé
                        @if($stockCounts['out_of_stock'] > 0)
                        <span class="badge ms-1 {{ $availability === 'out_of_stock' ? 'bg-white text-danger' : 'bg-danger bg-opacity-10 text-danger' }} rounded-pill">{{ $stockCounts['out_of_stock'] }}</span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- Sort --}}
            <div class="col-12 col-md-3 text-md-end">
                <form method="GET" action="{{ route('products.index') }}">
                    @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->slug }}">@endif
                    @if($search !== '')<input type="hidden" name="q" value="{{ $search }}">@endif
                    @if($availability !== 'all')<input type="hidden" name="availability" value="{{ $availability }}">@endif
                    <select name="sort" class="form-select form-select-sm rounded-pill d-inline-block w-auto"
                            onchange="this.form.submit()" aria-label="Trier par">
                        <option value="featured"   {{ $sort === 'featured'   ? 'selected' : '' }}>Top ventes</option>
                        <option value="name_asc"   {{ $sort === 'name_asc'   ? 'selected' : '' }}>Nom A → Z</option>
                        <option value="name_desc"  {{ $sort === 'name_desc'  ? 'selected' : '' }}>Nom Z → A</option>
                        <option value="price_asc"  {{ $sort === 'price_asc'  ? 'selected' : '' }}>Prix croissant</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                        <option value="stock"      {{ $sort === 'stock'      ? 'selected' : '' }}>Disponibilité</option>
                    </select>
                </form>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="empty-state p-5 mt-4">
                <i class="fa-solid fa-box-open fs-1 mb-3" style="color:var(--text-3);"></i>
                @if($search !== '')
                    <h3 class="fw-bold">Aucun résultat pour &laquo;&nbsp;{{ $search }}&nbsp;&raquo;.</h3>
                    <p class="text-muted">Essayez un autre mot-clé ou supprimez les filtres actifs.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">Voir tous les produits</a>
                @elseif($availability !== 'all')
                    <h3 class="fw-bold">Aucun produit ne correspond à ce filtre de disponibilité.</h3>
                    <p class="text-muted">Modifiez le filtre ou consultez l'ensemble du catalogue.</p>
                    <a href="{{ route('products.index', array_filter(['category' => $activeCategory?->slug, 'q' => $search ?: null])) }}" class="btn btn-primary mt-3">Voir tous les produits</a>
                @elseif($activeCategory)
                    <h3 class="fw-bold">Aucun produit dans cette catégorie.</h3>
                    <p class="text-muted">Aucun produit n'est disponible dans la catégorie <strong>{{ $activeCategory->name }}</strong>.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">Voir tous les produits</a>
                @else
                    <h3 class="fw-bold">Aucun produit n'est disponible pour le moment.</h3>
                    <p class="text-muted">Lancez le seeding pour charger la sélection initiale ou revenez plus tard.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary mt-3">Retour à l'accueil</a>
                @endif
            </div>
        @else
            <div class="row g-4">
                @foreach ($products as $product)
                    <div class="col-sm-6 col-lg-4 col-xl-3 reveal-on-scroll">
                        <a href="{{ route('products.show', $product->slug) }}"
                           class="product-card{{ $product->is_featured ? ' is-featured' : '' }}{{ $product->isOutOfStock() ? ' opacity-85' : '' }}"
                           style="animation-delay:{{ $loop->index * 60 }}ms"
                           aria-label="{{ $product->name }}{{ $product->isOutOfStock() ? ' — Rupture de stock' : ($product->isLowStock() ? ' — Stock limité' : '') }}">
                            {{-- FRAMEGEN corner reticles --}}
                            <span class="card-tic tl" aria-hidden="true"></span>
                            <span class="card-tic tr" aria-hidden="true"></span>
                            <span class="card-tic bl" aria-hidden="true"></span>
                            <span class="card-tic br" aria-hidden="true"></span>
                            {{-- FRAMEGEN sweep line --}}
                            <span class="card-sweep" aria-hidden="true"></span>
                            <div class="product-img-wrapper{{ $product->isOutOfStock() ? ' is-out-of-stock stock-oos' : '' }}">
                                {{-- Featured badge (top-right) --}}
                                @if($product->is_featured)
                                    <span class="product-badge">Top Vente</span>
                                @endif

                                {{-- Category badge (top-left) --}}
                                @if($product->category)
                                    <span class="product-category-badge">{{ $product->category->name }}</span>
                                @endif

                                {{-- Stock status badge (bottom-left) --}}
                                @if($product->isOutOfStock())
                                    <span class="product-stock-badge is-out-of-stock stock-oos">
                                        <i class="fa-solid fa-ban me-1"></i>Rupture
                                    </span>
                                @elseif($product->isLowStock())
                                    <span class="product-stock-badge is-low-stock stock-low">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $product->stock_quantity }} restant(s)
                                    </span>
                                @endif

                                @if ($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-img">
                                @else
                                    <div class="product-placeholder-art" data-color="{{ $product->id % 6 }}" aria-label="{{ $product->name }}">
                                        <i class="fa-solid fa-shirt placeholder-icon" aria-hidden="true"></i>
                                        <span class="placeholder-initials">{{ mb_strtoupper(mb_substr($product->name, 0, 2)) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="product-body">
                                <h2 class="product-title">{{ $product->name }}</h2>
                                <p class="product-description">{{ $product->description }}</p>

                                <div class="product-footer">
                                    <span class="product-price {{ $product->isOutOfStock() ? 'opacity-50' : '' }}">{{ $product->formattedPrice() }}</span>
                                    <span class="btn btn-sm rounded-pill px-3" style="border: 1px solid {{ $product->isOutOfStock() ? 'var(--border-dim)' : 'var(--border-glow)' }}; color: {{ $product->isOutOfStock() ? 'var(--text-3)' : 'var(--accent-blue)' }}; font-size:0.8rem;">
                                        @if($product->isOutOfStock())
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Indisponible
                                        @else
                                            Voir &rarr;
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $products->links() }}
            </div>
            @endif
        @endif
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

    <x-cookie-banner />
</body>
</html>
