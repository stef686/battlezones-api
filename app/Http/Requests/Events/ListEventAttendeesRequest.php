<?php

namespace App\Http\Requests\Events;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListEventAttendeesRequest extends FormRequest
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
            'search' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function queryParameters(): array
    {
        return [
            'search' => [
                'description' => 'Search attendees by name or username.',
                'example' => 'john',
            ],
        ];
    }
}
