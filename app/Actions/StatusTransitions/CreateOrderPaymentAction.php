<?php

namespace App\Actions\StatusTransitions;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CreateOrderPaymentAction extends BaseTransitionAction
{
    public function __construct(
        private readonly StripeService $stripeService
    ) {}

    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Order $order */
        $order = $model;

        // Only create payment for NEW -> PENDING_PAYMENT transition
        if ($fromState !== OrderStatus::NEW || $toState !== OrderStatus::PENDING_PAYMENT) {
            return;
        }

        // Check if we already have a pending payment for this order
        $existingPayment = $order->payments()
            ->where('type', PaymentType::ORDER_FULL)
            ->where('status', PaymentStatus::PENDING)
            ->first();

        if ($existingPayment) {
            Log::info('Existing payment found for order', [
                'order_id' => $order->id,
                'payment_id' => $existingPayment->id,
            ]);

            return;
        }

        // Calculate final amount (order total + shipping)
        $finalAmount = $order->total->cents() + $order->shipmentFees->cents();

        // Calculate Stripe fee
        $stripeFee = $this->stripeService->calculateStripeFee($finalAmount);

        // Create OrderPayment record
        $payment = $order->payments()->create([
            'type' => PaymentType::ORDER_FULL,
            'status' => PaymentStatus::PENDING,
            'amount' => $finalAmount,
            'stripe_fee' => $stripeFee,
            'description' => 'Paiement pour la commande #'.$order->reference,
        ]);

        // Create Stripe payment link
        $paymentLink = $this->stripeService->createPaymentLink($order);

        if ($paymentLink) {
            $payment->update(['stripe_payment_link' => $paymentLink]);

            Log::info('Order payment created', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount' => $finalAmount,
            ]);
        } else {
            Log::error('Failed to create payment link for order', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
            ]);

            // Remove the payment record if Stripe link creation failed
            $payment->delete();
        }
    }
}
