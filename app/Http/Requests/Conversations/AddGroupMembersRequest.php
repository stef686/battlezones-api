<?php

namespace App\Http\Requests\Conversations;

use App\Models\Conversation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class AddGroupMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('addMembers', $this->route('conversation'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recipient_ids' => ['required', 'array', 'min:1', 'max:9'],
            'recipient_ids.*' => ['required', 'integer', 'exists:users,id'],
            'include_history' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Conversation $conversation */
            $conversation = $this->route('conversation');
            $recipientIds = $this->input('recipient_ids', []);

            $existingIds = $conversation->users()
                ->wherePivotNull('deleted_at')
                ->pluck('users.id')
                ->all();

            $alreadyPresent = array_intersect($recipientIds, $existingIds);

            if ($alreadyPresent) {
                $validator->errors()->add('recipient_ids', 'Some users are already members: '.implode(', ', $alreadyPresent));
            }

            $currentCount = count($existingIds);
            $newCount = count(array_diff($recipientIds, $existingIds));

            if ($currentCount + $newCount > 10) {
                $validator->errors()->add('recipient_ids', 'A group cannot have more than 10 members.');
            }
        });
    }
}
