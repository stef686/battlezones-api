<?php

namespace App\Http\Requests\Conversations;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateGroupNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('updateName', $this->route('conversation'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The new group conversation name.',
                'example' => 'Weekend Warriors',
            ],
        ];
    }
}
