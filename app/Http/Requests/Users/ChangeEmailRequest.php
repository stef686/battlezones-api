<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:users,email'],
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
            'email' => [
                'description' => 'The new email address.',
                'example' => 'newemail@example.com',
            ],
        ];
    }
}
