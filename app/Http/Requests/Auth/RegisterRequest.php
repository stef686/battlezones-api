<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed'],
            'device_name' => ['required'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The user\'s display name.',
                'example' => 'John Doe',
            ],
            'email' => [
                'description' => 'The user\'s email address.',
                'example' => 'player@example.com',
            ],
            'password' => [
                'description' => 'The user\'s password.',
                'example' => 'password',
            ],
            'password_confirmation' => [
                'description' => 'Password confirmation, must match the password field.',
                'example' => 'password',
            ],
            'device_name' => [
                'description' => 'A name identifying the device requesting the token.',
                'example' => 'iPhone 15',
            ],
        ];
    }
}
