<?php

namespace App\Http\Requests\Conversations;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class RemoveGroupMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('removeMember', $this->route('conversation'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Conversation $conversation */
            $conversation = $this->route('conversation');

            /** @var User $target */
            $target = $this->route('user');

            if ($target->id === $this->user()->id) {
                $validator->errors()->add('user', 'You cannot remove yourself. Use the leave endpoint instead.');

                return;
            }

            $isMember = $conversation->users()
                ->wherePivot('user_id', $target->id)
                ->wherePivotNull('deleted_at')
                ->exists();

            if (! $isMember) {
                $validator->errors()->add('user', 'This user is not a member of the conversation.');
            }
        });
    }
}
