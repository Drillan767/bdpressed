<?php

namespace App\Actions\StatusTransitions;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Order;
use App\Notifications\OrderPaymentLinkNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SendOrderPaymentLinkAction extends BaseTransitionAction
{
    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Order $order */
        $order = $model;

        // Only send notification for NEW -> PENDING_PAYMENT transition
        if ($fromState !== OrderStatus::NEW || $toState !== OrderStatus::PENDING_PAYMENT) {
            return;
        }

        // Find the pending payment
        $payment = $order->payments()
            ->where('type', PaymentType::ORDER_FULL)
            ->where('status', PaymentStatus::PENDING)
            ->whereNotNull('stripe_payment_link')
            ->first();

        if (! $payment || ! $payment->stripe_payment_link) {
            Log::warning('No payment link found to send notification', [
                'order_id' => $order->id,
            ]);

            return;
        }

        $notification = new OrderPaymentLinkNotification($payment->stripe_payment_link);

        if ($order->guest()->exists()) {
            $order->guest->notify($notification);
        }

        if ($order->user()->exists()) {
            $order->user->notify($notification);
        }

        Log::info('Order payment link notification sent', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'customer_notified' => $order->guest()->exists() || $order->user()->exists(),
        ]);
    }
}
