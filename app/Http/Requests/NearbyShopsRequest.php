<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NearbyShopsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all users for now
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'postcode' => 'required|string',
            'max_distance' => 'required|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'postcode.required' => 'The postcode field is required.',
            'postcode.string' => 'The postcode must be a string.',
            'max_distance.required' => 'The max distance field is required.',
            'max_distance.numeric' => 'The max distance must be a number.',
            'max_distance.min' => 'The max distance must be at least 1.',
        ];
    }
}
