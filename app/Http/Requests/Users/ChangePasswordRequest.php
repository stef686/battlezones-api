<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'current_password' => [
                'description' => 'The user\'s current password for verification.',
                'example' => 'password',
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
