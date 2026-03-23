<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdatePrivacySettingsRequest;
use App\Http\Resources\Users\PrivacySettingsResource;
use App\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class UpdatePrivacySettingsController extends Controller
{
    #[Endpoint('Update Privacy Settings', 'Update the authenticated user\'s privacy settings.')]
    #[ResponseFromApiResource(PrivacySettingsResource::class, model: User::class)]
    public function __invoke(UpdatePrivacySettingsRequest $request): PrivacySettingsResource
    {
        $user = $request->user();

        $user->update([
            'privacy_settings' => array_merge(
                $user->privacy_settings ?? [],
                $request->validated(),
            ),
        ]);

        return PrivacySettingsResource::make($user);
    }
}
