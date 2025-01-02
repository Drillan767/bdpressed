<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Enums\OrderStatus;

class HandleOrderAction
{
    public function handle(OrderRequest $request, bool $guest, int $authId, array $addressesInfos)
    {
        $productIds = array_column($request->get('products'), 'id');
        $products = Product::whereIn('id', $productIds)->get(['id', 'price']);

        $productsPrice = array_reduce(array_column($products->toArray(), 'price'), function ($carry, $item) {
            return $carry + $item;
        }, 0);

        [
            'shipping' => $shippingId,
            'billing' => $billingId,
            'same' => $useSame,
        ] = $addressesInfos;

        $order = new Order();
        $order->total = $productsPrice;
        $order->reference = strtoupper(Str::random(8));
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
}
