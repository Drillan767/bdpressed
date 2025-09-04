<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return ! $this->user() || $this->user()->hasRole('user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // For authenticated users, we don't need user data in payload
        if ($this->user()) {
            return $this->getAuthenticatedUserRules();
        }

        // For guests/new users, include all the current validation
        return $this->getGuestUserRules();
    }

    /**
     * Get validation rules for authenticated users
     */
    private function getAuthenticatedUserRules(): array
    {
        return [
            // Products validation
            'products' => ['required', 'array', 'min:1'],
            'products.*.type' => ['required', 'string', 'in:item,illustration'],

            // Product ID and quantity only required for items (not illustrations)
            'products.*.id' => [
                'required_if:products.*.type,item',
                'integer',
                'exists:products,id',
                function ($attribute, $value, $fail) {
                    if (DB::table('products')->where('id', $value)->where('stock', '=', 0)->exists()) {
                        $fail('Article en rupture de stock.');
                    }
                }
            ],
            'products.*.quantity' => ['required_if:products.*.type,item', 'integer', 'min:1'],

            // Illustration details only required for illustrations
            'products.*.illustrationDetails' => ['required_if:products.*.type,illustration', 'array'],

            // Additional info (moved from user object)
            'additionalInfos' => ['nullable', 'string'],

            // Address validation only if not using saved addresses
            ...($this->has('addresses.shippingId') ? $this->getSavedAddressRules() : $this->getNewAddressRules()),
        ];
    }

    /**
     * Get validation rules for guest/new users
     */
    private function getGuestUserRules(): array
    {
        $unauthenticatedRules = [
            'user.password' => ['required_unless:user.guest,true', Password::defaults(), 'confirmed'],
            'user.password_confirmation' => ['required_unless:user.guest,true', 'string'],
        ];

        $guestEmailRule = [
            'required',
            'email:rfc,dns',
            'unique:users,email',
            function ($attribute, $value, $fail) {
                if (DB::table('guests')->where('email', $value)->exists()) {
                    $fail('guest exists');
                }
            },
        ];

        return [
            // User validation for guests/new users
            'user.email' => $guestEmailRule,
            'user.guest' => ['required', 'boolean'],
            'user.additionalInfos' => ['nullable', 'string'],
            ...$unauthenticatedRules,

            // Products validation
            'products' => ['required', 'array', 'min:1'],
            'products.*.type' => ['required', 'string', 'in:item,illustration'],

            // Product ID and quantity only required for items (not illustrations)
            'products.*.id' => [
                'required_if:products.*.type,item',
                'integer',
                'exists:products,id',
            ],
            'products.*.quantity' => ['required_if:products.*.type,item', 'integer', 'min:1'],

            // Illustration details only required for illustrations
            'products.*.illustrationDetails' => ['required_if:products.*.type,illustration', 'array'],

            // Address validation (always new addresses for guests)
            ...$this->getNewAddressRules(),
        ];
    }

    /**
     * Get validation rules for using saved addresses
     */
    private function getSavedAddressRules(): array
    {
        return [
            'addresses.shippingId' => ['required', 'integer', 'exists:addresses,id'],
            'addresses.billingId' => ['required', 'integer', 'exists:addresses,id'],
        ];
    }

    /**
     * Get validation rules for new addresses
     */
    private function getNewAddressRules(): array
    {
        return [
            'addresses.shipping.firstName' => ['required', 'string'],
            'addresses.shipping.lastName' => ['required', 'string'],
            'addresses.shipping.street' => ['required', 'string'],
            'addresses.shipping.city' => ['required', 'string'],
            'addresses.shipping.zipCode' => ['required', 'string'],
            'addresses.shipping.country' => ['required', 'string'],

            'addresses.same' => ['required', 'boolean'],

            'addresses.billing.firstName' => ['required_unless:addresses.same,true', 'string'],
            'addresses.billing.lastName' => ['required_unless:addresses.same,true', 'string'],
            'addresses.billing.street' => ['required_unless:addresses.same,true', 'string'],
            'addresses.billing.city' => ['required_unless:addresses.same,true', 'string'],
            'addresses.billing.zipCode' => ['required_unless:addresses.same,true', 'string'],
            'addresses.billing.country' => ['required_unless:addresses.same,true', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user.email.unique' => 'email exists',
            'guest.email.unique' => 'email exists',
        ];
    }
}
