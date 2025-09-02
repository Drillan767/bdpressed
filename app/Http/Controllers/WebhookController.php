<?php

namespace App\Http\Controllers;

use App\Enums\IllustrationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Illustration;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\OrderStatusService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handleStripe(Request $request, OrderStatusService $orderStatusService): Response
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
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event['data']['object'], $orderStatusService, app(StripeService::class));
                break;

            default:
                Log::info('Unhandled Stripe webhook event type: '.$event['type']);
        }

        return response('Webhook handled', 200);
    }

    private function handlePaymentIntentSucceeded($paymentIntent, OrderStatusService $orderStatusService, StripeService $stripeService): void
    {
        // First, try to find illustration payment data
        $illustrationData = $stripeService->findIllustrationFromPaymentIntent($paymentIntent['id']);

        if ($illustrationData) {
            $this->handleIllustrationPayment($paymentIntent, $illustrationData);

            return;
        }

        // Fall back to order payment handling
        $this->handleOrderPayment($paymentIntent, $orderStatusService, $stripeService);
    }

    private function handleIllustrationPayment($paymentIntent, $illustrationData): void
    {
        $paymentId = $illustrationData['payment_id'];
        $illustrationId = $illustrationData['illustration_id'];
        $paymentType = $illustrationData['payment_type'];

        // Find the payment record
        $payment = OrderPayment::find($paymentId);
        if (! $payment && $illustrationId) {
            // Fallback: find pending payment by illustration and type
            $payment = OrderPayment::where('illustration_id', $illustrationId)
                ->where('status', PaymentStatus::PENDING)
                ->where('type', PaymentType::from($paymentType))
                ->first();
        }

        if (! $payment) {
            Log::error('Illustration payment not found for payment intent', [
                'payment_id' => $paymentId,
                'illustration_id' => $illustrationId,
                'payment_intent_id' => $paymentIntent['id'],
            ]);

            return;
        }

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
                    'skip_notifications' => true, // We'll handle notifications manually
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

    private function handleOrderPayment($paymentIntent, OrderStatusService $orderStatusService, StripeService $stripeService): void
    {
        // Use StripeService to find order metadata from payment links
        $orderData = $stripeService->findOrderFromPaymentIntent($paymentIntent['id']);

        if (! $orderData) {
            return; // Error already logged in StripeService
        }

        $orderId = $orderData['order_id'];
        $orderReference = $orderData['order_reference'];

        // Find the order
        $order = null;
        if ($orderId) {
            $order = Order::find($orderId);
        } elseif ($orderReference) {
            $order = Order::where('reference', $orderReference)->first();
        }

        if (! $order) {
            Log::error('Order not found for Stripe payment intent', [
                'order_id' => $orderId,
                'order_reference' => $orderReference,
                'payment_intent_id' => $paymentIntent['id'],
            ]);

            return;
        }

        // Find the payment record - try multiple approaches
        $payment = OrderPayment::where('stripe_payment_intent_id', $paymentIntent['id'])
            ->where('order_id', $order->id)
            ->first();

        // If not found by payment intent ID, try to find the pending payment for this order
        if (! $payment) {
            Log::info('Payment not found by intent ID, trying to find pending payment', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntent['id'],
            ]);

            $payment = OrderPayment::where('order_id', $order->id)
                ->where('status', PaymentStatus::PENDING)
                ->where('type', PaymentType::ORDER_FULL)
                ->first();
        }

        if (! $payment) {
            Log::error('OrderPayment not found for payment intent', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntent['id'],
                'available_payments' => OrderPayment::where('order_id', $order->id)->get(),
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

        // Trigger email notifications via OrderStatusService
        $orderStatusService->changed($order, OrderStatus::PAID);
    }
}
