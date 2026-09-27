<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Stripe\PaymentIntent;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Create a user, a product, a cart session, and seed a CartItem for it.
     * Returns [ $sessionId, $product, $user ].
     */
    private function makeCartWithProduct(): array
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create(['price_cents' => 1500, 'currency' => 'EUR']);
        $sessionId = Str::uuid()->toString();
        CartItem::create([
            'session_id' => $sessionId,
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        return [$sessionId, $product, $user];
    }

    /** Minimal valid address payload. */
    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'email'                  => 'client@example.com',
            'phone'                  => '0612345678',
            'shipping_name'          => 'Jane Doe',
            'shipping_address_line1' => '12 rue de la Paix',
            'shipping_address_line2' => '',
            'shipping_city'          => 'Paris',
            'shipping_postal_code'   => '75001',
            'shipping_country'       => 'FR',
        ], $overrides);
    }

    /** Minimal valid payment payload (Stripe PaymentIntent id). */
    private function paymentPayload(array $overrides = []): array
    {
        return array_merge([
            'payment_intent_id' => 'pi_test_succeeded_123',
        ], $overrides);
    }

    /**
     * Bind a mock StripePaymentService into the container that returns a fake
     * succeeded PaymentIntent when retrievePaymentIntent() is called.
     *
     * @param  string $intentId  The PaymentIntent id to return for.
     * @param  string $status    The status the fake PI should report.
     */
    private function mockStripe(string $intentId = 'pi_test_succeeded_123', string $status = 'succeeded'): void
    {
        $this->mock(StripePaymentService::class, function (MockInterface $mock) use ($intentId, $status) {
            $pi = PaymentIntent::constructFrom([
                'id'            => $intentId,
                'status'        => $status,
                'client_secret' => 'cs_test_' . $intentId,
                'object'        => 'payment_intent',
            ]);

            $mock->shouldReceive('createPaymentIntent')
                 ->andReturn($pi);

            $mock->shouldReceive('retrievePaymentIntent')
                 ->with($intentId)
                 ->andReturn($pi);
        });
    }

    // -------------------------------------------------------------------------
    // Access control – guests are redirected to login
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_to_login_when_visiting_checkout(): void
    {
        $this->get(route('checkout.address'))
             ->assertRedirect(route('auth.login'));
    }

    // -------------------------------------------------------------------------
    // Step 1 – Address
    // -------------------------------------------------------------------------

    public function test_address_page_loads_when_cart_has_items(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();

        $this->actingAs($user)
             ->withSession(['cart_session_id' => $sessionId])
             ->get(route('checkout.address'))
             ->assertOk()
             ->assertSee('Adresse de livraison');
    }

    public function test_address_page_redirects_to_cart_when_empty(): void
    {
        $user      = User::factory()->create();
        $sessionId = Str::uuid()->toString();

        $this->actingAs($user)
             ->withSession(['cart_session_id' => $sessionId])
             ->get(route('checkout.address'))
             ->assertRedirect(route('cart.index'));
    }

    public function test_address_page_prefills_user_profile_for_authenticated_user(): void
    {
        [$sessionId] = $this->makeCartWithProduct();
        $user = User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);

        $this->actingAs($user)
             ->withSession(['cart_session_id' => $sessionId])
             ->get(route('checkout.address'))
             ->assertOk()
             ->assertSee('alice@example.com');
    }

    public function test_address_validation_rejects_missing_required_fields(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();

        $this->actingAs($user)
             ->withSession(['cart_session_id' => $sessionId])
             ->post(route('checkout.address.store'), [])
             ->assertSessionHasErrors(['email', 'shipping_name', 'shipping_address_line1', 'shipping_city', 'shipping_postal_code', 'shipping_country']);
    }

    public function test_valid_address_is_stored_in_session_and_redirects_to_payment(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();

        $response = $this->actingAs($user)
                         ->withSession(['cart_session_id' => $sessionId])
                         ->post(route('checkout.address.store'), $this->addressPayload());

        $response->assertRedirect(route('checkout.payment'));
        $this->assertEquals('Jane Doe', session('checkout_address')['shipping_name']);
    }

    public function test_address_store_redirects_to_cart_when_empty(): void
    {
        $user      = User::factory()->create();
        $sessionId = Str::uuid()->toString();

        $this->actingAs($user)
             ->withSession(['cart_session_id' => $sessionId])
             ->post(route('checkout.address.store'), $this->addressPayload())
             ->assertRedirect(route('cart.index'));
    }

    // -------------------------------------------------------------------------
    // Step 2 – Payment
    // -------------------------------------------------------------------------

    public function test_payment_page_loads_with_items_and_address_in_session(): void
    {
        [$sessionId, $product, $user] = $this->makeCartWithProduct();

        $this->mockStripe();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'  => $sessionId,
                 'checkout_address' => $this->addressPayload(),
             ])
             ->get(route('checkout.payment'))
             ->assertOk()
             ->assertSee('Paiement')
             ->assertSee($product->name);
    }

    public function test_payment_page_redirects_to_cart_when_empty(): void
    {
        $user      = User::factory()->create();
        $sessionId = Str::uuid()->toString();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'  => $sessionId,
                 'checkout_address' => $this->addressPayload(),
             ])
             ->get(route('checkout.payment'))
             ->assertRedirect(route('cart.index'));
    }

    public function test_payment_page_redirects_to_address_when_no_address_in_session(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();

        $this->actingAs($user)
             ->withSession(['cart_session_id' => $sessionId])
             ->get(route('checkout.payment'))
             ->assertRedirect(route('checkout.address'));
    }

    // -------------------------------------------------------------------------
    // Step 3 – processPayment
    // -------------------------------------------------------------------------

    public function test_processing_payment_creates_order_record(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();
        $this->mockStripe();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_succeeded_123',
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload());

        $this->assertDatabaseHas('orders', [
            'session_id' => $sessionId,
            'status'     => 'paid',
            'email'      => 'client@example.com',
        ]);
    }

    public function test_processing_payment_creates_order_items_with_snapshots(): void
    {
        [$sessionId, $product, $user] = $this->makeCartWithProduct();
        $this->mockStripe();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_succeeded_123',
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload());

        $order = Order::where('session_id', $sessionId)->firstOrFail();

        $this->assertDatabaseHas('order_items', [
            'order_id'         => $order->id,
            'product_id'       => $product->id,
            'product_name'     => $product->name,
            'unit_price_cents' => 1500,
            'quantity'         => 2,
        ]);
    }

    public function test_processing_payment_stores_correct_total(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();  // 2 × 1500 = 3000 cents
        $this->mockStripe();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_succeeded_123',
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload());

        $order = Order::where('session_id', $sessionId)->firstOrFail();
        $this->assertEquals(3000, $order->total_cents);
    }

    public function test_processing_payment_stores_stripe_payment_intent_id(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();
        $this->mockStripe('pi_test_succeeded_123');

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_succeeded_123',
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload());

        $this->assertDatabaseHas('orders', [
            'stripe_payment_intent_id' => 'pi_test_succeeded_123',
        ]);
    }

    public function test_processing_payment_clears_cart_items(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();
        $this->assertDatabaseCount('cart_items', 1);
        $this->mockStripe();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_succeeded_123',
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload());

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_processing_payment_redirects_to_confirmation(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();
        $this->mockStripe();

        $response = $this->actingAs($user)
                         ->withSession([
                             'cart_session_id'            => $sessionId,
                             'checkout_address'           => $this->addressPayload(),
                             'checkout_payment_intent_id' => 'pi_test_succeeded_123',
                         ])
                         ->post(route('checkout.payment.process'), $this->paymentPayload());

        $order = Order::where('session_id', $sessionId)->firstOrFail();
        $response->assertRedirect(route('checkout.confirmation', $order->uuid));
    }

    public function test_processing_payment_clears_checkout_address_from_session(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();
        $this->mockStripe();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_succeeded_123',
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload());

        $this->assertNull(session('checkout_address'));
    }

    public function test_payment_process_redirects_to_cart_when_empty(): void
    {
        $user      = User::factory()->create();
        $sessionId = Str::uuid()->toString();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'  => $sessionId,
                 'checkout_address' => $this->addressPayload(),
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload())
             ->assertRedirect(route('cart.index'));
    }

    public function test_payment_process_redirects_to_address_when_no_session_address(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();

        $this->actingAs($user)
             ->withSession(['cart_session_id' => $sessionId])
             ->post(route('checkout.payment.process'), $this->paymentPayload())
             ->assertRedirect(route('checkout.address'));
    }

    public function test_payment_process_rejects_missing_payment_intent_id(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'  => $sessionId,
                 'checkout_address' => $this->addressPayload(),
             ])
             ->post(route('checkout.payment.process'), [])
             ->assertSessionHasErrors('payment_intent_id');
    }

    public function test_payment_process_rejects_malformed_payment_intent_id(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'  => $sessionId,
                 'checkout_address' => $this->addressPayload(),
             ])
             ->post(route('checkout.payment.process'), ['payment_intent_id' => 'not-a-pi-id'])
             ->assertSessionHasErrors('payment_intent_id');
    }

    public function test_payment_process_rejects_mismatched_payment_intent_id(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();

        // Session holds one PI id; client submits a different (valid-format) one.
        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_session_original_abc',
             ])
             ->post(route('checkout.payment.process'), ['payment_intent_id' => 'pi_different_substituted'])
             ->assertRedirect()
             ->assertSessionHasErrors('payment');

        $this->assertDatabaseMissing('orders', ['session_id' => $sessionId]);
    }

    public function test_payment_process_redirects_back_when_stripe_status_not_succeeded(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();
        $this->mockStripe('pi_test_pending', 'requires_action');

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_pending',
             ])
             ->post(route('checkout.payment.process'), ['payment_intent_id' => 'pi_test_pending'])
             ->assertRedirect()
             ->assertSessionHasErrors('payment');

        $this->assertDatabaseMissing('orders', ['session_id' => $sessionId]);
    }

    public function test_payment_process_redirects_back_on_stripe_api_error(): void
    {
        [$sessionId, , $user] = $this->makeCartWithProduct();

        $this->mock(StripePaymentService::class, function (MockInterface $mock) {
            $pi = PaymentIntent::constructFrom([
                'id'            => 'pi_err',
                'status'        => 'succeeded',
                'client_secret' => 'cs_test_pi_err',
                'object'        => 'payment_intent',
            ]);

            $mock->shouldReceive('createPaymentIntent')
                 ->andReturn($pi);

            $mock->shouldReceive('retrievePaymentIntent')
                 ->andThrow(new \Stripe\Exception\InvalidRequestException());
        });

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_err',
             ])
             ->post(route('checkout.payment.process'), ['payment_intent_id' => 'pi_err'])
             ->assertRedirect()
             ->assertSessionHasErrors('payment');
    }

    // -------------------------------------------------------------------------
    // Step 4 – Confirmation
    // -------------------------------------------------------------------------

    public function test_confirmation_page_shows_order_details(): void
    {
        [$sessionId, $product, $user] = $this->makeCartWithProduct();
        $order = Order::create([
            'user_id'                => $user->id,
            'session_id'             => $sessionId,
            'status'                 => 'paid',
            'email'                  => 'client@example.com',
            'shipping_name'          => 'Jane Doe',
            'shipping_address_line1' => '12 rue de la Paix',
            'shipping_city'          => 'Paris',
            'shipping_postal_code'   => '75001',
            'shipping_country'       => 'FR',
            'subtotal_cents'         => 3000,
            'total_cents'            => 3000,
            'currency'               => 'EUR',
            'payment_method'         => 'card',
            'paid_at'                => now(),
        ]);
        \App\Models\OrderItem::create([
            'order_id'         => $order->id,
            'product_id'       => $product->id,
            'product_name'     => $product->name,
            'unit_price_cents' => 1500,
            'quantity'         => 2,
            'currency'         => 'EUR',
        ]);

        $this->actingAs($user)
             ->withSession(['cart_session_id' => $sessionId])
             ->get(route('checkout.confirmation', $order->uuid))
             ->assertOk()
             ->assertSee($product->name)
             ->assertSee('Jane Doe');
    }

    public function test_confirmation_page_is_accessible_by_authenticated_owner(): void
    {
        $user  = User::factory()->create();
        $order = Order::create([
            'user_id'                => $user->id,
            'session_id'             => Str::uuid()->toString(),  // different session
            'status'                 => 'paid',
            'email'                  => $user->email,
            'shipping_name'          => 'User',
            'shipping_address_line1' => '1 rue Test',
            'shipping_city'          => 'Paris',
            'shipping_postal_code'   => '75000',
            'shipping_country'       => 'FR',
            'subtotal_cents'         => 1000,
            'total_cents'            => 1000,
            'currency'               => 'EUR',
            'payment_method'         => 'card',
            'paid_at'                => now(),
        ]);

        // Different cart session but user_id matches – should be allowed.
        $this->actingAs($user)
             ->withSession(['cart_session_id' => Str::uuid()->toString()])
             ->get(route('checkout.confirmation', $order->uuid))
             ->assertOk();
    }

    public function test_confirmation_page_returns_403_for_unauthorized_visitor(): void
    {
        $owner     = User::factory()->create();
        $sessionId = Str::uuid()->toString();
        $order = Order::create([
            'user_id'                => $owner->id,
            'session_id'             => $sessionId,
            'status'                 => 'paid',
            'email'                  => 'other@example.com',
            'shipping_name'          => 'Other User',
            'shipping_address_line1' => '1 rue Test',
            'shipping_city'          => 'Lyon',
            'shipping_postal_code'   => '69001',
            'shipping_country'       => 'FR',
            'subtotal_cents'         => 1000,
            'total_cents'            => 1000,
            'currency'               => 'EUR',
            'payment_method'         => 'card',
            'paid_at'                => now(),
        ]);

        // A different authenticated user with a different session should be denied.
        $intruder = User::factory()->create();
        $this->actingAs($intruder)
             ->withSession(['cart_session_id' => Str::uuid()->toString()])
             ->get(route('checkout.confirmation', $order->uuid))
             ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Stock decrement on order creation
    // -------------------------------------------------------------------------

    public function test_processing_payment_decrements_stock_for_tracked_products(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->inStock(10)->create([
            'price_cents' => 1500,
            'currency'    => 'EUR',
        ]);
        $sessionId = Str::uuid()->toString();
        CartItem::create([
            'session_id' => $sessionId,
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $this->mockStripe();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_succeeded_123',
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload())
             ->assertRedirect();

        $this->assertSame(7, $product->fresh()->stock_quantity);
    }

    public function test_processing_payment_does_not_decrement_untracked_products(): void
    {
        [$sessionId, $product, $user] = $this->makeCartWithProduct();
        $this->assertFalse($product->track_stock);
        $originalStock = $product->stock_quantity;

        $this->mockStripe();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_succeeded_123',
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload())
             ->assertRedirect();

        $this->assertSame($originalStock, $product->fresh()->stock_quantity);
    }

    public function test_processing_payment_rejects_when_stock_insufficient(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->inStock(10)->create([
            'price_cents' => 1500,
            'currency'    => 'EUR',
        ]);
        $sessionId = Str::uuid()->toString();
        CartItem::create([
            'session_id' => $sessionId,
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        // Simulate concurrent depletion of stock between cart-add and payment.
        $product->update(['stock_quantity' => 1]);

        $this->mockStripe();

        $this->actingAs($user)
             ->withSession([
                 'cart_session_id'            => $sessionId,
                 'checkout_address'           => $this->addressPayload(),
                 'checkout_payment_intent_id' => 'pi_test_succeeded_123',
             ])
             ->post(route('checkout.payment.process'), $this->paymentPayload())
             ->assertSessionHasErrors('payment');

        $this->assertDatabaseMissing('orders', ['session_id' => $sessionId]);
        // Stock untouched, cart preserved.
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('cart_items', ['session_id' => $sessionId]);
    }
}
