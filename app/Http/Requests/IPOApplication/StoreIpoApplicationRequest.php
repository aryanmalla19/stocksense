<?php

namespace App\Http\Requests\IPOApplication;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\IpoDetail;
use Illuminate\Support\Carbon;

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
            'ipo_id' => 'required|integer|exists:ipo_details,id',
            'applied_shares' => 'required|integer|min:10',
            'status' => 'nullable|string|in:pending,allotted,not_allotted',
            'allotted_shares' => 'nullable|integer',
        ];
    }

    /**
     * Perform custom validation to ensure that the applied date is within the IPO open and close times.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Retrieve the IPO details based on the ipo_id from the request
            $ipo = IpoDetail::find($this->input('ipo_id'));
            if ($ipo) {
                // Check IPO status
                $ipoStatus = $ipo->ipo_status; // Matches JSON field name
                if ($ipoStatus !== 'opened') {
                    $validator->errors()->add('invalid_status', 'The IPO must be open to apply.');
                }

                // Parse the IPO open and close dates into Carbon instances
                $ipoOpenTime = Carbon::parse($ipo->open_date); // Use 'open_date' from JSON
                $ipoCloseTime = Carbon::parse($ipo->close_date); // Use 'close_date' from JSON
                $currentTime = Carbon::now();

                // Check if the current time is within the IPO open and close dates
                if ($currentTime->lt($ipoOpenTime) || $currentTime->gt($ipoCloseTime)) {
                    $validator->errors()->add('invalid_time', 'The application time must be between the IPO open and close time.');
                }
            }
        });
    }

    /**
     * Custom validation messages.
     */
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
            'status.in' => 'Status must be one of the following: pending, allotted, or not_allotted.',

            'applied_date.required' => 'The date of application is required.',
            'applied_date.date' => 'Applied date must be a valid date.',

            'allotted_shares.integer' => 'Allotted shares must be a number if provided.',
        ];
    }
}