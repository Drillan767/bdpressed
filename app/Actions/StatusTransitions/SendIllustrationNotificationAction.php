<?php

namespace App\Actions\StatusTransitions;

use App\Enums\IllustrationStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Illustration;
use App\Models\OrderPayment;
use App\Notifications\IllustrationCompletedNotification;
use App\Notifications\IllustrationDepositLinkNotification;
use App\Notifications\IllustrationDepositPaidNotification;
use App\Notifications\IllustrationFinalLinkNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendIllustrationNotificationAction extends BaseTransitionAction
{
    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Illustration $illustration */
        $illustration = $model;

        match ($toState) {
            IllustrationStatus::DEPOSIT_PENDING => $this->sendDepositLinkNotification($illustration),
            IllustrationStatus::DEPOSIT_PAID => $this->sendDepositPaidNotification($illustration),
            IllustrationStatus::PAYMENT_PENDING => $this->sendFinalPaymentLinkNotification($illustration),
            IllustrationStatus::COMPLETED => $this->sendCompletedNotification($illustration),
            default => null,
        };
    }

    private function sendDepositLinkNotification(Illustration $illustration): void
    {
        /** @var OrderPayment $depositPayment */
        $depositPayment = $illustration->payments()
            ->where('type', PaymentType::ILLUSTRATION_DEPOSIT)
            ->where('status', PaymentStatus::PENDING)
            ->first();

        if (! $depositPayment || ! $depositPayment->stripe_payment_link) {
            Log::error('No deposit payment or payment link found for illustration', [
                'illustration_id' => $illustration->id,
            ]);

            return;
        }

        $notification = new IllustrationDepositLinkNotification($illustration, $depositPayment);
        $this->sendNotificationToCustomer($illustration, $notification);
    }

    private function sendDepositPaidNotification(Illustration $illustration): void
    {
        Log::info('Illustration deposit payment completed', [
            'illustration_id' => $illustration->id,
            'order_id' => $illustration->order_id,
        ]);

        $notification = new IllustrationDepositPaidNotification($illustration);
        $this->sendNotificationToCustomer($illustration, $notification);
    }

    private function sendFinalPaymentLinkNotification(Illustration $illustration): void
    {
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

        $notification = new IllustrationFinalLinkNotification($illustration, $finalPayment);
        $this->sendNotificationToCustomer($illustration, $notification);
    }

    private function sendCompletedNotification(Illustration $illustration): void
    {
        Log::info('Illustration completed', [
            'illustration_id' => $illustration->id,
            'order_id' => $illustration->order_id,
        ]);

        $notification = new IllustrationCompletedNotification($illustration);
        $this->sendNotificationToCustomer($illustration, $notification);
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
