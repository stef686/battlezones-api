<?php

namespace App\Http\Requests\Users;

use App\Enums\Country;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'regex:/^[a-zA-Z][a-zA-Z0-9_-]{2,29}$/', Rule::unique('users')->ignore($this->user()?->id)],
            'country' => ['sometimes', 'nullable', 'string', Rule::enum(Country::class)],
            'show_public_name' => ['sometimes', 'boolean'],
        ];
    }
}
