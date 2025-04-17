<?php

namespace App\Http\Requests\Sector;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sectorId = $this->route('sector');
        Log::info('Sector ID for unique validation: ' . $sectorId); // Debugging

        return [
            'name' => [
                'required',
                'in:Banking,Hydropower,Life Insurance,Non-life Insurance,Health,Manufacturing,Hotel,Trading,Microfinance,Finance,Investment,Others',
                'unique:sectors,name,' . $sectorId,
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