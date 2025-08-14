<?php

namespace App\Services;

use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderPaymentLinkNotification;
use App\Notifications\PaymentConfirmationNotification;
use App\Notifications\AdminPaymentNotification;
use App\Models\Order;

class OrderStatusService
{
    public function changed(Order $order, OrderStatus $newStatus): void
    {
        // Handle specific status changes
        switch ($newStatus) {
            case OrderStatus::PENDING_PAYMENT:
                $this->handlePaymentPending($order, $newStatus);
                break;

            case OrderStatus::PAID:
                $this->handlePaymentCompleted($order, $newStatus);
                break;

            // Add other status change handlers as needed
            default:
                return;
        }

        $order->status = $newStatus;
        $order->save();
    }

    private function handlePaymentPending(Order $order, OrderStatus $newStatus): void
    {
        if (!$order->stripe_payment_link) {
            $service = new StripeService();
            $paymentLink = $service->createPaymentLink($order);

            if ($paymentLink) {
                $order->stripe_payment_link = $paymentLink;
            } else {
                Log::error('Failed to create payment link for order ' . $order->id);
                return;
            }
        }

        $order->status = $newStatus;
        $order->save();

        if ($order->guest()->exists()) {
            // Notification::sendNow(collect($order->guest->email), new OrderPaymentLinkNotification($order->stripe_payment_link));
            $order
                ->guest
                ->notify(
                    new OrderPaymentLinkNotification($order->stripe_payment_link)
                );
        }

        if ($order->user()->exists()) {
            $order
                ->user
                ->notify(
                    new OrderPaymentLinkNotification($order->stripe_payment_link)
                );
        }
    }

    private function handlePaymentCompleted(Order $order, OrderStatus $newStatus): void
    {
        // Update status first
        $order->status = $newStatus;
        $order->save();

        Log::info('Order payment completed', [
            'order_id' => $order->id,
            'order_reference' => $order->reference
        ]);

        // Send confirmation emails (will implement next)
        $this->sendPaymentConfirmationNotifications($order);
    }

    private function sendPaymentConfirmationNotifications(Order $order): void
    {
        // Send confirmation to the customer
        if ($order->guest()->exists()) {
            Notification::route('mail', $order->guest->email)
                ->notify(new PaymentConfirmationNotification($order));
        }

        if ($order->user()->exists()) {
            $order->user->notify(new PaymentConfirmationNotification($order));
        }

        // Send notification to admin(s)
        // You'll need to define who should receive admin notifications
        $adminEmails = config('app.admin_emails', []); // Configure in config/app.php

        if (!empty($adminEmails)) {
            Notification::route('mail', $adminEmails)
                ->notify(new AdminPaymentNotification($order));
        }

        // Alternative: notify all admin users (if you have a role system)
        // User::whereHas('roles', function($q) { $q->where('name', 'admin'); })
        //     ->each(fn($admin) => $admin->notify(new AdminPaymentNotification($order)));

        Log::info('Payment confirmation notifications sent', [
            'order_id' => $order->id,
            'customer_notified' => $order->guest()->exists() || $order->user()->exists(),
            'admin_notified' => !empty($adminEmails)
        ]);
    }
}
