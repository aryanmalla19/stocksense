<?php

namespace App\Http\Requests\IPODetails;

use Illuminate\Foundation\Http\FormRequest;

class StoreIpoDetailRequest extends FormRequest
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
            'stock_id' => 'required|integer|exists:stocks,id',
            'issue_price' => 'required|integer|min:100',
            'total_shares' => 'required|integer|min:20',
            'open_date' => 'required|date',
            'close_date' => 'required|date|after:open_date',
            'listing_date' => 'required|date|after:close_date',
        ];
    }

    public function messages(): array
    {
        return [
            'stock_id' => 'Stock id is required.',
            'stock_id.exists' => 'The selected stock does not exist.',
            'issue_price.min' => 'Issue price must be at least :min.',
            'total_shares.min' => 'Total shares must be at least :min.',
            'close_date.after' => 'Close date must be after open date.',
            'listing_date.after' => 'Listing date must be after close date.',
            'ipo_status.in' => 'IPO status must be one of: opened, closed, pending.',

        ];
    }
}
