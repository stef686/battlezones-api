<?php

namespace App\Http\Requests\Conversations;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('message'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'body' => [
                'description' => 'The updated message content.',
                'example' => 'Updated: Looking forward to the game!',
            ],
        ];
    }
}
