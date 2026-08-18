<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ResolveInviteToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class StoreInviteSessionController extends Controller
{
    #[Endpoint('Enter with an Invitation', 'Exchange an invite token for an API token acting as the invited account.')]
    #[UrlParam('token', 'string', 'The token from the invitation email.')]
    #[BodyParam('device_name', 'string', 'The name of the device entering.', required: true, example: 'iPhone')]
    #[Response(['token' => '{AUTH_TOKEN}'])]
    public function __invoke(Request $request, string $token, ResolveInviteToken $resolveInviteToken): JsonResponse
    {
        $request->validate(['device_name' => ['required', 'string', 'max:255']]);

        $invite = $resolveInviteToken->handle($token);

        $newToken = $invite->user->createToken($request->string('device_name')->toString());

        return response()->json([
            'token' => $newToken->plainTextToken,
            'expires_at' => $newToken->accessToken->created_at
                ->addMinutes(config('sanctum.expiration')),
        ]);
    }
}
