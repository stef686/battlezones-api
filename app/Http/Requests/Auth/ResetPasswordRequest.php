<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'token' => [
                'description' => 'The password reset token from the reset email.',
                'example' => 'abc123resettoken',
            ],
            'email' => [
                'description' => 'The email address associated with the account.',
                'example' => 'player@example.com',
            ],
            'password' => [
                'description' => 'The new password (min 8 characters).',
                'example' => 'newpassword',
            ],
            'password_confirmation' => [
                'description' => 'Password confirmation, must match the password field.',
                'example' => 'newpassword',
            ],
        ];
    }
}
