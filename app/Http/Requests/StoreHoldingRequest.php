<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHoldingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    public function rules(): array
    {
        return [
            'average_price' => 'required|numeric',
            'quantity' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'average_price.required' => 'The average price is required.',
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'The quantity must be at least 1.',
            'price.required' => 'The price is required.',
            'price.min' => 'The price cannot be negative.',
        ];
    }
}