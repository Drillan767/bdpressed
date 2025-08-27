<?php

namespace App\Actions\Order;

use App\Enums\AddressType;
use App\Http\Requests\OrderRequest;
use App\Models\Address;
use App\Models\User;

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
        /** @var int | null $shippingId */
        $shippingId = null;

        /** @var int | null $billingId */
        $billingId = null;

        $useSame = $request->get('addresses')['same'];
        $addresses = $request->get('addresses');

        // Handle shipping address
        if (! $this->guest && isset($addresses['shipping_id'])) {
            // User is logged in and selected existing shipping address
            $shippingId = $addresses['shipping_id'];
        } else {
            // User is guest or filled shipping fields - create new shipping address
            $shippingId = $this->storeAddress($request, AddressType::SHIPPING);
        }

        // Handle billing address
        if ($useSame) {
            // Use same address for billing (shipping address)
            $billingId = $shippingId;
        } elseif (! $this->guest && isset($addresses['billing_id'])) {
            // User is logged in and selected existing billing address
            $billingId = $addresses['billing_id'];
        } else {
            // User is guest or filled billing fields - create new billing address
            $billingId = $this->storeAddress($request, AddressType::BILLING);
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
    ): int {
        $addressType = strtolower($type->name);
        $addresses = $request->get('addresses');
        $relationColumn = $this->guest ? 'guest_id' : 'user_id';

        $fields = array_intersect_key($addresses[$addressType], array_flip($this->fields));
        $fields["default_$addressType"] = ! $this->guest;

        if (!$this->guest) {
            $hasExistingDefault = Address::where([
                'user_id' => $this->authId,
                "default_$addressType" => true,
            ])->exists();

            if (!$hasExistingDefault) {
                $fields["default_$addressType"] = true;
            }
        }

        $fields[$relationColumn] = $this->authId;

        $address = Address::create($fields);

        return $address->id;
    }
}
