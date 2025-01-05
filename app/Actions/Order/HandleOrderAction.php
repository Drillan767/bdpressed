<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderStatus;
use Illuminate\Support\Collection;

class HandleOrderAction
{
    public function handle(OrderRequest $request, bool $guest, int $authId, array $addressesInfos): void
    {
        $productIds = array_column($request->get('products'), 'id');
        $products = Product::whereIn('id', $productIds)->get(['id', 'price', 'weight']);

        [
            'shipping' => $shippingId,
            'billing' => $billingId,
            'same' => $useSame,
        ] = $addressesInfos;

        [$totalPrice, $fees, $shipFee] = $this->definePrice($products, $request->get('products'));

        $order = new Order();
        $order->total = Number::abbreviate($totalPrice, 2);
        $order->stripeFees = Number::abbreviate($fees, 2);
        $order->shipmentFees = Number::abbreviate($shipFee, 2);
        $order->reference = $this->defineReference();
        $order->additionalInfos = $request->get('user')['additionalInfos'];

        if ($guest) {
            $order->guest_id = $authId;
        } else {
            $order->user_id = $authId;
        }

        $order->shipping_address_id = $shippingId;
        $order->billing_address_id = $billingId ?? $shippingId;
        $order->useSameAddress = $useSame;
        $order->status = OrderStatus::NEW;

        $order->save();

        foreach ($request->get('products') as $product) {
            $orderDetail = new OrderDetail();
            $orderDetail->order_id = $order->id;
            $orderDetail->product_id = $product['id'];
            $orderDetail->quantity = $product['quantity'];
            $orderDetail->price = $products->firstWhere('id', $product['id'])->price * $product['quantity'];

            $orderDetail->save();

            DB::table('products')->decrement('stock', $product['quantity']);
        }
    }

    private function definePrice(Collection $products, array $referenceProducts): array
    {
        $totalPrice = 0;
        $totalWeight = 0;

        foreach ($referenceProducts as $refProduct) {
            $product = $products->firstWhere('id', $refProduct['id']);
            if ($product) {
                $quantity = $refProduct['quantity'];
                $totalPrice += $product->price * $quantity;
                $totalWeight += $product->weight * $quantity;
            }
        }

        $fees = 0.015 * $totalPrice + 0.25;
        $shipFee = $totalWeight > 400 ? 7 : 4;

        return [
            $totalPrice + $fees + $shipFee,
            $fees,
            $shipFee,
        ];
    }

    /**
     * Ensures reference is unique.
     */
    private function defineReference(): string
    {
        $orders = Order::all(['reference']);

        $reference = strtoupper(Str::random(8));

        while($orders->contains('reference', $reference)) {
            $reference = strtoupper(Str::random(8));
        }

        return $reference;
    }
}
