<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserProfileResource;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseField;

#[Group('Users', 'APIs for Users')]
class MyProfileController extends Controller
{
    #[Endpoint('Current User Profile', "Display the current user's profile data.")]
    #[Response(['data' => [
        'id' => 12,
        'updated_at' => '2026-08-01T10:00:00Z',
        'public_name' => 'Ada Lovelace',
        'country' => 'GB',
        'email' => 'ada@example.com',
        'is_claimed' => true,
        'email_verified' => true,
        'unread_notifications_count' => 3,
        'game_systems' => [],
        'avatar' => '',
        'location' => '',
        'events_count' => 0,
        'followers_count' => 4,
        'following_count' => 7,
    ]])]
    #[ResponseField('data.is_claimed', 'boolean', 'Whether this account has been claimed with a password. An unclaimed account exists only because someone invited it, and the SPA restricts what it may do.')]
    #[ResponseField('data.email_verified', 'boolean', 'Whether the email address on the account has been verified.')]
    #[ResponseField('data.unread_notifications_count', 'integer', 'How many in-app notifications are unread.')]
    public function __invoke(Request $request): UserProfileResource
    {
        $user = $request->user();
        $user->loadCount(['followers', 'following']);

        return UserProfileResource::make($user);
    }
}
