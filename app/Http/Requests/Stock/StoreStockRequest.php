<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'symbol' => 'required|string|max:6|unique:stocks,symbol|regex:/^[A-Za-z]+$/',
            'company_name' => 'required|string|unique:stocks,company_name',
            'sector_id' => 'required|integer|exists:sectors,id',
        ];
    }

    public function messages(): array
    {
        return [
            'symbol.required' => 'The stock symbol is required.',
            'symbol.max' => 'The symbol must not be more than 6 characters.',
            'symbol.unique' => 'This stock symbol is already registered.',
            'symbol.regex' => 'The symbol must contain only letters and no spaces or numbers.',

            'company_name.required' => 'The company name is required.',
            'company.unique' => 'This stock company name is already registered.',

            '' => 'This stock symbol is already registered.',
            'sector_id.required' => 'You must select a sector.',
            'sector_id.exists' => 'The selected sector does not exist.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'symbol' => strtoupper($this->symbol),
        ]);
    }
}
