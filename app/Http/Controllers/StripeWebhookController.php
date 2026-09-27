<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives Stripe webhook events and updates order state accordingly.
 *
 * Route: POST /stripe/webhook  (CSRF-exempt via bootstrap/app.php)
 *
 * Events handled:
 *   - payment_intent.succeeded      → mark order paid
 *   - payment_intent.payment_failed → mark order cancelled
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripePaymentService $stripe): JsonResponse
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $secret    = config('services.stripe.webhook_secret');

        // Verify the webhook signature. Returns 400 on any mismatch so Stripe
        // will retry the event delivery.
        try {
            $event = $stripe->constructWebhookEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $pi = $event->data->object;

        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($pi),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($pi),
            default => null,   // Ignore unhandled event types gracefully.
        };

        return response()->json(['status' => 'ok']);
    }

    // -------------------------------------------------------------------------

    private function handlePaymentSucceeded(object $pi): void
    {
        // Load each matching order individually so we can assign a unique order_number.
        Order::where('stripe_payment_intent_id', $pi->id)
             ->where('status', 'pending')
             ->each(function (Order $order) {
                 $order->update([
                     'status'       => 'paid',
                     'paid_at'      => now(),
                     'order_number' => Order::generateOrderNumber($order),
                 ]);
             });
    }

    private function handlePaymentFailed(object $pi): void
    {
        Order::where('stripe_payment_intent_id', $pi->id)
             ->where('status', 'pending')
             ->update(['status' => 'cancelled']);
    }
}
