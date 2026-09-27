<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteSetting->site_name }} | Accueil</title>
    @if($siteSetting->meta_description)
    <meta name="description" content="{{ $siteSetting->meta_description }}">
    @endif
    @if($siteSetting->meta_keywords)
    <meta name="keywords" content="{{ $siteSetting->meta_keywords }}">
    @endif
    {{-- Open Graph --}}
    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="{{ $siteSetting->site_name }}">
    <meta property="og:title"       content="{{ $siteSetting->site_name }}{{ $siteSetting->tagline ? ' — ' . $siteSetting->tagline : '' }}">
    <meta property="og:description" content="{{ $siteSetting->meta_description ?? $siteSetting->tagline ?? $siteSetting->site_name }}">
    @if($siteSetting->og_image_url)
    <meta property="og:image"       content="{{ $siteSetting->og_image_url }}">
    @endif
    <meta property="og:url"         content="{{ url('/') }}">
    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    @if($siteSetting->twitter_handle)
    <meta name="twitter:site"        content="@{{ $siteSetting->twitter_handle }}">
    @endif
    <meta name="twitter:title"       content="{{ $siteSetting->site_name }}">
    <meta name="twitter:description" content="{{ $siteSetting->meta_description ?? $siteSetting->tagline ?? $siteSetting->site_name }}">
    @if($siteSetting->og_image_url)
    <meta name="twitter:image"       content="{{ $siteSetting->og_image_url }}">
    @endif
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS (loaded after Bootstrap so Tailwind utilities take precedence over conflicting Bootstrap classes) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ── Design tokens ─────────────────────────────────── */
        :root {
            --page-bg:      #050A14;
            --surface-1:    rgba(15, 23, 42, 0.70);
            --surface-2:    rgba(15, 23, 42, 0.90);
            --border-dim:   rgba(255,255,255,0.07);
            --border-glow:  rgba(59,130,246,0.28);
            --text-1:       #E2E8F0;
            --text-2:       #94A3B8;
            --text-3:       #475569;
            --accent-blue:  #3B82F6;
            --accent-purple:#8B5CF6;
            --accent-green: #10B981;
            --card-radius:  1.1rem;
            --font-family:  'Inter', system-ui, sans-serif;
        }

        /* ── Base ───────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: var(--font-family);
            background-color: var(--page-bg);
            color: var(--text-1);
            overflow-x: hidden;
        }

        /* ── Navbar ─────────────────────────────────────────── */
        .navbar {
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            background: rgba(5, 10, 20, 0.82) !important;
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

        .nav-link {
            color: var(--text-2) !important;
            font-weight: 600;
            transition: color 0.2s;
        }
        .nav-link:hover { color: var(--text-1) !important; }

        .navbar-toggler {
            border-color: var(--border-dim);
            color: var(--text-2);
        }
        .navbar-toggler-icon {
            filter: invert(0.7);
        }

        /* Auth links / buttons */
        .nav-auth-btn-outline {
            padding: 6px 16px;
            border-radius: 99px;
            border: 1px solid var(--border-glow);
            color: #93C5FD !important;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }
        .nav-auth-btn-outline:hover {
            background: rgba(59,130,246,0.12);
            color: #fff !important;
        }
        .nav-auth-btn-solid {
            padding: 6px 16px;
            border-radius: 99px;
            background: linear-gradient(135deg, #2563EB, #7C3AED);
            color: #fff !important;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 0 18px rgba(59,130,246,0.35);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .nav-auth-btn-solid:hover {
            box-shadow: 0 0 30px rgba(59,130,246,0.5);
            transform: translateY(-1px);
            color: #fff !important;
        }

        /* Dropdown dark */
        .dropdown-menu {
            background: var(--surface-2) !important;
            border: 1px solid var(--border-dim) !important;
            backdrop-filter: blur(18px);
        }
        .dropdown-item {
            color: var(--text-2) !important;
            font-size: 0.88rem;
        }
        .dropdown-item:hover, .dropdown-item:focus {
            background: rgba(255,255,255,0.06) !important;
            color: var(--text-1) !important;
        }
        .dropdown-divider { border-color: var(--border-dim) !important; }

        /* User dropdown toggle */
        .btn-user-toggle {
            background: none;
            border: none;
            color: var(--text-2);
            font-weight: 600;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: color 0.2s;
            padding: 0;
        }
        .btn-user-toggle:hover { color: var(--text-1); }

        /* ── Section headings shared ────────────────────────── */
        .section-eyebrow {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #60A5FA;
            margin-bottom: 0.6rem;
        }
        .section-title {
            font-size: clamp(1.9rem, 3.5vw, 2.8rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text-1);
            line-height: 1.15;
        }

        /* ── Dot-grid background helper ─────────────────────── */
        .dot-grid-bg {
            background-image: radial-gradient(rgba(99,102,241,0.18) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* ── Category section ───────────────────────────────── */
        #univers {
            position: relative;
            background: var(--page-bg);
            padding: 6rem 0;
        }
        #univers::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(99,102,241,0.14) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }

        .category-card {
            display: block;
            text-decoration: none;
            color: var(--text-1);
            background: var(--surface-1);
            border: 1px solid var(--border-dim);
            border-radius: var(--card-radius);
            padding: 2rem;
            height: 100%;
            backdrop-filter: blur(12px);
            transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s, background 0.3s;
            position: relative;
            overflow: hidden;
        }
        .category-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(59,130,246,0.06), rgba(139,92,246,0.04));
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: inherit;
            pointer-events: none;
        }
        .category-card:hover {
            border-color: rgba(59,130,246,0.4);
            box-shadow: 0 0 32px rgba(59,130,246,0.15), 0 16px 40px rgba(0,0,0,0.4);
            transform: translateY(-5px);
            color: var(--text-1);
            background: rgba(15,23,42,0.85);
        }
        .category-card:hover::before { opacity: 1; }

        .category-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: rgba(59,130,246,0.1);
            border: 1px solid rgba(59,130,246,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #60A5FA;
            margin-bottom: 1.25rem;
            transition: background 0.3s, border-color 0.3s, color 0.3s, box-shadow 0.3s;
        }
        .category-card:hover .category-icon {
            background: rgba(59,130,246,0.2);
            border-color: rgba(59,130,246,0.45);
            color: #93C5FD;
            box-shadow: 0 0 18px rgba(59,130,246,0.25);
        }
        .category-card h4 { color: var(--text-1); font-weight: 700; font-size: 1.05rem; margin-bottom: 0.4rem; }
        .category-card .cat-count { color: var(--text-3); font-size: 0.82rem; }

        /* ── Footer ─────────────────────────────────────────── */
        footer {
            background: #030711;
            border-top: 1px solid var(--border-dim);
            color: var(--text-2);
            padding: 72px 0 36px;
            margin-top: 0;
        }
        footer h4, footer h5 { color: var(--text-1); font-weight: 700; margin-bottom: 1.25rem; }
        .footer-brand-grad {
            background: linear-gradient(135deg, #60A5FA, #A78BFA);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }
        .footer-link {
            color: var(--text-3);
            text-decoration: none;
            font-size: 0.88rem;
            transition: color 0.2s;
            display: block;
            margin-bottom: 0.65rem;
        }
        .footer-link:hover { color: var(--text-1); }
        .footer-social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border-dim);
            color: var(--text-3);
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.2s, color 0.2s, border-color 0.2s, box-shadow 0.2s;
        }
        .footer-social-link:hover {
            background: rgba(59,130,246,0.15);
            border-color: rgba(59,130,246,0.35);
            color: #93C5FD;
            box-shadow: 0 0 12px rgba(59,130,246,0.2);
        }
        .footer-divider { border-color: var(--border-dim) !important; }

        /* Newsletter input dark */
        .newsletter-input {
            background: rgba(255,255,255,0.04) !important;
            border: 1px solid var(--border-dim) !important;
            color: var(--text-1) !important;
            border-radius: 8px 0 0 8px !important;
        }
        .newsletter-input::placeholder { color: var(--text-3) !important; }
        .newsletter-input:focus {
            border-color: var(--border-glow) !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12) !important;
            outline: none !important;
        }
        .newsletter-btn {
            background: linear-gradient(135deg, #2563EB, #7C3AED) !important;
            border: none !important;
            font-weight: 700 !important;
            color: #fff !important;
            border-radius: 0 8px 8px 0 !important;
            transition: box-shadow 0.2s !important;
        }
        .newsletter-btn:hover {
            box-shadow: 0 0 18px rgba(59,130,246,0.45) !important;
        }

        /* ── Cookie consent banner override ────────────────── */
        #cookie-banner {
            background: var(--surface-2) !important;
            border-top: 1px solid var(--border-dim) !important;
            color: var(--text-2) !important;
        }
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
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div data-vue="creative-hero" data-props="{{ json_encode([
        'productCount' => $productCount,
        'categoryCount' => $categoryCount,
        'lastImport' => now()->format('d/m/Y'),
        'productsUrl' => route('products.index')
    ]) }}"></div>

    <!-- Univers Grid -->
    <section id="univers" class="reveal-on-scroll">
        <div class="container py-lg-5" style="position:relative;z-index:1;">
            <div class="text-center mb-5 reveal-on-scroll">
                <span class="section-eyebrow">Nos Univers</span>
                <h2 class="section-title">Explorez nos catégories</h2>
            </div>
            <div class="row g-4">
                @php $revealDelays = [100, 200, 300, 400]; @endphp
                @foreach($categories as $category)
                <div class="col-md-6 col-lg-3 reveal-on-scroll {{ isset($revealDelays[$loop->index]) ? 'reveal-delay-' . $revealDelays[$loop->index] : '' }}">
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="category-card hover-micro-lift">
                        <div class="category-icon">
                            @php
                                $icon = match($category->slug) {
                                    'hommes' => 'fa-person',
                                    'femmes' => 'fa-person-dress',
                                    'garcons' => 'fa-child',
                                    'filles' => 'fa-child-dress',
                                    default => 'fa-shirt'
                                };
                            @endphp
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <h4>{{ $category->name }}</h4>
                        <p class="cat-count mb-0">{{ $category->products_count }} articles disponibles</p>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Showcases -->
    <div data-vue="dynamic-product-showcase" data-props="{{ json_encode([
        'products' => $featuredProducts->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'category_name' => $p->category?->name,
            'formatted_price' => $p->formattedPrice(),
            'image_url' => $p->image_path ? asset('storage/' . $p->image_path) : null,
        ]),
        'productsUrl' => route('products.index'),
        'addToCartUrl' => route('cart.items.store'),
        'csrfToken' => csrf_token()
    ]) }}"></div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="footer-brand-grad mb-3">ITtshirt3</div>
                    <p class="small mb-4" style="color:var(--text-3);line-height:1.7;">Votre destination privilégiée pour les t-shirts premium. Alliez confort, style et performance au quotidien.</p>
                    <div class="d-flex gap-2">
                        <a href="#" class="footer-social-link" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="footer-social-link" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#" class="footer-social-link" aria-label="X / Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" class="footer-social-link" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <h5>Navigation</h5>
                    <a href="{{ route('home') }}" class="footer-link">Accueil</a>
                    <a href="{{ route('products.index') }}" class="footer-link">Catalogue</a>
                    <a href="#" class="footer-link">Nouveautés</a>
                </div>
                <div class="col-md-4 col-lg-2">
                    <h5>Support</h5>
                    <a href="{{ route('contact.show') }}" class="footer-link">Contact</a>
                    <a href="#" class="footer-link">Aide &amp; FAQ</a>
                    <a href="#" class="footer-link">Livraison</a>
                    <a href="#" class="footer-link">Retours</a>
                </div>
                <div class="col-lg-4">
                    <h5>Newsletter</h5>
                    <p class="small mb-3" style="color:var(--text-3);">Recevez nos offres exclusives et l'actualité de nos collections.</p>
                    @if(session('newsletter_success'))
                    <div class="alert alert-success py-2 small mb-3" role="alert" style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25);color:#34D399;">
                        <i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>
                        {{ session('newsletter_success') }}
                    </div>
                    @elseif(session('newsletter_info'))
                    <div class="alert alert-info py-2 small mb-3" role="alert" style="background:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.25);color:#93C5FD;">
                        <i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i>
                        {{ session('newsletter_info') }}
                    </div>
                    @endif
                    <form method="POST" action="{{ route('newsletter.store') }}" aria-label="Inscription à la newsletter">
                        @csrf
                        <div class="input-group">
                            <input type="email"
                                   name="email"
                                   class="form-control newsletter-input @error('email') border-danger @enderror"
                                   placeholder="Votre email"
                                   value="{{ old('email') }}"
                                   required
                                   aria-label="Adresse e-mail pour la newsletter"
                                   maxlength="255">
                            <button type="submit" class="btn newsletter-btn">S'abonner</button>
                        </div>
                        @error('email')
                        <div class="small mt-1" style="color:#F87171;" role="alert">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            </div>
            <hr class="my-5 footer-divider">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small mb-0" style="color:var(--text-3);">&copy; 2026 ITtshirt3. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <a href="{{ route('cookies.show') }}" class="small text-decoration-none me-3" style="color:var(--text-3);">Cookies</a>
                    <a href="#" class="small text-decoration-none" style="color:var(--text-3);">CGV</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->

    <script>
        (function () {
            function animateCounter(el) {
                const target = parseInt(el.dataset.counter, 10);
                if (isNaN(target)) return;
                const duration = 1200;
                const start = performance.now();
                el.textContent = '0';
                function step(now) {
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / duration, 1);
                    // Ease-out cubic
                    const eased = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.round(eased * target);
                    if (progress < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
            }

            const counters = document.querySelectorAll('[data-counter]');
            if (!counters.length) return;

            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(function (el) { observer.observe(el); });
        })();
    </script>

    <script>
        (function () {
            // ── Typewriter ──────────────────────────────────────────────────
            var tw     = document.getElementById('typewriter-text');
            var cursor = document.querySelector('.typewriter-cursor');
            if (!tw) return;
            var text  = tw.closest('[data-typewriter]').dataset.typewriter;
            var i     = 0;
            var delay = 45;
            function type() {
                tw.textContent = text.slice(0, i++);
                if (i <= text.length) {
                    setTimeout(type, delay);
                } else if (cursor) {
                    setTimeout(function () {
                        cursor.style.animation = 'none';
                        cursor.style.opacity   = '0';
                    }, 1200);
                }
            }
            type();
        })();
    </script>

    <x-cookie-banner />
</body>
</html>


