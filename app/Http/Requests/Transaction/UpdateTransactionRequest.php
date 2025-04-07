<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
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
            'user_id' => 'sometimes|integer|exists:users,id',
            'stock_id' => 'sometimes|integer|exists:stocks,id',
            'type' => ['sometimes', Rule::in(['buy', 'sell', 'ipo_allotted'])],
            'quantity' => 'sometimes|integer|min:10',
            'price' => 'sometimes',
            'transaction_fee' => 'sometimes'
        ];
    }


    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'stock_id.exists' => 'The selected stock does not exist.',
            'type.in' => 'The transaction type must be one of: buy, sell, or ipo_allotted.',
            'quantity.min' => 'The quantity must be at least 10.',
        ];
    }
}
