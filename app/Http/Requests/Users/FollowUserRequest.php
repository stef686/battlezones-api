<?php

namespace App\Http\Requests\Users;

use App\Services\PrivacyService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FollowUserRequest extends FormRequest
{
    public function authorize(PrivacyService $privacyService): bool
    {
        $target = $this->route('user');

        return $this->user()->isNot($target)
            && ! $privacyService->isBlocked($this->user(), $target);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
