<?php

namespace App\Actions\StatusTransitions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SendOrderCancellationNotificationAction extends BaseTransitionAction
{
    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        // Debug: Action is being called
        logger('SendOrderCancellationNotificationAction called', [
            'model_id' => $model->id,
            'from' => $fromState?->value,
            'to' => $toState?->value,
        ]);

        if ($this->shouldSkipAction($context)) {
            logger('Action skipped due to context');
            return;
        }

        /** @var Order $order */
        $order = $model;

        // Only send notification for transitions to CANCELLED status
        if ($toState !== OrderStatus::CANCELLED) {
            logger('Action skipped: not transitioning to CANCELLED');
            return;
        }

        // Get cancellation reason from context
        $reason = $context['cancellation_reason'] ?? null;

        // Determine if a refund was processed
        $wasRefunded = $order->requiresRefundOnCancellation();

        Log::info('Sending order cancellation notification', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'reason' => $reason,
            'was_refunded' => $wasRefunded,
        ]);

        // Send notification to the customer
        $customer = $order->user ?? $order->guest;
        if ($customer) {
            logger('Sending notification to customer', ['customer_id' => $customer->id]);
            $customer->notify(new OrderCancelledNotification($order, $wasRefunded, $reason));
            logger('Notification sent successfully');
        } else {
            Log::warning('Cannot send cancellation notification: no customer found', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'has_user' => $order->user !== null,
                'has_guest' => $order->guest !== null,
            ]);
        }
    }
}