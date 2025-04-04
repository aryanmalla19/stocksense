<?php

namespace App\Http\Requests;

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
            'name' => 'required|string|max:50|min:5',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'min:8',
                'max:50',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                'confirmed',
            ],
            'password_confirmation' => 'required_with:password',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'Name is missing',
            'name.max' => 'Name must not exceed 50 characters',
            'name.min' => 'Name must be at least 5 characters',
            'email.required' => 'Email is missing',
            'email.email' => 'Invalid email format',
            'email.unique' => 'Email is already taken',
            'password.required' => 'Password is missing',
            'password.min' => 'Password must be at least 8 characters',
            'password.max' => 'Password must not exceed 50 characters',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character',
            'password.confirmed' => 'Password confirmation does not match',
            'password_confirmation.required_with' => 'Password confirmation is required',
        ];
    }
}
