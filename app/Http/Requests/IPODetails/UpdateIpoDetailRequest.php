<?php

namespace App\Http\Requests\IPODetails;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIpoDetailRequest extends FormRequest
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
            'stock_id' => 'sometimes|integer',
            'issue_price' => 'sometimes|integer|min:100',
            'total_shares' => 'sometimes|integer|min:1000',
            'open_date' => 'sometimes|date',
            'close_date' => 'sometimes|date|after:open_date',
            'listing_date' => 'sometimes|date|after:close_date',
            'ipo_status' => 'sometimes|string|in:open,close,pending',
        ];
    }

    public function messages(): array
    {
        return [
            'open_date.required' => 'Open date is required.',
            'close_date.after' => 'Close date must be after open date.',
            'listing_date.after' => 'Listing date must be after close date.',
            'ipo_status.in' => 'IPO status must be one of: open, close, pending.',
        ];
    }
}
