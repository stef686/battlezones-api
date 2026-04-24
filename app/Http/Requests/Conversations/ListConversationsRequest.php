<?php

namespace App\Http\Requests\Conversations;

use App\Enums\ConversationTab;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListConversationsRequest extends FormRequest
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
            'tab' => ['sometimes', 'string', Rule::enum(ConversationTab::class)],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function queryParameters(): array
    {
        return [
            'tab' => [
                'description' => 'Filter by tab. One of: primary, events, requests, archived.',
                'example' => 'primary',
            ],
        ];
    }
}
