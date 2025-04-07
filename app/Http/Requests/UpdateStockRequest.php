<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('stock'); // Gets the ID from the route

        return [
            'symbol' => 'sometimes|string|max:6|unique:stocks,symbol,'.$id,
            'name' => 'sometimes|string',
            'sector_id' => 'sometimes|integer|exists:sectors,id',
        ];
    }

    public function messages(): array
    {
        return [
            'symbol.required' => 'The stock symbol is required.',
            'symbol.max' => 'The symbol must not be more than 6 characters.',
            'symbol.unique' => 'This stock symbol is already registered.',
            'name.required' => 'The company name is required.',
            'sector_id.required' => 'You must select a sector.',
            'sector_id.exists' => 'The selected sector does not exist.',
        ];
    }
}
