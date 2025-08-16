<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'recent_password' => 'required',
            'new_password' => [
                'required',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                'min:8',
                'max:50',
                'confirmed',
                'different:recent_password',
            ],
        ];
    }

    public function messages()
    {
        return [
            'recent_password.required' => 'Recent password is missing',
            'new_password.required' => 'New password is required',
            'new_password.min' => 'Password must be at least 8 characters',
            'new_password.max' => 'Password must not exceed 50 characters',
            'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character',
            'new_password.confirmed' => 'Password confirmation does not match',
            'new_password.different' => 'New password must not be same as old',
        ];
    }
}
