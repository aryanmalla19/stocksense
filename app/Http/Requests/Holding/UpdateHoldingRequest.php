<?php

namespace App\Http\Requests\Holding;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHoldingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    public function rules(): array
    {
        return [
            'average_price' => 'sometimes|numeric',
            'quantity' => 'sometimes|numeric|min:1',
            'price' => 'sometimes|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'The quantity must be at least 1.',
            'price.min' => 'The price cannot be negative.',
        ];
    }
}