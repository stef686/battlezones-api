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

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'messaging' => [
                'description' => 'Who can send messages. One of: anyone, followers_only, following_only, mutual_followers, fellow_club_members.',
                'example' => 'anyone',
            ],
            'profile' => [
                'description' => 'Who can view the profile. One of: anyone, followers_only, following_only, mutual_followers, fellow_club_members.',
                'example' => 'anyone',
            ],
        ];
    }
}
