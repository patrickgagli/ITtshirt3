<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Stripe\Event;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeOrder(string $piId, string $status = 'pending'): Order
    {
        return Order::create([
            'session_id'               => Str::uuid()->toString(),
            'status'                   => $status,
            'email'                    => 'test@example.com',
            'shipping_name'            => 'Test User',
            'shipping_address_line1'   => '1 rue Test',
            'shipping_city'            => 'Paris',
            'shipping_postal_code'     => '75001',
            'shipping_country'         => 'FR',
            'subtotal_cents'           => 1000,
            'total_cents'              => 1000,
            'currency'                 => 'EUR',
            'payment_method'           => 'card',
            'stripe_payment_intent_id' => $piId,
        ]);
    }

    /**
     * Build a fake Stripe Event and bind a mock StripePaymentService that returns it.
     */
    private function mockWebhookEvent(string $type, string $piId, string $piStatus = 'succeeded'): void
    {
        $event = Event::constructFrom([
            'id'     => 'evt_test_' . $piId,
            'object' => 'event',
            'type'   => $type,
            'data'   => [
                'object' => [
                    'id'     => $piId,
                    'object' => 'payment_intent',
                    'status' => $piStatus,
                ],
            ],
        ]);

        $this->mock(StripePaymentService::class, function (MockInterface $mock) use ($event) {
            $mock->shouldReceive('constructWebhookEvent')
                 ->andReturn($event);
        });
    }

    private function postWebhook(array $payload = [], string $sig = 'valid-sig'): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(
            route('stripe.webhook'),
            $payload,
            ['Stripe-Signature' => $sig]
        );
    }

    /**
     * Generate a valid Stripe-Signature header value for a raw JSON string,
     * using the same HMAC-SHA256 algorithm as Stripe::constructEvent().
     *
     * This lets tests exercise the real signature verification path
     * without any mocking of StripePaymentService.
     */
    private function stripeSignature(string $rawJson, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $secret     = config('services.stripe.webhook_secret');
        $signed     = $timestamp . '.' . $rawJson;
        $hmac       = hash_hmac('sha256', $signed, $secret);

        return "t={$timestamp},v1={$hmac}";
    }

    /**
     * Build a minimal Stripe event JSON string for the given type and PaymentIntent id.
     */
    private function webhookJson(string $type, string $piId, string $piStatus = 'succeeded'): string
    {
        return json_encode([
            'id'     => 'evt_test_' . $piId,
            'object' => 'event',
            'type'   => $type,
            'data'   => [
                'object' => [
                    'id'     => $piId,
                    'object' => 'payment_intent',
                    'status' => $piStatus,
                ],
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Signature / payload verification
    // -------------------------------------------------------------------------

    public function test_webhook_returns_400_on_invalid_signature(): void
    {
        $this->mock(StripePaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('constructWebhookEvent')
                 ->andThrow(new \Stripe\Exception\SignatureVerificationException('bad sig'));
        });

        $this->postWebhook()->assertStatus(400);
    }

    public function test_webhook_returns_400_on_invalid_payload(): void
    {
        $this->mock(StripePaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('constructWebhookEvent')
                 ->andThrow(new \UnexpectedValueException('bad payload'));
        });

        $this->postWebhook()->assertStatus(400);
    }

    // -------------------------------------------------------------------------
    // payment_intent.succeeded
    // -------------------------------------------------------------------------

    public function test_payment_intent_succeeded_marks_pending_order_as_paid(): void
    {
        $piId  = 'pi_webhook_succeeded_test';
        $order = $this->makeOrder($piId, 'pending');

        $this->mockWebhookEvent('payment_intent.succeeded', $piId, 'succeeded');

        $this->postWebhook()->assertOk()->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'paid',
        ]);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_payment_intent_succeeded_assigns_order_number(): void
    {
        $piId  = 'pi_order_number_test';
        $order = $this->makeOrder($piId, 'pending');

        // Confirm no order_number before the webhook fires.
        $this->assertNull($order->order_number);

        $this->mockWebhookEvent('payment_intent.succeeded', $piId, 'succeeded');

        $this->postWebhook()->assertOk();

        $fresh = $order->fresh();
        $this->assertNotNull($fresh->order_number);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{6}$/', $fresh->order_number);
    }

    public function test_payment_intent_succeeded_does_not_affect_already_paid_order(): void
    {
        $piId  = 'pi_already_paid';
        $order = $this->makeOrder($piId, 'paid');

        $this->mockWebhookEvent('payment_intent.succeeded', $piId, 'succeeded');

        $this->postWebhook()->assertOk();

        // Status should remain 'paid' – the WHERE status = 'pending' guard prevents double-update.
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
    }

    // -------------------------------------------------------------------------
    // payment_intent.payment_failed
    // -------------------------------------------------------------------------

    public function test_payment_intent_failed_cancels_pending_order(): void
    {
        $piId  = 'pi_webhook_failed_test';
        $order = $this->makeOrder($piId, 'pending');

        $this->mockWebhookEvent('payment_intent.payment_failed', $piId, 'requires_payment_method');

        $this->postWebhook()->assertOk()->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'cancelled',
        ]);
    }

    // -------------------------------------------------------------------------
    // Unhandled event types
    // -------------------------------------------------------------------------

    public function test_unhandled_event_type_returns_ok_and_is_ignored(): void
    {
        $this->mockWebhookEvent('customer.created', 'pi_not_relevant');

        // No order should be created or modified.
        $this->postWebhook()->assertOk()->assertJson(['status' => 'ok']);
        $this->assertDatabaseCount('orders', 0);
    }

    // -------------------------------------------------------------------------
    // End-to-end signature verification (no mocking – real HMAC path)
    // -------------------------------------------------------------------------

    public function test_valid_signed_payload_is_accepted_and_marks_order_paid(): void
    {
        $piId  = 'pi_signed_succeed';
        $order = $this->makeOrder($piId, 'pending');

        $json = $this->webhookJson('payment_intent.succeeded', $piId);
        $sig  = $this->stripeSignature($json);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
                 'CONTENT_TYPE'           => 'application/json',
                 'HTTP_STRIPE_SIGNATURE'  => $sig,
             ], $json)
             ->assertOk()
             ->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
    }

    public function test_tampered_signature_returns_400(): void
    {
        $json = $this->webhookJson('payment_intent.succeeded', 'pi_tampered');
        $sig  = $this->stripeSignature($json);

        // Corrupt the HMAC by flipping one character.
        $corruptSig = preg_replace('/v1=([a-f0-9]{4})/', 'v1=xxxx', $sig, 1);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
                 'CONTENT_TYPE'           => 'application/json',
                 'HTTP_STRIPE_SIGNATURE'  => $corruptSig,
             ], $json)
             ->assertStatus(400);
    }

    public function test_replayed_event_with_stale_timestamp_returns_400(): void
    {
        // Stripe rejects events whose timestamp is more than 5 minutes old.
        $piId      = 'pi_replay';
        $json      = $this->webhookJson('payment_intent.succeeded', $piId);
        $staleTime = time() - 400; // 400 seconds in the past — beyond the 300 s tolerance
        $sig       = $this->stripeSignature($json, $staleTime);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
                 'CONTENT_TYPE'           => 'application/json',
                 'HTTP_STRIPE_SIGNATURE'  => $sig,
             ], $json)
             ->assertStatus(400);
    }
}
