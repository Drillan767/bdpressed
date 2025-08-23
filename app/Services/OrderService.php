<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Settings\WebsiteSettings;

class OrderService
{
    public function __construct(
        private readonly WebsiteSettings $websiteSettings,
        private readonly StripeService $stripeService,
        private readonly MoneyService $moneyService,
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
        $totalWeight = $order->details->sum(fn ($detail) => $detail->product->weight * $detail->quantity);
        $totalWeight += $order->illustrations->count() * $this->websiteSettings->illustration_weight;
        $totalWeight += $this->websiteSettings->shipping_default_weight;

        $estimatedStripeFee = $this->stripeService->calculateStripeFee($order->total->cents());
        $shippingFee = $totalWeight > 400 ? 700 : 400; // 7€ or 4€

        return $estimatedStripeFee + $shippingFee;
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
}
