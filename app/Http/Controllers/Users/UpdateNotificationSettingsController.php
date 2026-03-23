<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateNotificationSettingsRequest;
use App\Http\Resources\Users\NotificationSettingsResource;
use App\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class UpdateNotificationSettingsController extends Controller
{
    #[Endpoint('Update Notification Settings', 'Update the authenticated user\'s notification settings.')]
    #[ResponseFromApiResource(NotificationSettingsResource::class, model: User::class)]
    public function __invoke(UpdateNotificationSettingsRequest $request): NotificationSettingsResource
    {
        $user = $request->user();

        $user->update([
            'notification_settings' => array_merge(
                $user->notification_settings ?? [],
                $request->validated(),
            ),
        ]);

        return NotificationSettingsResource::make($user);
    }
}
