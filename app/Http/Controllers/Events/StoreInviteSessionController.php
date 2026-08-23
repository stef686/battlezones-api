<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ResolveInviteToken;
use App\Http\Controllers\Controller;
use App\Models\EventInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class StoreInviteSessionController extends Controller
{
    #[Endpoint('Enter with an Invitation', 'Exchange an invite token for an API token acting as the invited account. The token expires with the invitation.')]
    #[UrlParam('token', 'string', 'The token from the invitation email.')]
    #[BodyParam('device_name', 'string', 'The name of the device entering.', required: true, example: 'iPhone')]
    #[Response(['token' => '{AUTH_TOKEN}'])]
    public function __invoke(Request $request, string $token, ResolveInviteToken $resolveInviteToken): JsonResponse
    {
        $request->validate(['device_name' => ['required', 'string', 'max:255']]);

        $invite = $resolveInviteToken->handle($token);

        $expiresAt = $this->expiryFor($invite);

        $newToken = $invite->user->createToken(
            $request->string('device_name')->toString(),
            ['*'],
            $expiresAt,
        );

        return response()->json([
            'token' => $newToken->plainTextToken,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * The token is reusable for the life of the invite, and no longer.
     *
     * A forwarded invitation email hands its reader a session, so the session
     * must die when the invitation does rather than outliving it by the
     * default token lifetime.
     */
    private function expiryFor(EventInvite $invite): Carbon
    {
        $default = now()->addMinutes((int) config('sanctum.expiration'));

        return $invite->expires_at->lessThan($default) ? $invite->expires_at : $default;
    }
}
