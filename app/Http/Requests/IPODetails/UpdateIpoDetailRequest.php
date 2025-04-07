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
            'stock_id' => 'required|integer',
            'issue_price' => 'required|integer|min:100',
            'total_shares' => 'required|integer|min:1000',
            'open_date' => 'required|date',
            'close_date' => 'required|date|after:open_date',
            'listing_date' => 'required|date|after:close_date',
            'ipo_status' => 'required|string|in:open,close,pending',
        ];
    }

    public function messages(): array
    {
        return [
            'stock_id.required' => 'Stock ID is required.',
            'issue_price.required' => 'Issue price is required.',
            'total_shares.required' => 'Total shares are required.',
            'open_date.required' => 'Open date is required.',
            'close_date.after' => 'Close date must be after open date.',
            'listing_date.after' => 'Listing date must be after close date.',
            'ipo_status.in' => 'IPO status must be one of: open, close, pending.',
        ];
    }
}
