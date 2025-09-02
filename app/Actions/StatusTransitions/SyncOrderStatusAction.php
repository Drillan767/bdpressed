<?php

namespace App\Actions\StatusTransitions;

use App\Enums\IllustrationStatus;
use App\Enums\OrderStatus;
use App\Models\Illustration;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Model;

class SyncOrderStatusAction extends BaseTransitionAction
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Illustration $illustration */
        $illustration = $model;
        $order = $illustration->order;

        match ($toState) {
            IllustrationStatus::DEPOSIT_PAID => $this->handleDepositPaid($illustration, $order),
            IllustrationStatus::COMPLETED => $this->handleIllustrationCompleted($illustration, $order),
            default => null,
        };
    }

    private function handleDepositPaid(Illustration $illustration, $order): void
    {
        // If this is an illustration-only order, sync the order status when work can begin
        if ($this->orderService->shouldSkipOrderPayment($order) && $order->status === OrderStatus::NEW) {
            $order->transitionTo(OrderStatus::IN_PROGRESS, [
                'triggered_by' => 'system',
                'reason' => 'Illustration deposit paid - work can begin',
            ]);
        }
    }

    private function handleIllustrationCompleted(Illustration $illustration, $order): void
    {
        // Check if this completes the entire order (for illustration-only orders)
        $this->orderService->handleIllustrationOrderCompletion($order);
    }
}
