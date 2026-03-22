<?php

namespace App\Http\Requests\Conversations;

use App\Models\User;
use App\Services\PrivacyService;
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
            'recipient_id' => ['required', 'exists:users,id'],
            'body' => ['required', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ((int) $this->input('recipient_id') === $this->user()->id) {
                $validator->errors()->add('recipient_id', 'You cannot message yourself.');

                return;
            }

            $recipient = User::find($this->input('recipient_id'));

            if (! $recipient) {
                return;
            }

            $privacyService = app(PrivacyService::class);

            if ($privacyService->isBlocked($this->user(), $recipient)) {
                $validator->errors()->add('recipient_id', 'You cannot message this user.');

                return;
            }

            if (! $privacyService->canMessage($this->user(), $recipient)) {
                $validator->errors()->add('recipient_id', "This user's privacy settings prevent you from messaging them.");
            }
        });
    }
}
