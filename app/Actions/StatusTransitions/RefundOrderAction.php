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
        if (!$order->requiresRefundOnCancellation()) {
            return;
        }

        // Get cancellation reason from context
        $reason = $context['reason'] ?? 'Order cancelled';

        // Process the refund
        $refundResult = $this->refundService->processOrderCancellationRefund($order, $reason);

        if (!$refundResult['success']) {
            throw new \Exception('Failed to process refunds for order cancellation: '.$refundResult['message']);
        }
    }
}
