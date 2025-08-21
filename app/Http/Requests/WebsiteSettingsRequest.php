<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebsiteSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comics_text' => ['required', 'string'],
            'comics_image_url' => ['nullable', 'image'],
            'shop_title' => ['required', 'string'],
            'shop_subtitle' => ['required', 'string'],
            'contact_image_url' => ['nullable', 'image'],
            'contact_text' => ['required', 'string'],
            'holiday_mode' => ['required', 'boolean'],
        ];
    }
}
