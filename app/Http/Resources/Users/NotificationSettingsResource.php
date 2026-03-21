<?php

namespace App\Http\Resources\Users;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class NotificationSettingsResource extends JsonResource
{
    public function toArray($request): array
    {
        $settings = [];

        foreach (NotificationType::cases() as $type) {
            $channels = $this->getNotificationChannels($type);

            $settings[$type->value] = [
                'label' => $type->label(),
                'channels' => array_map(
                    fn (NotificationChannel $channel): string => $channel->value,
                    $channels,
                ),
            ];
        }

        return $settings;
    }
}
