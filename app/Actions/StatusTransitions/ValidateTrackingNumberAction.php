<?php

namespace App\Actions\StatusTransitions;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ValidateTrackingNumberAction extends BaseTransitionAction
{
    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Order $order */
        $order = $model;

        // Only validate for transitions to SHIPPED status
        if ($toState !== OrderStatus::SHIPPED) {
            return;
        }

        // Get tracking number from context
        $trackingNumber = $context['tracking_number'] ?? null;

        if (empty($trackingNumber)) {
            Log::warning('Order transition to SHIPPED blocked - missing tracking number', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'from_status' => $fromState->value,
                'to_status' => $toState->value,
            ]);

            throw new InvalidArgumentException(
                "Tracking number is required to transition order #{$order->reference} to SHIPPED status"
            );
        }

        Log::info('Tracking number validation passed', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'tracking_number' => $trackingNumber,
        ]);
    }
}
