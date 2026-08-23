<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\CarbonInterface;
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
        $abilities = $accessToken->abilities ?? ['*'];
        $expiresAt = $this->expiryFor($accessToken);

        $accessToken->delete();

        $newToken = $user->createToken($deviceName, $abilities, $expiresAt);

        return response()->json([
            'token' => $newToken->plainTextToken,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * A token is refreshable if it hasn't expired, or has expired
     * within the grace period. Explicitly revoked tokens (deleted
     * from DB) won't reach here since findToken returns null.
     */
    private function isRefreshable(PersonalAccessToken $token): bool
    {
        $expiresAt = $this->currentExpiryOf($token);

        if (! $expiresAt) {
            return true;
        }

        return now()->lte($expiresAt->copy()->addMinutes(self::GRACE_PERIOD_MINUTES));
    }

    /**
     * Refreshing renews a session; it must never extend one.
     *
     * An invite session is deliberately capped to the life of the Invite it
     * came from, so the replacement carries that ceiling forward rather than
     * being minted afresh from the config default and outliving the Invite.
     */
    private function expiryFor(PersonalAccessToken $token): ?CarbonInterface
    {
        $expiration = config('sanctum.expiration');
        $default = $expiration ? now()->addMinutes((int) $expiration) : null;
        $ceiling = $token->expires_at;

        if (! $ceiling) {
            return $default;
        }

        if (! $default) {
            return $ceiling;
        }

        return $ceiling->lessThan($default) ? $ceiling : $default;
    }

    /**
     * The expiry the token is actually living under: its own where one was
     * set explicitly, otherwise the config default measured from issue.
     */
    private function currentExpiryOf(PersonalAccessToken $token): ?CarbonInterface
    {
        if ($token->expires_at) {
            return $token->expires_at;
        }

        $expiration = config('sanctum.expiration');

        return $expiration ? $token->created_at->copy()->addMinutes((int) $expiration) : null;
    }
}
