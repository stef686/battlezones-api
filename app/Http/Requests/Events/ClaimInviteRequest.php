<?php

namespace App\Http\Requests\Events;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ClaimInviteRequest extends FormRequest
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
            'password' => ['required', 'confirmed'],
            'device_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'password' => [
                'description' => 'The password to set on the account.',
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
            'name' => [
                'description' => 'The name to go by, if it differs from the one the organiser entered.',
                'example' => 'Horus Lupercal',
            ],
        ];
    }
}
