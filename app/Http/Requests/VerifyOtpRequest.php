<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust this if you want to apply auth checks
    }

    public function rules(): array
    {
        return [
            'otp' => 'required|string|size:6',
            'email' => 'required|email',
            'private_token' => 'required|string|size:32',
        ];
    }

    public function messages(): array
    {
        return [
            'otp.required' => 'OTP is required.',
            'otp.size' => 'OTP must be 6 digits.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'private_token.required' => 'Private token is required.',
            'private_token.size' => 'Private token must be exactly 32 characters.',
        ];
    }
}
