<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
  
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email missing',
            'email.email' => 'Not in email format',
            'password.required' => 'Password missing',
        ];
    }
}
