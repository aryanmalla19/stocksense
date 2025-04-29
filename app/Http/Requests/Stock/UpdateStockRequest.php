<?php

namespace App\Http\Requests\Stock;

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
            'symbol' => 'sometimes|string|max:6|regex:/^[A-Za-z]+$/|unique:stocks,symbol,'.$id,
            'company_name' => 'sometimes|string|max:255||regex:/^[A-Za-z\s\.]+$/|unique:stocks,company_name,'.$id,
            'sector_id' => 'sometimes|integer|exists:sectors,id',
            'description' => 'sometimes|string',
        ];
    }

    public function messages(): array
    {
        return [
            'symbol.max' => 'The symbol must not be more than 6 characters.',
            'symbol.regex' => 'The symbol must contain only letters and no spaces or numbers.',
            'symbol.unique' => 'This stock symbol is already registered.',

            'company_name.string' => 'The company name must be a valid string.',
            'company_name.max' => 'The company name must not exceed 255 characters.',
            'company_name.unique' => 'This company name is already registered.',

            'sector_id.integer' => 'Sector ID must be a valid number.',
            'sector_id.exists' => 'The selected sector does not exist.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('symbol')) {
            $this->merge([
                'symbol' => strtoupper($this->symbol),
            ]);
        }
    }
}
