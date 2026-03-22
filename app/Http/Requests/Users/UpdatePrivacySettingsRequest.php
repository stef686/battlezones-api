<?php

namespace App\Http\Requests\Users;

use App\Enums\PrivacyOption;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrivacySettingsRequest extends FormRequest
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
            'messaging' => ['sometimes', Rule::enum(PrivacyOption::class)],
            'profile' => ['sometimes', Rule::enum(PrivacyOption::class)],
        ];
    }
}
