<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use App\Models\Address;
use App\Models\User;

class ClientAddressesAction
{
    public function handle(OrderRequest $request, User $user): void
    {
        $shipping = Address::create([ 
            'firstName' => $request->shippingAddress->firstName,
            'lastName' => $request->shippingAddress->lastName,
            'street' => $request->shippingAddress->street,
            'city' => $request->shippingAddress->city,
            'zipCode' => $request->shippingAddress->zipCode,
            'country' => $request->shippingAddress->country,
        ]);

        $user->addresses()->save($shipping);
    }
}