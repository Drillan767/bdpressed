<?php

namespace App\Services;

use App\Enums\IllustrationStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Illustration;
use App\Models\OrderPayment;
use App\Notifications\IllustrationCompletedNotification;
use App\Notifications\IllustrationDepositLinkNotification;
use App\Notifications\IllustrationDepositPaidNotification;
use App\Notifications\IllustrationFinalLinkNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

readonly class IllustrationStatusService
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function changed(Illustration $illustration, IllustrationStatus $newStatus): void
    {
        // Handle specific status changes
        switch ($newStatus) {
            case IllustrationStatus::DEPOSIT_PENDING:
                $this->handleDepositPending($illustration);
                break;

            case IllustrationStatus::DEPOSIT_PAID:
                $this->handleDepositPaid($illustration);
                break;

            case IllustrationStatus::PAYMENT_PENDING:
                $this->handleFinalPaymentPending($illustration);
                break;

            case IllustrationStatus::COMPLETED:
                $this->handleCompleted($illustration);
                break;

            default:
                return;
        }
    }

    private function handleDepositPending(Illustration $illustration): void
    {
        // Find the deposit payment record
        /** @var OrderPayment $depositPayment */
        $depositPayment = $illustration->payments()
            ->where('type', PaymentType::ILLUSTRATION_DEPOSIT)
            ->where('status', PaymentStatus::PENDING)
            ->first();

        if (!$depositPayment || ! $depositPayment->stripe_payment_link) {
            Log::error('No deposit payment or payment link found for illustration', [
                'illustration_id' => $illustration->id,
            ]);

            return;
        }

        $this->sendNotificationToCustomer($illustration, new IllustrationDepositLinkNotification($illustration, $depositPayment));
    }

    private function handleDepositPaid(Illustration $illustration): void
    {
        Log::info('Illustration deposit payment completed', [
            'illustration_id' => $illustration->id,
            'order_id' => $illustration->order_id,
        ]);

        $this->sendNotificationToCustomer($illustration, new IllustrationDepositPaidNotification($illustration));

        // If this is an illustration-only order, sync the order status when work can begin
        $order = $illustration->order;
        if ($this->orderService->shouldSkipOrderPayment($order) && $order->status === \App\Enums\OrderStatus::NEW) {
            $order->transitionTo(\App\Enums\OrderStatus::IN_PROGRESS, [
                'triggered_by' => 'system',
                'reason' => 'Illustration deposit paid - work can begin',
            ]);
        }
    }

    private function handleFinalPaymentPending(Illustration $illustration): void
    {
        // Find the final payment record
        /** @var OrderPayment $finalPayment */
        $finalPayment = $illustration->payments()
            ->where('type', PaymentType::ILLUSTRATION_FINAL)
            ->where('status', PaymentStatus::PENDING)
            ->first();

        if (! $finalPayment || ! $finalPayment->stripe_payment_link) {
            Log::error('No final payment or payment link found for illustration', [
                'illustration_id' => $illustration->id,
            ]);

            return;
        }

        $this->sendNotificationToCustomer($illustration, new IllustrationFinalLinkNotification($illustration, $finalPayment));
    }

    private function handleCompleted(Illustration $illustration): void
    {
        Log::info('Illustration completed', [
            'illustration_id' => $illustration->id,
            'order_id' => $illustration->order_id,
        ]);

        $this->sendNotificationToCustomer($illustration, new IllustrationCompletedNotification($illustration));

        // Check if this completes the entire order (for illustration-only orders)
        $order = $illustration->order;
        $this->orderService->handleIllustrationOrderCompletion($order);
    }

    private function sendNotificationToCustomer(Illustration $illustration, $notification): void
    {
        $order = $illustration->order;

        if ($order->guest()->exists()) {
            Notification::route('mail', $order->guest->email)
                ->notify($notification);
        }

        if ($order->user()->exists()) {
            $order->user->notify($notification);
        }

        Log::info('Illustration notification sent', [
            'illustration_id' => $illustration->id,
            'notification_type' => get_class($notification),
            'customer_notified' => $order->guest()->exists() || $order->user()->exists(),
        ]);
    }
}
