<?php

namespace App\Actions\StatusTransitions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\AdminPaymentNotification;
use App\Notifications\PaymentConfirmationNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendPaymentConfirmationAction extends BaseTransitionAction
{
    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Order $order */
        $order = $model;

        // Only send confirmation for transitions to PAID status
        if ($toState !== OrderStatus::PAID) {
            return;
        }

        Log::info('Order payment completed', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
        ]);

        // Send confirmation to the customer
        $customerNotified = false;
        if ($order->guest()->exists()) {
            Notification::route('mail', $order->guest->email)
                ->notify(new PaymentConfirmationNotification($order));
            $customerNotified = true;
        }

        if ($order->user()->exists()) {
            $order->user->notify(new PaymentConfirmationNotification($order));
            $customerNotified = true;
        }

        // Send notification to admin(s)
        $adminEmails = config('app.admin_emails', []);
        $adminNotified = false;

        if (! empty($adminEmails)) {
            Notification::route('mail', $adminEmails)
                ->notify(new AdminPaymentNotification($order));
            $adminNotified = true;
        }

        Log::info('Payment confirmation notifications sent', [
            'order_id' => $order->id,
            'customer_notified' => $customerNotified,
            'admin_notified' => $adminNotified,
        ]);
    }
}
