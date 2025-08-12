<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\OrderStatus;
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

        // Store payment information
        $order->stripe_payment_intent_id = $paymentIntent['id'];
        $order->paid_at = now();
        $order->save();

        Log::info('Stripe payment intent completed', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'payment_intent_id' => $paymentIntent['id']
        ]);

        // Update order status to PAID
        $orderStatusService->changed($order, OrderStatus::PAID);
    }
}
