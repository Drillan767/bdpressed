<?php

namespace App\Actions\Order;

use App\Enums\AddressType;
use App\Http\Requests\OrderRequest;
use App\Models\Address;

class HandleAddressesAction
{
    public function __construct(
        public bool $guest,
        public int $authId,
    ) {}

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
        $addresses = $request->get('addresses');
        $useSame = $addresses['same'];

        $shippingId = $this->handleShippingAddress($request, $addresses);
        $billingId = $useSame
            ? $this->handleSameAddress($shippingId)
            : $this->handleBillingAddress($request, $addresses);

        return [
            'shipping' => $shippingId,
            'billing' => $billingId,
            'same' => $useSame,
        ];
    }

    private function handleShippingAddress(OrderRequest $request, array $addresses): int
    {
        if (! $this->guest && isset($addresses['shipping_id'])) {
            return $addresses['shipping_id'];
        }

        return $this->storeAddress($request, AddressType::SHIPPING);
    }

    private function handleBillingAddress(OrderRequest $request, array $addresses): int
    {
        if (! $this->guest && isset($addresses['billing_id'])) {
            return $addresses['billing_id'];
        }

        return $this->storeAddress($request, AddressType::BILLING);
    }

    private function handleSameAddress(int $shippingId): int
    {
        $this->ensureDefaultBilling($shippingId);

        return $shippingId;
    }

    private function ensureDefaultBilling(int $addressId): void
    {
        if ($this->guest) {
            return;
        }

        $hasExistingDefaultBilling = Address::where([
            'user_id' => $this->authId,
            'default_billing' => true,
        ])->exists();

        if (! $hasExistingDefaultBilling) {
            Address::find($addressId)->update(['default_billing' => true]);
        }
    }

    private function storeAddress(
        OrderRequest $request,
        AddressType $type,
    ): int {
        $addressType = strtolower($type->name);
        $addresses = $request->get('addresses');
        $relationColumn = $this->guest ? 'guest_id' : 'user_id';

        $fields = array_intersect_key($addresses[$addressType], array_flip($this->fields));
        $fields["default_$addressType"] = false;

        if (! $this->guest) {
            $hasExistingDefault = Address::where([
                'user_id' => $this->authId,
                "default_$addressType" => true,
            ])->exists();

            if (! $hasExistingDefault) {
                $fields["default_$addressType"] = true;
            }
        }

        $fields[$relationColumn] = $this->authId;

        $address = Address::create($fields);

        return $address->id;
    }
}
