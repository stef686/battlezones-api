<?php

namespace App\Http\Requests\Conversations;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StartConversationRequest extends FormRequest
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
            'recipient_ids' => ['required', 'array', 'min:1', 'max:'.(config('battlezones.group_member_limit') - 1)],
            'recipient_ids.*' => ['required', 'integer', 'exists:users,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
        ];
    }

    public function isGroupConversation(): bool
    {
        return count($this->validated('recipient_ids')) > 1;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $recipientIds = $this->input('recipient_ids', []);

            if (in_array($this->user()->id, array_map('intval', $recipientIds), true)) {
                $validator->errors()->add('recipient_ids', 'You cannot include yourself as a recipient.');

                return;
            }

            if (count($recipientIds) > 1 && ! $this->filled('name')) {
                $validator->errors()->add('name', 'A group name is required when starting a group conversation.');
            }
        });
    }
}
