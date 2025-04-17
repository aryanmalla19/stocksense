<?php

namespace App\Http\Requests\Sector;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|in:Banking,Hydropower,Life Insurance,Non-life Insurance,Health,Manufacturing,Hotel,Trading,Microfinance,Finance,Investment,Others|unique:sectors,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Sector name is required',
            'name.in' => 'Sector name must be in Predefined values',
            'name.unique' => 'Sector name must be unique',
        ];
    }
}