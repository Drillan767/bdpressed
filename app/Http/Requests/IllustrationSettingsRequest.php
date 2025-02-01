<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IllustrationSettingsRequest extends FormRequest
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
            'bust_base' => ['required'],
            'bust_add_human' => ['required'],
            'bust_add_animal' => ['required'],
            'fl_base' => ['required'],
            'fl_add_human' => ['required'],
            'fl_add_animal' => ['required'],
            'animal_base' => ['required'],
            'annimal_add_one' => ['required'],
            'animal_toy' => ['required'],
            'option_pose_simple' => ['required'],
            'option_pose_complex' => ['required'],
            'option_bg_gradient' => ['required'],
            'option_bg_simple' => ['required'],
            'option_bg_complex' => ['required'],
            'options_print' => ['required'],
            'options_add_tracking' => ['required'],
        ];
    }
}
