<?php

namespace App\Http\Requests\UserSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->id == $this->input('user_id');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
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
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The specified user does not exist.',
            'notification_enabled.boolean' => 'The notification enabled field must be true or false.',
            'mode.required' => 'The mode is required.',
            'mode.in' => 'The mode must be either light or dark.',
        ];
    }
}
