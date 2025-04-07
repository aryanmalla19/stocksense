<?php

namespace App\Http\Requests\PasswordReset;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            //
        ];
    }

    public function messages()
{
    return [
        'email.required' => 'Please provide your email address.',
        'email.email' => 'Please provide a valid email address.',
        'password.required' => 'Please enter your new password.',
        'password.min' => 'Your password must be at least 6 characters long.',
        'password.confirmed' => 'The password confirmation does not match.',
    ];
}

}
