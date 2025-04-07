<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'user_id' => 'required|integer|exists:users,id',
            'stock_id' => 'required|integer|exists:stocks,id',
            'type' => ['required', Rule::in(['buy', 'sell', 'ipo_allotted'])],
            'quantity' => 'required|integer|min:10',
            'price' => 'required',
            'transaction_fee' => 'required'
        ];
    }


    public function messages(): array
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'stock_id.required' => 'The stock ID is required.',
            'stock_id.exists' => 'The selected stock does not exist.',
            'type.required' => 'The transaction type is required.',
            'type.in' => 'The transaction type must be one of: buy, sell, or ipo_allotted.',
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'The quantity must be at least 10.',
            'price.required' => 'The price is required.',
            'transaction_fee.required' => 'The transaction fee is required.',
        ];
    }
}
