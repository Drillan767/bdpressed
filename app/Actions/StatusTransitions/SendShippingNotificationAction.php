<?php

namespace App\Actions\StatusTransitions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\ShippingNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendShippingNotificationAction extends BaseTransitionAction
{
    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Order $order */
        $order = $model;

        // Only send notification for transitions to SHIPPED status
        if ($toState !== OrderStatus::SHIPPED) {
            return;
        }

        // Get tracking number from context
        $trackingNumber = $context['tracking_number'] ?? '';

        if (empty($trackingNumber)) {
            Log::warning('Shipping notification not sent - missing tracking number', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
            ]);
            return;
        }

        Log::info('Order shipped', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'tracking_number' => $trackingNumber,
        ]);

        // Send notification to the customer
        $customerNotified = false;
        if ($order->guest) {
            $order->guest->notify(new ShippingNotification($order, $trackingNumber));
            $customerNotified = true;
        }

        if ($order->user) {
            $order->user->notify(new ShippingNotification($order, $trackingNumber));
            $customerNotified = true;
        }

        Log::info('Shipping notification sent', [
            'order_id' => $order->id,
            'customer_notified' => $customerNotified,
            'tracking_number' => $trackingNumber,
        ]);
    }
}