<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Paiement</title>
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
        .step.active { border-bottom-color: var(--primary-color); color: var(--primary-color); }
        .step.done   { border-bottom-color: #22C55E; color: #22C55E; }
        .form-card { background: white; border-radius: var(--card-radius); border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 2rem; }
        .summary-card { background: white; border-radius: var(--card-radius); border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 1.5rem; position: sticky; top: 1.5rem; }
        .summary-line { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #F1F5F9; font-size: 0.9rem; }
        .summary-line:last-of-type { border: none; }
        .summary-total { font-size: 1.25rem; font-weight: 800; color: var(--primary-color); }
        .address-block { background: #F8FAFC; border-radius: 0.75rem; padding: 1rem 1.25rem; border: 1px solid #E2E8F0; font-size: 0.9rem; }
        /* Stripe Elements mount target */
        #card-element { padding: 0.625rem 0.75rem; border: 1px solid #dee2e6; border-radius: 0.375rem; background: white; transition: border-color 0.15s; }
        #card-element.StripeElement--focus { border-color: #86b7fe; outline: 0; box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25); }
        #card-element.StripeElement--invalid { border-color: #dc3545; }
        #card-errors { color: #dc3545; font-size: 0.875em; margin-top: 0.25rem; }
        footer { background: #0F172A; color: #94A3B8; padding: 40px 0 20px; margin-top: 60px; }
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
            <div class="step done"><i class="fa-solid fa-check me-1"></i>Livraison</div>
            <div class="step active"><i class="fa-solid fa-credit-card me-1"></i>Paiement</div>
            <div class="step"><i class="fa-solid fa-check me-1"></i>Confirmation</div>
        </div>

        <div class="row g-4">
            <!-- Payment form -->
            <div class="col-lg-7">
                <div class="form-card">
                    <h1 class="mb-4" style="font-size:1.5rem;font-weight:800;">Paiement</h1>

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                    @endif

                    @if(session('payment_error'))
                    <div class="alert alert-danger">{{ session('payment_error') }}</div>
                    @endif

                    @if(str_starts_with($stripeKey, 'pk_test_'))
                    <div class="alert alert-info d-flex gap-3 mb-4" role="alert">
                        <i class="fa-solid fa-flask-vial fs-5 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>Mode test — carte de simulation Stripe</strong><br>
                            Numéro : <span class="font-monospace fw-semibold">4242 4242 4242 4242</span>
                            &nbsp;·&nbsp; Expiration : <span class="font-monospace fw-semibold">12/34</span>
                            &nbsp;·&nbsp; CVC : <span class="font-monospace fw-semibold">123</span>
                        </div>
                    </div>
                    @endif

                    <!-- Shipping address recap -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-700 small text-uppercase text-muted" style="font-weight:700;letter-spacing:0.05em;">Adresse de livraison</span>
                            <a href="{{ route('checkout.address') }}" class="small text-primary text-decoration-none">Modifier</a>
                        </div>
                        <div class="address-block">
                            {{ $address['shipping_name'] }}<br>
                            {{ $address['shipping_address_line1'] }}
                            @if($address['shipping_address_line2'] ?? null)
                                , {{ $address['shipping_address_line2'] }}
                            @endif
                            <br>
                            {{ $address['shipping_postal_code'] }} {{ $address['shipping_city'] }}, {{ $address['shipping_country'] }}<br>
                            <span class="text-muted">{{ $address['email'] }}</span>
                        </div>
                    </div>

                    {{--
                        The form does NOT submit card data to our server.
                        Stripe.js intercepts the submit event, tokenises the card via
                        stripe.confirmCardPayment(), then places the resulting
                        PaymentIntent id into the hidden field before allowing the
                        form to POST. Raw card data never touches our backend.
                    --}}
                    <form id="payment-form" method="POST" action="{{ route('checkout.payment.process') }}" novalidate>
                        @csrf

                        {{-- Hidden field filled by Stripe.js before form submission --}}
                        <input type="hidden" name="payment_intent_id" id="payment-intent-id">

                        <div class="mb-3">
                            <label for="cardholder-name" class="form-label fw-600" style="font-weight:600;">
                                Nom sur la carte <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="cardholder-name"
                                   class="form-control"
                                   autocomplete="cc-name"
                                   placeholder="Prénom Nom"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label for="card-element" class="form-label fw-600" style="font-weight:600;">
                                Informations de carte <span class="text-danger">*</span>
                            </label>
                            {{-- Stripe Elements mounts here --}}
                            <div id="card-element"></div>
                            <div id="card-errors" role="alert"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('checkout.address') }}" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-2"></i>Modifier l'adresse
                            </a>
                            <button id="pay-button" type="submit" class="btn btn-primary px-5 py-3 rounded-3 fw-700" style="font-weight:700;">
                                <span id="pay-label">
                                    <i class="fa-solid fa-lock me-2"></i>Payer {{ number_format($totalCents / 100, 2, ',', ' ') }} EUR
                                </span>
                                <span id="pay-spinner" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Traitement&hellip;
                                </span>
                            </button>
                        </div>

                        <p class="text-center text-muted small mt-3 mb-0">
                            <i class="fa-solid fa-shield-halved me-1"></i>
                            Paiement sécurisé par <strong>Stripe</strong>. Vos données bancaires ne transitent jamais par nos serveurs.
                        </p>
                    </form>
                </div>
            </div>

            <!-- Order summary sidebar -->
            <div class="col-lg-5">
                <div class="summary-card">
                    <h2 class="mb-3" style="font-size:1rem;font-weight:700;">Récapitulatif</h2>
                    @foreach($items as $item)
                    <div class="summary-line">
                        <span>{{ $item->product->name }} <span class="text-muted">{{ $item->quantity }}</span></span>
                        <span>{{ number_format($item->product->price_cents * $item->quantity / 100, 2, ',', ' ') }} &euro;</span>
                    </div>
                    @endforeach
                    <div class="summary-line">
                        <span class="text-muted">Livraison</span>
                        <span class="text-success fw-600" style="font-weight:600;">Offerte</span>
                    </div>
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

    {{-- Stripe.js v3 – loaded from Stripe's own CDN as required by their PCI DSS guidelines --}}
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        (function () {
            const stripeKey    = @json($stripeKey);
            const clientSecret = @json($clientSecret);

            const stripe   = Stripe(stripeKey);
            const elements = stripe.elements();

            const cardElement = elements.create('card', {
                hidePostalCode: true,
                style: {
                    base: {
                        fontFamily: "'Inter', sans-serif",
                        fontSize:   '16px',
                        color:      '#1E293B',
                        '::placeholder': { color: '#94A3B8' },
                    },
                    invalid: { color: '#dc3545', iconColor: '#dc3545' },
                },
            });
            cardElement.mount('#card-element');

            cardElement.on('change', function (event) {
                document.getElementById('card-errors').textContent = event.error ? event.error.message : '';
            });

            const form       = document.getElementById('payment-form');
            const payButton  = document.getElementById('pay-button');
            const payLabel   = document.getElementById('pay-label');
            const paySpinner = document.getElementById('pay-spinner');

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                payButton.disabled = true;
                payLabel.classList.add('d-none');
                paySpinner.classList.remove('d-none');

                const cardholderName = document.getElementById('cardholder-name').value.trim();

                const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card:            cardElement,
                        billing_details: { name: cardholderName },
                    },
                });

                if (error) {
                    document.getElementById('card-errors').textContent = error.message;
                    payButton.disabled = false;
                    payLabel.classList.remove('d-none');
                    paySpinner.classList.add('d-none');
                    return;
                }

                if (paymentIntent.status === 'succeeded') {
                    document.getElementById('payment-intent-id').value = paymentIntent.id;
                    form.submit();
                } else {
                    document.getElementById('card-errors').textContent =
                        'Paiement non confirmé (statut : ' + paymentIntent.status + '). Veuillez réessayer.';
                    payButton.disabled = false;
                    payLabel.classList.remove('d-none');
                    paySpinner.classList.add('d-none');
                }
            });
        })();
    </script>
    <x-cookie-banner />
</body>
</html>
