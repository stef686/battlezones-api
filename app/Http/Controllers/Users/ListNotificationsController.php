<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Users', 'APIs for Users')]
#[Authenticated]
class ListNotificationsController extends Controller
{
    #[Endpoint('List Notifications', "The authenticated Player's in-app notifications, newest first, with the unread count alongside. Event notifications always arrive here, whatever the Player's channel preferences say.")]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $notifications = $user->notifications()->paginate();

        return NotificationResource::collection($notifications)
            ->additional(['meta' => ['unread_count' => $user->unreadNotifications()->count()]]);
    }
}
