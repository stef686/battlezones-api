<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ResolveInviteToken;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventInviteResource;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ShowInviteController extends Controller
{
    #[Endpoint('Open an Invitation', 'Resolve an emailed invite token. The link is reusable for the life of the invite.')]
    #[UrlParam('token', 'string', 'The token from the invitation email.')]
    #[Response(status: 200, content: ['data' => [
        'id' => 7,
        'role' => 'captain',
        'email' => 'captain@example.com',
        'name' => 'Ada Lovelace',
        'is_claimed' => false,
        'attendee_id' => null,
        'event' => [
            'slug' => 'london-grand-tournament',
            'name' => 'London Grand Tournament',
            'starts_at' => '2026-09-12T09:00:00+00:00',
            'ends_at' => '2026-09-13T18:00:00+00:00',
        ],
        'expires_at' => '2026-09-12T09:00:00+00:00',
        'revoked_at' => null,
    ]])]
    #[Response(['message' => 'Not Found.'], 404, 'The token is unknown, spent, revoked or expired.')]
    public function __invoke(string $token, ResolveInviteToken $resolveInviteToken): EventInviteResource
    {
        return EventInviteResource::make(
            $resolveInviteToken->handle($token)->load(['user', 'event'])
        );
    }
}
