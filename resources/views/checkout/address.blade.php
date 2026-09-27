<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Adresse de livraison</title>
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
        body { font-family: var(--font-family); background-color: var(--surface-bg); color: #1E293B; }
        .navbar { backdrop-filter: blur(10px); background: rgba(255,255,255,0.8); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 800; letter-spacing: -0.02em; color: var(--primary-color) !important; }
        .checkout-section { padding: 40px 0 80px; }
        .step-bar { display: flex; gap: 0; margin-bottom: 2.5rem; }
        .step { flex: 1; text-align: center; padding: 0.75rem 0.5rem; font-size: 0.8125rem; font-weight: 700; border-bottom: 3px solid #E2E8F0; color: #94A3B8; }
        .step.active { border-bottom-color: var(--primary-color); color: var(--primary-color); }
        .step.done   { border-bottom-color: #22C55E; color: #22C55E; }
        .form-card { background: white; border-radius: var(--card-radius); border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 2rem; }
        .summary-card { background: white; border-radius: var(--card-radius); border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 1.5rem; position: sticky; top: 1.5rem; }
        .summary-line { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #F1F5F9; font-size: 0.9rem; }
        .summary-line:last-of-type { border: none; }
        .summary-total { font-size: 1.25rem; font-weight: 800; color: var(--primary-color); }
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
                <a href="{{ route('cart.index') }}" class="btn btn-link text-dark p-0 position-relative" title="Mon panier">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:0.6rem;">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="checkout-section container">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cart.index') }}" class="text-decoration-none">Panier</a></li>
                <li class="breadcrumb-item active">Commande</li>
            </ol>
        </nav>

        <!-- Progress bar -->
        <div class="step-bar mb-4">
            <div class="step active"><i class="fa-solid fa-location-dot me-1"></i>Livraison</div>
            <div class="step"><i class="fa-solid fa-credit-card me-1"></i>Paiement</div>
            <div class="step"><i class="fa-solid fa-check me-1"></i>Confirmation</div>
        </div>

        <div class="row g-4">
            <!-- Form -->
            <div class="col-lg-7">
                <div class="form-card">
                    <h1 class="mb-4" style="font-size:1.5rem;font-weight:800;">Adresse de livraison</h1>

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('checkout.address.store') }}" novalidate>
                        @csrf

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-600" style="font-weight:600;">E-mail <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $defaults['email']) }}"
                                       class="form-control @error('email') is-invalid @enderror"
                                       required autocomplete="email">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-600" style="font-weight:600;">Téléphone</label>
                                <input type="tel" name="phone" value="{{ old('phone', $defaults['phone']) }}"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       autocomplete="tel">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600" style="font-weight:600;">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_name" value="{{ old('shipping_name', $defaults['name']) }}"
                                       class="form-control @error('shipping_name') is-invalid @enderror"
                                       required autocomplete="name">
                                @error('shipping_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600" style="font-weight:600;">Adresse <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_address_line1" value="{{ old('shipping_address_line1', $defaults['address_line1']) }}"
                                       class="form-control @error('shipping_address_line1') is-invalid @enderror"
                                       required autocomplete="address-line1" placeholder="Numéro et nom de rue">
                                @error('shipping_address_line1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <input type="text" name="shipping_address_line2" value="{{ old('shipping_address_line2', $defaults['address_line2']) }}"
                                       class="form-control @error('shipping_address_line2') is-invalid @enderror"
                                       autocomplete="address-line2" placeholder="Complément d'adresse (optionnel)">
                                @error('shipping_address_line2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label fw-600" style="font-weight:600;">Code postal <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_postal_code" value="{{ old('shipping_postal_code', $defaults['postal_code']) }}"
                                       class="form-control @error('shipping_postal_code') is-invalid @enderror"
                                       required autocomplete="postal-code">
                                @error('shipping_postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-7">
                                <label class="form-label fw-600" style="font-weight:600;">Ville <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_city" value="{{ old('shipping_city', $defaults['city']) }}"
                                       class="form-control @error('shipping_city') is-invalid @enderror"
                                       required autocomplete="address-level2">
                                @error('shipping_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-600" style="font-weight:600;">Pays <span class="text-danger">*</span></label>
                                <select name="shipping_country" class="form-select @error('shipping_country') is-invalid @enderror" required>
                                    <option value="FR" {{ old('shipping_country', $defaults['country']) === 'FR' ? 'selected' : '' }}>France</option>
                                    <option value="BE" {{ old('shipping_country', $defaults['country']) === 'BE' ? 'selected' : '' }}>Belgique</option>
                                    <option value="CH" {{ old('shipping_country', $defaults['country']) === 'CH' ? 'selected' : '' }}>Suisse</option>
                                    <option value="LU" {{ old('shipping_country', $defaults['country']) === 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                    <option value="CA" {{ old('shipping_country', $defaults['country']) === 'CA' ? 'selected' : '' }}>Canada</option>
                                </select>
                                @error('shipping_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-2"></i>Retour au panier
                            </a>
                            <button type="submit" class="btn btn-primary px-5 py-3 rounded-3 fw-700" style="font-weight:700;">
                                Continuer vers le paiement <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order summary sidebar -->
            <div class="col-lg-5">
                <div class="summary-card">
                    <h2 class="mb-3" style="font-size:1rem;font-weight:700;">Résumé de la commande</h2>
                    @foreach($items as $item)
                    <div class="summary-line">
                        <span>{{ $item->product->name }} <span class="text-muted">× {{ $item->quantity }}</span></span>
                        <span>{{ number_format($item->product->price_cents * $item->quantity / 100, 2, ',', ' ') }} €</span>
                    </div>
                    @endforeach
                    <div class="summary-line mt-2 pt-1" style="border-top:2px solid #E2E8F0 !important;">
                        <span class="fw-700" style="font-weight:700;">Total</span>
                        <span class="summary-total">{{ number_format($totalCents / 100, 2, ',', ' ') }} EUR</span>
                    </div>
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
