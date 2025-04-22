<?php

namespace App\Http\Requests\StockPrice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreStockPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stock_id' => 'required|exists:stocks,id',
            'current_price' => 'required|numeric',
            'open_price' => 'nullable|numeric',
            'close_price' => 'nullable|numeric',
            'high_price' => 'nullable|numeric',
            'low_price' => 'nullable|numeric',
            'volume' => 'nullable|integer',
            'date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'stock_id.required' => 'Stock ID is required.',
            'stock_id.exists' => 'The selected stock does not exist.',
            'current_price.required' => 'Price is required.',
            'current_price.numeric' => 'Current price must be a numeric value.',
            'open_price.numeric' => 'Open price must be a numeric value.',
            'close_price.numeric' => 'Close price must be a numeric value.',
            'high_price.numeric' => 'High price must be a numeric value.',
            'low_price.numeric' => 'Low price must be a numeric value.',
            'volume.integer' => 'Volume must be an integer.',
            'date.date' => 'Date must be a valid date.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}