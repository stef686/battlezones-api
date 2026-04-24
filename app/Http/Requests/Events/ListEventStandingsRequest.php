<?php

namespace App\Http\Requests\Events;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListEventStandingsRequest extends FormRequest
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
            'sort_by' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function queryParameters(): array
    {
        return [
            'search' => [
                'description' => 'Search standings by player name or username.',
                'example' => 'john',
            ],
            'sort_by' => [
                'description' => 'The field to sort standings by.',
                'example' => 'points',
            ],
        ];
    }
}
