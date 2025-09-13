<?php

namespace App\Actions\StatusTransitions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\OrderCancellationNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendOrderCancellationAction extends BaseTransitionAction
{
    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Order $order */
        $order = $model;

        // Only send notification for transitions to CANCELLED status
        if ($toState !== OrderStatus::CANCELLED) {
            return;
        }

        // Require a reason for cancellation
        $reason = $context['reason'] ?? null;
        if (empty($reason)) {
            Log::warning('Order cancellation attempted without reason', [
                'order_id' => $order->id,
            ]);

            return;
        }

        $notification = new OrderCancellationNotification($order, $reason);

        // Send notification to the customer
        $customerNotified = false;
        if ($order->guest()->exists()) {
            $order->guest->notify($notification);
            $customerNotified = true;
        }

        if ($order->user()->exists()) {
            $order->user->notify($notification);
            $customerNotified = true;
        }
    }
}
