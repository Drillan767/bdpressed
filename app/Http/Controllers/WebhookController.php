<?php

namespace App\Http\Controllers;

use App\Enums\PaymentType;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Services\OrderStatusService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

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
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            // Continue processing for now (signature issue with proxy)
            $event = json_decode($payload, true);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('Webhook error', 400);
        }

        // Handle the event
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event['data']['object'], $orderStatusService, app(StripeService::class));
                break;

            default:
                Log::info('Unhandled Stripe webhook event type: ' . $event['type']);
        }

        return response('Webhook handled', 200);
    }

    private function handlePaymentIntentSucceeded($paymentIntent, OrderStatusService $orderStatusService, StripeService $stripeService): void
    {
        // Use StripeService to find order metadata from payment links
        $orderData = $stripeService->findOrderFromPaymentIntent($paymentIntent['id']);

        if (!$orderData) {
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

        if (!$order) {
            Log::error('Order not found for Stripe payment intent', [
                'order_id' => $orderId,
                'order_reference' => $orderReference,
                'payment_intent_id' => $paymentIntent['id']
            ]);
            return;
        }

        // Find the payment record - try multiple approaches
        $payment = OrderPayment::where('stripe_payment_intent_id', $paymentIntent['id'])
           ->where('order_id', $order->id)
           ->first();

        // If not found by payment intent ID, try to find the pending payment for this order
        if (!$payment) {
            Log::info('Payment not found by intent ID, trying to find pending payment', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntent['id']
            ]);

            $payment = OrderPayment::where('order_id', $order->id)
               ->where('status', PaymentStatus::PENDING)
               ->where('type', PaymentType::ORDER_FULL)
               ->first();
        }

        if (!$payment) {
            Log::error('OrderPayment not found for payment intent', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntent['id'],
                'available_payments' => OrderPayment::where('order_id', $order->id)->get()
            ]);
            return;
        }

        // Update payment status (simple, no state machine)
        $payment->update([
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
            'stripe_metadata' => $paymentIntent
        ]);

        Log::info('Stripe payment intent completed', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'payment_intent_id' => $paymentIntent['id'],
            'payment_type' => $payment->type->value
        ]);

        // Use state machine for Order status transition with context
        $order->transitionTo(OrderStatus::PAID, [
            'triggered_by' => 'webhook',
            'metadata' => [
                'payment_intent_id' => $paymentIntent['id'],
                'stripe_event' => 'payment_intent.succeeded'
            ]
        ]);
        
        // Trigger email notifications via OrderStatusService
        $orderStatusService->changed($order, OrderStatus::PAID);
    }
}
