<?php

namespace App\Http\Requests\Sector;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sectorId = $this->route('sector');

        return [
            'name' => [
                'required',
                'in:Banking,Hydropower,Life Insurance,Non-life Insurance,Health,Manufacturing,Hotel,Trading,Microfinance,Finance,Investment,Others',
                'unique:sectors,name,'.$sectorId,
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Sector name is required.',
            'name.in' => 'The sector name must be one of the predefined values.',
            'name.unique' => 'The sector name must be unique.',
        ];
    }
}
