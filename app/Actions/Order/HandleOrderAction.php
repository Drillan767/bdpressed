<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Enums\OrderStatus;
use Illuminate\Support\Collection;

class HandleOrderAction
{
    public function handle(OrderRequest $request, bool $guest, int $authId, array $addressesInfos)
    {
        $productIds = array_column($request->get('products'), 'id');
        $products = Product::whereIn('id', $productIds)->get(['id', 'price', 'weight']);

        [
            'shipping' => $shippingId,
            'billing' => $billingId,
            'same' => $useSame,
        ] = $addressesInfos;

        $order = new Order();
        $order->total = $this->definePrice($products);
        $order->reference = $this->defineReference();
        // TODO: replace with request value.
        $order->additionalInfos = '';

        if ($guest) {
            $order->guest_id = $authId;
        } else {
            $order->auth_id = $authId;
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
            $orderDetail->price = $products->firstWhere('id', $product['id'])->price;

            $orderDetail->save();
        }
    }

    private function definePrice(Collection $products): float
    {
        $totalPrice = $products->sum($products, 'price');
        $totalWeight = $products->sum($products, 'weight');

        $fees = 0.015 * $totalPrice + 0.25;
        $shipFee = $totalWeight > 400 ? 7 : 4;

        return $totalPrice + $fees + $shipFee;
    }

    /**
     * Ensures reference is unique.
     */
    private function defineReference()
    {
        $orders = Order::all(['reference']);

        $reference = strtoupper(Str::random(8));

        while($orders->contains('reference', $reference)) {
            $reference = strtoupper(Str::random(8));
        }

        return $reference;
    }
}
