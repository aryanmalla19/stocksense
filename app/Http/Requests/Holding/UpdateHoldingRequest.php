<?php

namespace App\Http\Requests\Holding;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHoldingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // Require authentication
    }

    public function rules(): array
    {
        return [
            'quantity' => ['sometimes', 'numeric', 'min:1'],
            'average_price' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'The quantity must be at least 1.',
            'average_price.min' => 'The average price cannot be negative.',
        ];
    }
}