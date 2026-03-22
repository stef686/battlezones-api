<?php

namespace App\Http\Requests\Conversations;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StartGroupConversationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'usernames' => ['required', 'array', 'min:2', 'max:9'],
            'usernames.*' => ['required', 'string', 'exists:users,username'],
            'body' => ['required', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $usernames = $this->input('usernames', []);

            if (in_array($this->user()->username, $usernames, true)) {
                $validator->errors()->add('usernames', 'You cannot add yourself to the group.');
            }
        });
    }
}
