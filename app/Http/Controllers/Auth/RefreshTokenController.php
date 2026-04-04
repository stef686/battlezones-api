<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Laravel\Sanctum\PersonalAccessToken;

#[Group('Authentication', 'APIs for authentication')]
class RefreshTokenController extends Controller
{
    private const GRACE_PERIOD_MINUTES = 2;

    #[Endpoint('Refresh Token', 'Exchange a current or recently-expired token for a new one')]
    #[Response(['token' => '{AUTH_TOKEN}', 'expires_at' => '2026-05-04T00:00:00Z'])]
    #[Response(content: ['message' => 'Unauthenticated.'], status: 401)]
    public function __invoke(Request $request): JsonResponse
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if (! $accessToken || ! $this->isRefreshable($accessToken)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        /** @var User $user */
        $user = $accessToken->tokenable;
        $deviceName = $accessToken->name;

        $accessToken->delete();

        $newToken = $user->createToken($deviceName);

        return response()->json([
            'token' => $newToken->plainTextToken,
            'expires_at' => $newToken->accessToken->created_at
                ->addMinutes(config('sanctum.expiration')),
        ]);
    }

    /**
     * A token is refreshable if it hasn't expired, or has expired
     * within the grace period. Explicitly revoked tokens (deleted
     * from DB) won't reach here since findToken returns null.
     */
    private function isRefreshable(PersonalAccessToken $token): bool
    {
        $expiration = config('sanctum.expiration');

        if (! $expiration) {
            return true;
        }

        $expiresAt = $token->created_at->addMinutes($expiration);
        $graceDeadline = $expiresAt->copy()->addMinutes(self::GRACE_PERIOD_MINUTES);

        return now()->lte($graceDeadline);
    }
}
