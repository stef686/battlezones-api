<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdatePrivacySettingsRequest;
use App\Http\Resources\Users\PrivacySettingsResource;

class UpdatePrivacySettingsController extends Controller
{
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
