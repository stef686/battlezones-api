<?php

namespace App\Http\Resources\Users;

use App\Enums\PrivacyOption;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class PrivacySettingsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'messaging' => [
                'value' => $this->getMessagingPrivacy()->value,
                'label' => $this->getMessagingPrivacy()->label(),
            ],
            'profile' => [
                'value' => $this->getProfilePrivacy()->value,
                'label' => $this->getProfilePrivacy()->label(),
            ],
            'options' => collect(PrivacyOption::cases())->map(fn (PrivacyOption $option) => [
                'value' => $option->value,
                'label' => $option->label(),
            ])->all(),
        ];
    }
}
