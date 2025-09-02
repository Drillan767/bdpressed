<?php

namespace App\Actions\StatusTransitions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\RefundService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RefundOrderAction extends BaseTransitionAction
{
    public function __construct(
        private RefundService $refundService
    ) {}

    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Order $order */
        $order = $model;

        // Only process refunds for transitions to CANCELLED status
        if ($toState !== OrderStatus::CANCELLED) {
            return;
        }

        // Check if this order requires refund based on its previous state
        if (! $order->requiresRefundOnCancellation()) {
            Log::info('Order cancellation does not require refund', [
                'order_id' => $order->id,
                'from_status' => $fromState?->value ?? 'unknown',
            ]);

            return;
        }

        // Get cancellation reason from context
        $reason = $context['reason'] ?? 'Order cancelled';

        Log::info('Processing automatic refund for cancelled order', [
            'order_id' => $order->id,
            'from_status' => $fromState?->value ?? 'unknown',
            'reason' => $reason,
        ]);

        // Process the refund
        $refundResult = $this->refundService->processOrderCancellationRefund($order, $reason);

        if ($refundResult['success']) {
            $refundCount = count($refundResult['refunds']);
            Log::info('Order refund processed successfully', [
                'order_id' => $order->id,
                'refunds_processed' => $refundCount,
                'message' => $refundResult['message'],
            ]);
        } else {
            Log::error('Order refund processing failed', [
                'order_id' => $order->id,
                'error' => $refundResult['message'],
                'refund_details' => $refundResult['refunds'],
            ]);

            // In a production system, you might want to:
            // - Send admin notification about failed refund
            // - Queue the refund for manual processing
            // - Revert the status change if refund is critical

            throw new \Exception('Failed to process refunds for order cancellation: '.$refundResult['message']);
        }
    }
}
