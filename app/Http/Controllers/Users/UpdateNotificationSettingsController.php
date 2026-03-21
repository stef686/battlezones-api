<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateNotificationSettingsRequest;
use App\Http\Resources\Users\NotificationSettingsResource;

class UpdateNotificationSettingsController extends Controller
{
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
