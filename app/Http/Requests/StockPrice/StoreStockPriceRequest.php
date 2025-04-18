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
            'current_price' => 'required|numeric|min:0',
            'open_price' => 'nullable|numeric|min:0',
            'close_price' => 'nullable|numeric|min:0',
            'high_price' => 'nullable|numeric|min:0',
            'low_price' => 'nullable|numeric|min:0',
            'volume' => 'nullable|integer|min:0',
            'date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'stock_id.required' => 'Stock ID is required.',
            'stock_id.exists' => 'The selected stock does not exist.',
            'current_price.required' => 'Current price is required.',
            'current_price.numeric' => 'Current price must be a numeric value.',
            'current_price.min' => 'Current price must be at least 0.',
            'open_price.numeric' => 'Open price must be a numeric value.',
            'open_price.min' => 'Open price must be at least 0.',
            'close_price.numeric' => 'Close price must be a numeric value.',
            'close_price.min' => 'Close price must be at least 0.',
            'high_price.numeric' => 'High price must be a numeric value.',
            'high_price.min' => 'High price must be at least 0.',
            'low_price.numeric' => 'Low price must be a numeric value.',
            'low_price.min' => 'Low price must be at least 0.',
            'volume.integer' => 'Volume must be an integer.',
            'volume.min' => 'Volume must be at least 0.',
            'date.date' => 'Date must be a valid date format.',
        ];
    }
}