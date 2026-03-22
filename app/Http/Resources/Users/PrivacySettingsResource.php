<?php

namespace App\Http\Resources\Users;

use App\Enums\PrivacyOption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class PrivacySettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $messaging = $this->getMessagingPrivacy();
        $profile = $this->getProfilePrivacy();

        return [
            'messaging' => [
                'value' => $messaging->value,
                'label' => $messaging->label(),
            ],
            'profile' => [
                'value' => $profile->value,
                'label' => $profile->label(),
            ],
            'options' => array_map(fn (PrivacyOption $option) => [
                'value' => $option->value,
                'label' => $option->label(),
            ], PrivacyOption::cases()),
        ];
    }
}
