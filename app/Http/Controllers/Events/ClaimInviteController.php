<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ResolveInviteToken;
use App\Actions\Users\ClaimAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ClaimInviteRequest;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ClaimInviteController extends Controller
{
    #[Endpoint('Claim an Invited Account', 'Set a password on the invited account, turning it into a real one. This revokes the invitation.')]
    #[UrlParam('token', 'string', 'The token from the invitation email.')]
    #[Response(['token' => '{AUTH_TOKEN}'])]
    public function __invoke(
        ClaimInviteRequest $request,
        string $token,
        ResolveInviteToken $resolveInviteToken,
        ClaimAccount $claimAccount,
    ): JsonResponse {
        $invite = $resolveInviteToken->handle($token);

        $user = $invite->user;

        $claimAccount->handle(
            $user,
            $request->string('password')->toString(),
            $request->string('name')->toString(),
        );

        $newToken = $user->createToken($request->string('device_name')->toString());

        return response()->json([
            'token' => $newToken->plainTextToken,
            'expires_at' => $newToken->accessToken->created_at
                ->addMinutes(config('sanctum.expiration')),
        ], 201);
    }
}
