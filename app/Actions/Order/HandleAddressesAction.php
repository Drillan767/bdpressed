<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderRequest;
use App\Models\Address;
use App\Enums\AddressType;
use App\Models\User;

class HandleAddressesAction
{
    public function __construct(
        public bool $guest,
        public int $authId,
    )
    {}

    private array $fields = [
        'firstName',
        'lastName',
        'street',
        'street2',
        'city',
        'zipCode',
        'country',
    ];

    public function handle(OrderRequest $request): array
    {
        /** @var int | null $shippingId */
        $shippingId = null;

        /** @var int | null $billingId */
        $billingId = null;

        $useSame = $request->get('addresses')['same'];

        if ($this->guest) {
            $shippingId = $this->storeAddress($request, AddressType::SHIPPING);

            if (!$useSame) {
                $billingId = $this->storeAddress($request, AddressType::BILLING);
            }
        }

        // TODO: Implement case where user is authenticated
        else {
            if ($request->has('shipping_id')) {
                $shippingId = $request->get('shipping_id');
            } else {
                $shippingId = $this->storeAddress($request, AddressType::SHIPPING);
            }

            if ($request->has('billing_id')) {
                $billingId = $request->get('billing_id');
            } else {
                $billingId = $this->storeAddress($request, AddressType::BILLING);
            }
        }

        return [
            'shipping' => $shippingId,
            'billing' => $billingId,
            'same' => $useSame,
        ];
    }

    private function storeAddress(
        OrderRequest $request,
        AddressType $type,
    ): int
    {
        $addressType = strtolower($type->name);
        $addresses = $request->get('addresses');
        $relationColumn = $this->guest ? "guest_{$addressType}_id" : "user_{$addressType}_id";

        $fields = array_intersect_key($addresses[$addressType], array_flip($this->fields));
        $fields['type'] = $type;
        $fields['default'] = !$this->guest;
        $fields[$relationColumn] = $this->authId;

        $address = Address::create($fields);

        return $address->id;
    }
}
