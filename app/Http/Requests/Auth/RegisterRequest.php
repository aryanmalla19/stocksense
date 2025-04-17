<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50|min:3|regex:/^[A-Za-z\s]+$/',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                'min:8',
                'max:50',
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.regex' => 'Name can only contain letters and spaces',
            'name.min' => 'Name must be at least 3 characters',
            'name.max' => 'Name must not exceed 50 characters',

            'email.required' => 'Email is missing',
            'email.email' => 'Not a valid email format',
            'email.unique' => 'This email is already registered',

            'password.required' => 'Password is missing',
            'password.min' => 'Password must be at least 8 characters',
            'password.max' => 'Password must not exceed 50 characters',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character',
            'password.confirmed' => 'Password confirmation does not match',
        ];
    }
}
