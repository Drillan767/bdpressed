<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Services\OrderStatusService;
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
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('Webhook error', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event['type']]);

        // Handle the event
        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object'], $orderStatusService);
                break;
            
            default:
                Log::info('Unhandled Stripe webhook event type: ' . $event['type']);
        }

        return response('Webhook handled', 200);
    }

    private function handleCheckoutSessionCompleted($session, OrderStatusService $orderStatusService): void
    {
        // Extract order information from metadata
        $orderId = $session['metadata']['order_id'] ?? null;
        $orderReference = $session['metadata']['order_reference'] ?? null;

        if (!$orderId && !$orderReference) {
            Log::error('No order information found in Stripe session metadata', [
                'session_id' => $session['id']
            ]);
            return;
        }

        // Find the order
        $order = null;
        if ($orderId) {
            $order = Order::find($orderId);
        } elseif ($orderReference) {
            $order = Order::where('reference', $orderReference)->first();
        }

        if (!$order) {
            Log::error('Order not found for Stripe payment', [
                'order_id' => $orderId,
                'order_reference' => $orderReference,
                'session_id' => $session['id']
            ]);
            return;
        }

        // Store payment information
        $order->stripe_payment_intent_id = $session['payment_intent'];
        $order->paid_at = now();
        
        Log::info('Processing payment completion for order', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'session_id' => $session['id']
        ]);

        // Update order status to PAID
        $orderStatusService->changed($order, OrderStatus::PAID);

        Log::info('Order payment completed successfully', [
            'order_id' => $order->id,
            'order_reference' => $order->reference
        ]);
    }
}