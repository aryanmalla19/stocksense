<?php

namespace App\Http\Requests\IPOAppllication;

use Illuminate\Foundation\Http\FormRequest;

class StoreIpoApplicationRequest extends FormRequest
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
            'user_id' => 'required|integer',
            'ipo_id' => 'required|integer',
            'applied_shares' => 'required|integer|min:10',
            'status' => 'required|string|in:open,close,pending',
            'applied_date' => 'required|date',
            'allotted_shares' => 'nullable|integer',
        ];
    }


    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required.',
            'user_id.integer' => 'User ID must be a valid integer.',

            'ipo_id.required' => 'IPO ID is required.',
            'ipo_id.integer' => 'IPO ID must be a valid integer.',

            'applied_shares.required' => 'You must specify how many shares you are applying for.',
            'applied_shares.integer' => 'Applied shares must be a number.',
            'applied_shares.min' => 'You must apply for at least :min shares.',

            'status.required' => 'Application status is required.',
            'status.in' => 'Status must be one of the following: open, close, or pending.',

            'applied_date.required' => 'The date of application is required.',
            'applied_date.date' => 'Applied date must be a valid date.',

            'allotted_shares.integer' => 'Allotted shares must be a number if provided.',
        ];
    }
}
