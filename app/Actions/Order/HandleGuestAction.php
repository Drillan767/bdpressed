<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use App\Models\Guest;

class HandleGuestAction
{
    public function handle(OrderRequest $request): int
    {
        $guest = Guest::create([
            'email' => $request->user->email,
            'order_id' => $request->order->id,
            'billing_address_id' => $request->addresses->billing ? $request->addresses->billing->id : null,
            'shipping_address_id' => $request->addresses->shipping->id,
        ]);

        return $guest->id;
    }
}