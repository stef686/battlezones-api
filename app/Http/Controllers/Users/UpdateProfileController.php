<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateProfileRequest;
use App\Http\Resources\Users\UserProfileResource;

class UpdateProfileController extends Controller
{
    public function __invoke(UpdateProfileRequest $request): UserProfileResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return UserProfileResource::make($user);
    }
}
