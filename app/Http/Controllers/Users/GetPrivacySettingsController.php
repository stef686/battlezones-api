<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\PrivacySettingsResource;

class GetPrivacySettingsController extends Controller
{
    public function __invoke(): PrivacySettingsResource
    {
        return PrivacySettingsResource::make(auth()->user());
    }
}
