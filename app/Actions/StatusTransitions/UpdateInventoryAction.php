<?php

namespace App\Actions\StatusTransitions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpdateInventoryAction extends BaseTransitionAction
{
    public function execute(Model $model, $fromState, $toState, array $context = []): void
    {
        if ($this->shouldSkipAction($context)) {
            return;
        }

        /** @var Order $order */
        $order = $model;

        // Only handle inventory for actual product orders (not illustration-only)
        if ($order->isIllustrationOnlyOrder()) {
            return;
        }

        // Reduce inventory when order is paid
        if ($toState === OrderStatus::PAID) {
            $this->reduceInventory($order);
        }

        // Restore inventory when order is cancelled from a paid state
        if ($toState === OrderStatus::CANCELLED && $this->wasPaidBefore($fromState)) {
            $this->restoreInventory($order);
        }
    }

    private function reduceInventory(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->details as $detail) {
                $product = Product::find($detail->product_id);

                if (! $product) {
                    continue;
                }

                // Simple reduction with 0 floor
                $newStock = max(0, $product->stock - $detail->quantity);
                $product->update(['stock' => $newStock]);
            }
        });
    }

    private function restoreInventory(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->details as $detail) {
                $product = Product::find($detail->product_id);

                if (! $product) {
                    continue;
                }

                $newStock = $product->stock + $detail->quantity;
                $product->stock = $newStock;
                $product->save();
            }
        });
    }

    private function wasPaidBefore($fromState): bool
    {
        return in_array($fromState, [
            OrderStatus::PAID,
            OrderStatus::TO_SHIP,
            OrderStatus::SHIPPED,
        ]);
    }
}
