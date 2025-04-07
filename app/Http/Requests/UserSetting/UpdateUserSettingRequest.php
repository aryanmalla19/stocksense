<?php

namespace App\Http\Requests\UserSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'notification_enabled' => 'nullable|boolean',
            'mode' => ['required', Rule::in(['light', 'dark'])],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'notification_enabled.boolean' => 'The notification enabled field must be true or false.',
            'mode.required' => 'The mode is required.',
            'mode.in' => 'The mode must be either light or dark.',
        ];
    }
}
