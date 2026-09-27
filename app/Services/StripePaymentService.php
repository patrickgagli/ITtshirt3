<?php

namespace App\Services;

use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * Thin wrapper around the Stripe SDK so the rest of the app never depends on
 * the concrete Stripe classes directly and can be swapped in tests via the
 * service container.
 */
class StripePaymentService
{
    private StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a new Stripe PaymentIntent.
     *
     * @param  int    $amountCents  Amount in the smallest currency unit (e.g. cents).
     * @param  string $currency     ISO-4217 currency code, lowercase (e.g. "eur").
     * @param  array  $metadata     Arbitrary key-value pairs stored with the PaymentIntent.
     */
    public function createPaymentIntent(int $amountCents, string $currency = 'eur', array $metadata = []): PaymentIntent
    {
        return $this->client->paymentIntents->create([
            'amount'                    => $amountCents,
            'currency'                  => strtolower($currency),
            'automatic_payment_methods' => ['enabled' => true],
            'metadata'                  => $metadata,
        ]);
    }

    /**
     * Retrieve an existing Stripe PaymentIntent by its ID.
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->client->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Construct and verify a Stripe webhook Event from the raw request body and
     * Stripe-Signature header.
     *
     * @throws \Stripe\Exception\SignatureVerificationException If the signature is invalid.
     * @throws \UnexpectedValueException If the payload cannot be parsed.
     */
    public function constructWebhookEvent(string $payload, string $sigHeader, string $secret): Event
    {
        return Webhook::constructEvent($payload, $sigHeader, $secret);
    }
}
