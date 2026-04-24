<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchUsersRequest extends FormRequest
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
            'q' => ['required', 'string', 'min:1'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function queryParameters(): array
    {
        return [
            'q' => [
                'description' => 'The search query to find users by name or username.',
                'example' => 'john',
            ],
        ];
    }
}
