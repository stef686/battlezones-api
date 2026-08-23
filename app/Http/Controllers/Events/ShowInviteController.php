<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ResolveInviteToken;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventInviteResource;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ShowInviteController extends Controller
{
    #[Endpoint('Open an Invitation', 'Resolve an emailed invite token. The link is reusable for the life of the invite.')]
    #[UrlParam('token', 'string', 'The token from the invitation email.')]
    public function __invoke(string $token, ResolveInviteToken $resolveInviteToken): EventInviteResource
    {
        return EventInviteResource::make(
            $resolveInviteToken->handle($token)->load(['user', 'event'])
        );
    }
}
