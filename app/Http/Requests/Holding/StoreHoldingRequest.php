<?php

namespace App\Http\Requests\Holding;

use Illuminate\Foundation\Http\FormRequest;

class StoreHoldingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // Require authentication
    }

    public function rules(): array
    {
        return [
            'portfolio_id' => ['required', 'exists:portfolios,id'],
            'stock_id' => ['required', 'exists:stocks,id'],
            'quantity' => ['required', 'numeric', 'min:1'],
            'average_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'portfolio_id.required' => 'The portfolio ID is required.',
            'portfolio_id.exists' => 'The selected portfolio does not exist.',
            'stock_id.required' => 'The stock ID is required.',
            'stock_id.exists' => 'The selected stock does not exist.',
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'The quantity must be at least 1.',
            'average_price.required' => 'The average price is required.',
            'average_price.min' => 'The average price cannot be negative.',
        ];
    }
}