<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class OrderRequest extends FormRequest
{
    private const array PRODUCT_RULES = [
        'products' => ['required', 'array', 'min:1'],
        'products.*.type' => ['required', 'string', 'in:item,illustration'],
        'products.*.quantity' => ['required_if:products.*.type,item', 'integer', 'min:1'],
    ];

    private const array ILLUSTRATION_RULES = [
        'products.*.illustrationDetails' => ['required_if:products.*.type,illustration', 'array'],
        'products.*.illustrationDetails.illustrationType' => ['required_if:products.*.type,illustration', 'string', 'in:bust,fl,animal'],
        'products.*.illustrationDetails.addedHuman' => ['required_if:products.*.type,illustration', 'integer', 'min:0'],
        'products.*.illustrationDetails.addedAnimal' => ['required_if:products.*.type,illustration', 'integer', 'min:0'],
        'products.*.illustrationDetails.pose' => ['required_if:products.*.type,illustration', 'string', 'in:simple,complex'],
        'products.*.illustrationDetails.background' => ['required_if:products.*.type,illustration', 'string', 'in:unified,gradient,simple,complex'],
        'products.*.illustrationDetails.print' => ['required_if:products.*.type,illustration', 'boolean'],
        'products.*.illustrationDetails.addTracking' => ['required_if:products.*.type,illustration', 'boolean'],
        'products.*.illustrationDetails.description' => ['required_if:products.*.type,illustration', 'string', 'max:1000'],
    ];

    private const array ADDRESS_RULES = [
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

    private const array SAVED_ADDRESS_RULES = [
        'addresses.shippingId' => ['required', 'integer', 'exists:addresses,id'],
        'addresses.billingId' => ['required', 'integer', 'exists:addresses,id'],
    ];

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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            ...self::PRODUCT_RULES,
            ...self::ILLUSTRATION_RULES,
            'products.*.id' => $this->getProductIdRules(),
        ];

        if ($this->user()) {
            // Authenticated user rules
            $rules['additionalInfos'] = ['nullable', 'string'];
            $rules = [
                ...$rules,
                ...$this->getAddressRules(),
            ];
        } else {
            // Guest/new user rules
            $rules = [
                ...$rules,
                ...$this->getUserRules(),
                ...self::ADDRESS_RULES,
            ];
        }

        return $rules;
    }

    /**
     * Get product ID validation rules
     */
    private function getProductIdRules(): array
    {
        return [
            'required_if:products.*.type,item',
            'integer',
            function ($attribute, $value, $fail) {
                // Get the product index from the attribute path
                preg_match('/products\.(\d+)\.id/', $attribute, $matches);
                $index = $matches[1] ?? 0;

                // Get the product type
                $productType = $this->input("products.{$index}.type");

                if ($productType === 'item') {
                    // For items, check if product exists and has stock
                    $product = DB::table('products')->where('id', $value)->first();
                    if (! $product) {
                        $fail('Le produit sélectionné est invalide.');

                        return;
                    }
                    if ($product->stock <= 0) {
                        $fail('Article en rupture de stock.');
                    }
                }
                // For illustrations, we don't validate the ID against the products table
            },
        ];
    }

    /**
     * Get user validation rules for guests/new users
     */
    private function getUserRules(): array
    {
        return [
            'user.email' => [
                'required',
                'email:rfc,dns',
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    if (DB::table('guests')->where('email', $value)->exists()) {
                        $fail('guest exists');
                    }
                },
            ],
            'user.guest' => ['required', 'boolean'],
            'user.additionalInfos' => ['nullable', 'string'],
            'user.password' => ['required_unless:user.guest,true', Password::defaults(), 'confirmed'],
            'user.password_confirmation' => ['required_unless:user.guest,true', 'string'],
        ];
    }

    /**
     * Get address validation rules for authenticated users
     */
    private function getAddressRules(): array
    {
        return $this->has('addresses.shippingId')
            ? self::SAVED_ADDRESS_RULES
            : self::ADDRESS_RULES;
    }

    public function messages(): array
    {
        return [
            'user.email.unique' => 'email exists',
            'guest.email.unique' => 'email exists',
        ];
    }
}
