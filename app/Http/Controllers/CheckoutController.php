<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ResolvesCartItems;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    use ResolvesCartItems;

    // -------------------------------------------------------------------------
    // Step 1 – Shipping address
    // -------------------------------------------------------------------------

    public function showAddress(Request $request): View|RedirectResponse
    {
        $items = $this->cartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('info', 'Votre panier est vide.');
        }

        // Pre-fill from session (if the user went back) or from the user profile.
        $saved = $request->session()->get('checkout_address', []);
        $user  = $request->user();

        $defaults = [
            'name'          => $saved['shipping_name']     ?? $user?->name    ?? '',
            'email'         => $saved['email']             ?? $user?->email   ?? '',
            'phone'         => $saved['phone']             ?? '',
            'address_line1' => $saved['shipping_address_line1'] ?? '',
            'address_line2' => $saved['shipping_address_line2'] ?? '',
            'city'          => $saved['shipping_city']     ?? '',
            'postal_code'   => $saved['shipping_postal_code'] ?? '',
            'country'       => $saved['shipping_country'] ?? 'FR',
        ];

        $totalCents = $items->sum(fn (CartItem $i) => $i->product->price_cents * $i->quantity);

        return view('checkout.address', compact('items', 'totalCents', 'defaults'));
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $items = $this->cartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('info', 'Votre panier est vide.');
        }

        $validated = $request->validate([
            'email'         => ['required', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'shipping_name'          => ['required', 'string', 'max:255'],
            'shipping_address_line1' => ['required', 'string', 'max:255'],
            'shipping_address_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city'          => ['required', 'string', 'max:100'],
            'shipping_postal_code'   => ['required', 'string', 'max:20'],
            'shipping_country'       => ['required', 'string', 'size:2'],
        ]);

        $request->session()->put('checkout_address', $validated);

        return redirect()->route('checkout.payment');
    }

    // -------------------------------------------------------------------------
    // Step 2 – Payment
    // -------------------------------------------------------------------------

    public function showPayment(Request $request, StripePaymentService $stripe): View|RedirectResponse
    {
        $items = $this->cartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('info', 'Votre panier est vide.');
        }

        if (! $request->session()->has('checkout_address')) {
            return redirect()->route('checkout.address');
        }

        $address    = $request->session()->get('checkout_address');
        $totalCents = $items->sum(fn (CartItem $i) => $i->product->price_cents * $i->quantity);

        // Create a Stripe PaymentIntent for this amount.
        // We always create a fresh one so the amount reflects the current cart.
        $pi = $stripe->createPaymentIntent($totalCents, 'eur', [
            'order_session' => $this->cartSessionId($request),
        ]);

        // Store the PI id in session so processPayment can verify it came from here.
        $request->session()->put('checkout_payment_intent_id', $pi->id);

        return view('checkout.payment', [
            'items'        => $items,
            'totalCents'   => $totalCents,
            'address'      => $address,
            'stripeKey'    => config('services.stripe.key'),
            'clientSecret' => $pi->client_secret,
        ]);
    }

    public function processPayment(Request $request, StripePaymentService $stripe): RedirectResponse
    {
        $items = $this->cartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('info', 'Votre panier est vide.');
        }

        if (! $request->session()->has('checkout_address')) {
            return redirect()->route('checkout.address');
        }

        // The Stripe.js front-end sends us the PaymentIntent id after the card
        // has been confirmed client-side. We re-retrieve it from Stripe to verify
        // the status server-side – never trust the client alone.
        $request->validate([
            'payment_intent_id' => ['required', 'string', 'regex:/^pi_[a-zA-Z0-9_]+$/'],
        ], [
            'payment_intent_id.required' => 'Aucune intention de paiement reçue.',
            'payment_intent_id.regex'    => 'Identifiant de paiement invalide.',
        ]);

        // Verify the submitted PaymentIntent ID matches the one we generated for
        // this checkout session. This prevents an attacker from substituting a
        // different (lower-value) succeeded PI to underpay for an order.
        $sessionPiId = $request->session()->get('checkout_payment_intent_id');
        if (! $sessionPiId || $sessionPiId !== $request->input('payment_intent_id')) {
            return back()->withErrors([
                'payment' => 'Session de paiement invalide. Veuillez recharger la page de paiement.',
            ]);
        }

        try {
            $pi = $stripe->retrievePaymentIntent($request->input('payment_intent_id'));
        } catch (\Stripe\Exception\ApiErrorException) {
            return back()->withErrors(['payment' => 'Impossible de vérifier le paiement. Veuillez réessayer.']);
        }

        if ($pi->status !== 'succeeded') {
            return back()->withErrors([
                'payment' => 'Le paiement n\'a pas abouti (statut : ' . $pi->status . '). Veuillez réessayer.',
            ]);
        }

        $address   = $request->session()->get('checkout_address');
        $sessionId = $this->cartSessionId($request);

        $totalCents = $items->sum(fn (CartItem $i) => $i->product->price_cents * $i->quantity);

        // Atomically validate stock and create the order. If any tracked product
        // no longer has enough stock (race condition between checkout steps),
        // refuse to create the order — the Stripe charge will need a manual
        // refund, but no over-sold orders are recorded.
        try {
            $order = DB::transaction(function () use ($items, $request, $address, $sessionId, $totalCents, $pi) {
                // Lock tracked products for update to prevent concurrent overselling.
                $trackedIds = $items->pluck('product_id')->all();
                $locked     = Product::whereIn('id', $trackedIds)
                    ->where('track_stock', true)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $cartItem) {
                    $product = $locked->get($cartItem->product_id);
                    if ($product && $product->stock_quantity < $cartItem->quantity) {
                        throw new \RuntimeException(
                            "Stock insuffisant pour « {$cartItem->product->name} » : "
                            . "{$product->stock_quantity} disponible(s), {$cartItem->quantity} demandé(s)."
                        );
                    }
                }

                $order = Order::create([
                    'user_id'                  => $request->user()?->id,
                    'session_id'               => $sessionId,
                    'status'                   => 'paid',
                    'email'                    => $address['email'],
                    'phone'                    => $address['phone'] ?? null,
                    'shipping_name'            => $address['shipping_name'],
                    'shipping_address_line1'   => $address['shipping_address_line1'],
                    'shipping_address_line2'   => $address['shipping_address_line2'] ?? null,
                    'shipping_city'            => $address['shipping_city'],
                    'shipping_postal_code'     => $address['shipping_postal_code'],
                    'shipping_country'         => $address['shipping_country'],
                    'subtotal_cents'           => $totalCents,
                    'total_cents'              => $totalCents,
                    'currency'                 => 'EUR',
                    'payment_method'           => 'card',
                    'stripe_payment_intent_id' => $pi->id,
                    'paid_at'                  => now(),
                ]);

                // Assign a human-readable order number now that we have the DB id.
                $order->update(['order_number' => Order::generateOrderNumber($order)]);

                // Snapshot each cart item into an order item and decrement stock
                // for tracked products.
                foreach ($items as $cartItem) {
                    OrderItem::create([
                        'order_id'         => $order->id,
                        'product_id'       => $cartItem->product_id,
                        'product_name'     => $cartItem->product->name,
                        'unit_price_cents' => $cartItem->product->price_cents,
                        'quantity'         => $cartItem->quantity,
                        'currency'         => $cartItem->product->currency,
                    ]);

                    if ($product = $locked->get($cartItem->product_id)) {
                        $product->decrement('stock_quantity', $cartItem->quantity);
                    }
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        // Clear the cart.
        CartItem::where('session_id', $sessionId)->delete();

        // Clear checkout session data.
        $request->session()->forget(['checkout_address', 'checkout_payment_intent_id']);

        return redirect()->route('checkout.confirmation', $order->uuid)
            ->with('success', 'Commande confirmée ! Merci pour votre achat.');
    }

    // -------------------------------------------------------------------------
    // Step 3 – Confirmation
    // -------------------------------------------------------------------------

    public function showConfirmation(Request $request, Order $order): View|RedirectResponse
    {
        // Authorise: the order must belong to the current session or authenticated user.
        $ownedBySession = $order->session_id === $request->session()->get('cart_session_id');
        $ownedByUser    = $request->user() && $order->user_id === $request->user()->id;

        abort_unless($ownedBySession || $ownedByUser, 403);

        $order->load('items');

        return view('checkout.confirmation', compact('order'));
    }
}
