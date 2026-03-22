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
            'usernames' => ['required', 'array', 'min:1'],
            'usernames.*' => ['required', 'string', 'exists:users,username'],
            'include_history' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Conversation $conversation */
            $conversation = $this->route('conversation');
            $usernames = $this->input('usernames', []);

            $existingUsernames = $conversation->users()
                ->wherePivotNull('deleted_at')
                ->pluck('username')
                ->all();

            $alreadyPresent = array_intersect($usernames, $existingUsernames);

            if ($alreadyPresent) {
                $validator->errors()->add('usernames', 'Some users are already members: '.implode(', ', $alreadyPresent));
            }

            $currentCount = count($existingUsernames);
            $newCount = count(array_diff($usernames, $existingUsernames));

            if ($currentCount + $newCount > 10) {
                $validator->errors()->add('usernames', 'A group cannot have more than 10 members.');
            }
        });
    }
}
