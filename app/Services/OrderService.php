<?php

namespace App\Services;

use App\Enums\IllustrationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;

readonly class OrderService
{
    public function __construct(
        private StripeService $stripeService,
        private MoneyService $moneyService,
    ) {}

    /**
     * Calculate fees - automatically uses actual fees if payment exists, otherwise estimates
     * Make sure to load 'payments' relationship before calling this method
     */
    public function calculateFees(Order $order): int
    {
        // Check if payments are loaded and we have actual payment data
        if ($order->relationLoaded('payments')) {
            $actualPayment = $order->payments->whereIn('status', [PaymentStatus::PAID, PaymentStatus::REFUNDED])->first();

            if ($actualPayment) {
                // Use actual fees from completed payment
                return $order->shipmentFees->cents() + $actualPayment->stripe_fee;
            }
        }

        // Fallback to estimates (for new orders or when payments not loaded)
        $estimatedStripeFee = $this->stripeService->calculateStripeFee($order->total->cents());

        // Use the order's existing shipmentFees instead of recalculating
        return $order->shipmentFees->cents() + $estimatedStripeFee;
    }

    /**
     * Calculate only the Stripe fees portion (for email notifications)
     * Returns a Money object for consistency with other fee calculations
     */
    public function calculateStripeFeesOnly(Order $order)
    {
        // Check if payments are loaded and we have actual payment data
        if ($order->relationLoaded('payments')) {
            $actualPayment = $order->payments->whereIn('status', [PaymentStatus::PAID, PaymentStatus::REFUNDED])->first();

            if ($actualPayment && $actualPayment->stripe_fee) {
                // Return actual Stripe fee as Money object
                return $this->moneyService->createMoneyObject($actualPayment->stripe_fee);
            }
        }

        // Fallback to estimated Stripe fees
        $estimatedStripeFee = $this->stripeService->calculateStripeFee($order->total->cents());

        return $this->moneyService->createMoneyObject($estimatedStripeFee);
    }

    /**
     * Get the final amount customer pays (or paid)
     */
    public function getFinalAmount(Order $order): int
    {
        return $order->total->cents() + $this->calculateFees($order);
    }

    /**
     * Check if an order should skip the normal payment flow because it only contains illustrations
     */
    public function shouldSkipOrderPayment(Order $order): bool
    {
        // Skip order payment if order only contains illustrations (no regular products)
        $hasRegularProducts = $order->details()->exists();

        return $order->illustrations()->exists() && ! $hasRegularProducts;
    }

    /**
     * Check if all illustrations in an order are completed
     */
    public function areAllIllustrationsCompleted(Order $order): bool
    {
        $totalIllustrations = $order->illustrations()->count();

        if ($totalIllustrations === 0) {
            return false;
        }

        $completedIllustrations = $order->illustrations()
            ->where('status', IllustrationStatus::COMPLETED)
            ->count();

        return $completedIllustrations === $totalIllustrations;
    }

    /**
     * Auto-complete illustration-only orders when all illustrations are done
     */
    public function handleIllustrationOrderCompletion(Order $order): void
    {
        if (! $this->shouldSkipOrderPayment($order)) {
            return;
        }

        if (! $this->areAllIllustrationsCompleted($order)) {
            return;
        }

        // Check if any illustrations need physical shipping
        $needsShipping = $order->illustrations()
            ->where('print', true)
            ->exists();

        // For illustration-only orders, we need to transition through proper states
        // since illustrations handle their own payments

        switch ($order->status) {
            case OrderStatus::NEW:
                // Edge case: illustration completed before deposit was paid
                // Transition to IN_PROGRESS first
                $order->transitionTo(OrderStatus::IN_PROGRESS, [
                    'triggered_by' => 'system',
                    'reason' => 'Illustration(s) terminée(s) - Transition en cours',
                ]);

                // Refresh the order to get the updated status
                $order = $order->fresh();

            case OrderStatus::IN_PROGRESS:
                // Transition to PAID (illustrations already handled payment)
                $order->transitionTo(OrderStatus::PAID, [
                    'triggered_by' => 'system',
                    'reason' => 'Illustration(s) terminée(s) - Paiement géré séparément',
                ]);

                // Refresh the order to get the updated status
                $order = $order->fresh();

            case OrderStatus::PAID:
                $order->transitionTo(OrderStatus::DONE, [
                    'triggered_by' => 'system',
                    'reason' => 'Illustration(s) terminée(s)',
                ]);
                break;
        }
    }
}
