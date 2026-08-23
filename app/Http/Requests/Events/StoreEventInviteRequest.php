<?php

namespace App\Http\Requests\Events;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('email', 'string', 'The email address to invite.', required: true, example: 'captain@example.com')]
#[BodyParam('name', 'string', 'The name to give the invited account, if it does not exist yet.', required: false, example: 'Horus Lupercal')]
class StoreEventInviteRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
