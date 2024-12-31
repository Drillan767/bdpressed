<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return !$this->user() || !$this->user()->hasRole('user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $guestEmailRule = [
            'required',
            'email:rfc,dns',
        ];

        if (!$this->user()) {
            $guestEmailRule[] = 'unique:users,email';
        }

        return [
            'user.email' => $guestEmailRule,
            'user.guest' => ['required', 'boolean'],
            'user.password' => ['required_unless:user.guest,true', Password::defaults(), 'confirmed'],
            'user.password_confirmation' => ['required_unless:user.guest,true', 'string'],

            'products.*.id' => ['required', 'integer', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],

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
        ];
    }
}
