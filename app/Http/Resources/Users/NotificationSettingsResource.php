<?php

namespace App\Http\Resources\Users;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class NotificationSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $settings = [];

        foreach (NotificationType::cases() as $type) {
            $channels = $this->getNotificationChannels($type);

            $settings[$type->value] = [
                'label' => $type->label(),
                'channels' => array_column($channels, 'value'),
            ];
        }

        return $settings;
    }
}
