<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\NotificationSettingsResource;

class GetNotificationSettingsController extends Controller
{
    public function __invoke(): NotificationSettingsResource
    {
        return NotificationSettingsResource::make(auth()->user());
    }
}
