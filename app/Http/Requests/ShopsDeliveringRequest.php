<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopsDeliveringRequest extends FormRequest
{
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
        ];
    }

    public function messages(): array
    {
        return [
            'postcode.required' => 'The postcode field is required.',
            'postcode.string' => 'The postcode must be a string.',
        ];
    }
}
