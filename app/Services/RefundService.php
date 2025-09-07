<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    /**
     * Process refund for an order cancellation
     */
    public function processOrderCancellationRefund(Order $order, string $reason): array
    {
        if (! $order->requiresRefundOnCancellation()) {
            return [
                'success' => true,
                'message' => 'No refund required for this order state',
                'refunds' => [],
            ];
        }

        $results = [];
        $allSuccessful = true;

        DB::transaction(function () use ($order, $reason, &$results, &$allSuccessful) {
            // Get all paid payments for this order
            $paidPayments = $order->payments()
                ->where('status', PaymentStatus::PAID)
                ->whereNotNull('stripe_payment_intent_id')
                ->get();

            foreach ($paidPayments as $payment) {
                $refundResult = $this->refundPayment($payment, $payment->amount->cents(), $reason);
                $results[] = $refundResult;

                if (! $refundResult['success']) {
                    $allSuccessful = false;
                }
            }
        });

        return [
            'success' => $allSuccessful,
            'message' => $allSuccessful ? 'All payments refunded successfully' : 'Some refunds failed',
            'refunds' => $results,
        ];
    }

    /**
     * Process refund for an individual payment
     */
    public function refundPayment(OrderPayment $payment, int $amount, string $reason): array
    {
        if (! $payment->stripe_payment_intent_id) {
            return [
                'success' => false,
                'error' => 'No Stripe payment intent ID found',
                'payment_id' => $payment->id,
            ];
        }

        if ($payment->isFullyRefunded()) {
            return [
                'success' => false,
                'error' => 'Payment is already fully refunded',
                'payment_id' => $payment->id,
            ];
        }

        // Ensure we don't refund more than the original amount
        $remainingAmount = $payment->amount->cents() - ($payment->refunded_amount?->cents() ?? 0);
        $refundAmount = min($amount, $remainingAmount);

        if ($refundAmount <= 0) {
            return [
                'success' => false,
                'error' => 'No amount available to refund',
                'payment_id' => $payment->id,
            ];
        }

        // Process refund with Stripe
        $stripeResult = $this->stripeService->refundPayment(
            $payment->stripe_payment_intent_id,
            $refundAmount,
            $reason
        );

        if (! $stripeResult['success']) {
            return [
                'success' => false,
                'error' => $stripeResult['error'],
                'payment_id' => $payment->id,
            ];
        }

        // Update OrderPayment record
        $currentRefundedAmount = $payment->refunded_amount?->cents() ?? 0;
        $newRefundedAmount = $currentRefundedAmount + $refundAmount;

        $payment->update([
            'refunded_amount' => $newRefundedAmount,
            'refunded_at' => now(),
            'refund_reason' => $reason,
            'status' => $newRefundedAmount >= $payment->amount->cents()
                ? PaymentStatus::REFUNDED
                : PaymentStatus::PARTIALLY_REFUNDED,
        ]);

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'stripe_refund_id' => $stripeResult['refund_id'],
            'amount' => $refundAmount,
            'is_full_refund' => $newRefundedAmount >= $payment->amount->cents(),
        ];
    }

    /**
     * Calculate refund amount for illustration cancellation based on state
     */
    public function calculateIllustrationRefundAmount(OrderPayment $payment, string $illustrationStatus): int
    {
        $originalAmount = $payment->amount->cents();

        return match ($illustrationStatus) {
            // Before work starts - full refund
            'PENDING', 'DEPOSIT_PENDING', 'DEPOSIT_PAID' => $originalAmount,

            // Work in progress - artist discretion, but suggest deposit kept for time invested
            'IN_PROGRESS' => $payment->type->value === 'illustration_deposit'
                ? 0  // Keep deposit
                : $originalAmount, // Refund final payment if paid

            // Client review phase - artist discretion
            'CLIENT_REVIEW' => 0, // Suggest no refund as work is completed

            // Point of no return - no refunds
            'PAYMENT_PENDING', 'COMPLETED' => 0,

            default => 0,
        };
    }

    /**
     * Get refund summary for an order
     */
    public function getOrderRefundSummary(Order $order): array
    {
        $payments = $order->payments()->get();
        $totalPaid = 0;
        $totalRefunded = 0;
        $refundableAmount = 0;

        foreach ($payments as $payment) {
            if (in_array($payment->status, [PaymentStatus::PAID, PaymentStatus::PARTIALLY_REFUNDED, PaymentStatus::REFUNDED])) {
                $totalPaid += $payment->amount->cents();
                $totalRefunded += $payment->refunded_amount?->cents() ?? 0;

                if ($order->requiresRefundOnCancellation() && ! $payment->isFullyRefunded()) {
                    $refundableAmount += $payment->amount->cents() - ($payment->refunded_amount?->cents() ?? 0);
                }
            }
        }

        return [
            'total_paid' => $totalPaid,
            'total_refunded' => $totalRefunded,
            'refundable_amount' => $refundableAmount,
            'can_be_refunded' => $refundableAmount > 0 && $order->requiresRefundOnCancellation(),
        ];
    }
}
