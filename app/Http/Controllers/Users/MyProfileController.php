<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserProfileResource;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class MyProfileController extends Controller
{
    #[Endpoint('Current User Profile', "Display the current user's profile data.")]
    #[ResponseFromApiResource(UserProfileResource::class)]
    public function __invoke(): UserProfileResource
    {
        return UserProfileResource::make(auth()->user());
    }
}
