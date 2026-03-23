<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\NotificationSettingsResource;
use App\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class GetNotificationSettingsController extends Controller
{
    #[Endpoint('Get Notification Settings', 'Get the authenticated user\'s notification settings.')]
    #[ResponseFromApiResource(NotificationSettingsResource::class, model: User::class)]
    public function __invoke(): NotificationSettingsResource
    {
        return NotificationSettingsResource::make(auth()->user());
    }
}
