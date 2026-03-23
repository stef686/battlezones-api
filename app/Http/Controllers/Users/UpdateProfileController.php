<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateProfileRequest;
use App\Http\Resources\Users\UserProfileResource;
use App\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class UpdateProfileController extends Controller
{
    #[Endpoint('Update Profile', "Update the current user's profile.")]
    #[ResponseFromApiResource(UserProfileResource::class, model: User::class)]
    public function __invoke(UpdateProfileRequest $request): UserProfileResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return UserProfileResource::make($user);
    }
}
