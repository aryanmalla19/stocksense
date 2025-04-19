<?php

namespace App\Http\Requests\StockPrice;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust with auth logic if needed
    }

    public function rules(): array
    {
        return [
            'stock_id' => 'required|exists:stocks,id',
            'current_price' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'stock_id.required' => 'Stock ID is required.',
            'stock_id.exists' => 'The selected stock does not exist.',
            'current_price.required' => 'Price is required.',
            'current_price.numeric' => 'Price must be a numeric value.',
        ];
    }
}