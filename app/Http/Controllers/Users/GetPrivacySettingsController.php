<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\PrivacySettingsResource;
use App\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class GetPrivacySettingsController extends Controller
{
    #[Endpoint('Get Privacy Settings', 'Get the authenticated user\'s privacy settings.')]
    #[ResponseFromApiResource(PrivacySettingsResource::class, model: User::class)]
    public function __invoke(): PrivacySettingsResource
    {
        return PrivacySettingsResource::make(auth()->user());
    }
}
