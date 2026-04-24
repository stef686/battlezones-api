<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginTokenRequest extends FormRequest
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
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['required'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'The user\'s email address.',
                'example' => 'player@example.com',
            ],
            'password' => [
                'description' => 'The user\'s password.',
                'example' => 'password',
            ],
            'device_name' => [
                'description' => 'A name identifying the device requesting the token.',
                'example' => 'iPhone 15',
            ],
        ];
    }
}
