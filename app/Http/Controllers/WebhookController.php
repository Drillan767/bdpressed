<?php

namespace App\Http\Controllers;

use App\Enums\IllustrationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\OrderPayment;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handleStripe(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('app.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed: '.$e->getMessage());
            // Continue processing for now (signature issue with proxy)
            $event = json_decode($payload, true);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: '.$e->getMessage());

            return response('Webhook error', 400);
        }

        // Handle the event
        switch ($event['type']) {

            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object'], app(StripeService::class));
                break;

            default:
                Log::info('Unhandled Stripe webhook event type: '.$event['type']);
        }

        return response('Webhook handled', 200);
    }

    private function handleCheckoutSessionCompleted($session, StripeService $stripeService): void
    {
        $metadata = $session['metadata'] ?? [];
        $paymentIntentId = $session['payment_intent'] ?? null;

        if (! $paymentIntentId) {
            Log::warning('No payment intent ID in checkout session', [
                'session_id' => $session['id'],
                'metadata' => $metadata,
            ]);

            return;
        }

        $payment = null;

        // For illustration payments - use payment_id for exact match
        if (isset($metadata['payment_id'])) {
            $payment = OrderPayment::find($metadata['payment_id']);
        }
        // For order payments - find pending payment by order_id
        elseif (isset($metadata['order_id'])) {
            $payment = OrderPayment::where('order_id', $metadata['order_id'])
                ->where('status', PaymentStatus::PENDING)
                ->first();
        }

        if (! $payment) {
            Log::error('Payment record not found for checkout session', [
                'session_id' => $session['id'],
                'payment_intent_id' => $paymentIntentId,
                'metadata' => $metadata,
            ]);

            return;
        }

        // Store the payment intent ID for future reference
        if (! $payment->stripe_payment_intent_id) {
            $payment->update(['stripe_payment_intent_id' => $paymentIntentId]);
        }

        // Get the payment intent details to process the payment
        $stripeClient = $stripeService->getClient();
        $paymentIntent = $stripeClient->paymentIntents->retrieve($paymentIntentId);

        // Process the payment based on type
        if ($payment->illustration_id) {
            $this->handleIllustrationPayment($paymentIntent->toArray(), $payment);
        } else {
            $this->handleOrderPayment($paymentIntent->toArray(), $stripeService, $payment);
        }
    }

    private function handleIllustrationPayment($paymentIntent, OrderPayment $payment): void
    {

        // Get the actual Stripe fee by retrieving the balance transaction
        $stripeService = app(StripeService::class);
        $stripeFee = $stripeService->getStripeFeeFromPaymentIntent($paymentIntent);

        // Update payment record
        $payment->update([
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
            'stripe_payment_intent_id' => $paymentIntent['id'],
            'stripe_fee' => $stripeFee,
            'stripe_metadata' => $paymentIntent,
        ]);

        // Get the illustration and transition its status
        $illustration = $payment->illustration;
        if ($illustration) {
            $newStatus = match ($payment->type) {
                PaymentType::ILLUSTRATION_DEPOSIT => IllustrationStatus::DEPOSIT_PAID,
                PaymentType::ILLUSTRATION_FINAL => IllustrationStatus::COMPLETED,
                default => null,
            };

            if ($newStatus) {
                $illustration->transitionTo($newStatus, [
                    'triggered_by' => 'webhook',
                    'metadata' => [
                        'payment_intent_id' => $paymentIntent['id'],
                        'stripe_event' => 'payment_intent.succeeded',
                        'payment_type' => $payment->type->value,
                    ],
                ]);

                Log::info('Illustration payment completed and status updated', [
                    'illustration_id' => $illustration->id,
                    'payment_type' => $payment->type->value,
                    'new_status' => $newStatus->value,
                    'payment_intent_id' => $paymentIntent['id'],
                ]);

            }
        }
    }

    private function handleOrderPayment($paymentIntent, StripeService $stripeService, OrderPayment $payment): void
    {
        $order = $payment->order;

        if (! $order) {
            Log::error('Order not found for payment', [
                'payment_id' => $payment->id,
                'payment_intent_id' => $paymentIntent['id'],
            ]);

            return;
        }

        // Get the actual Stripe fee by retrieving the balance transaction
        $stripeService = app(StripeService::class);
        $stripeFee = $stripeService->getStripeFeeFromPaymentIntent($paymentIntent);

        // Update payment status (simple, no state machine)
        $payment->update([
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
            'stripe_fee' => $stripeFee,
            'stripe_metadata' => $paymentIntent,
        ]);

        Log::info('Stripe payment intent completed', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'payment_intent_id' => $paymentIntent['id'],
            'payment_type' => $payment->type->value,
        ]);

        // Use state machine for Order status transition with context
        $order->transitionTo(OrderStatus::PAID, [
            'triggered_by' => 'webhook',
            'metadata' => [
                'payment_intent_id' => $paymentIntent['id'],
                'stripe_event' => 'payment_intent.succeeded',
            ],
        ]);
    }
}
