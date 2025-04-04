<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectorRequest extends FormRequest
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
            'name' => 'required|in:banking,hydropower,life Insurance,non-life Insurance,health,manufacturing,hotel,trading,microfinance,finance,investment,others',

            //
        ];
    }

    public function messages():array
    {
        return [
            'name.required' => 'Sector name is required',
            'name.in'=> 'Sector name must be in Predefined values',

        ];
    }
}
