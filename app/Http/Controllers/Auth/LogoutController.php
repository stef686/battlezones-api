<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ScribeResponse;

#[Group('Authentication', 'APIs for authentication')]
#[Authenticated]
class LogoutController extends Controller
{
    /**
     * Revoke only the token this request carried, so signing out on one
     * device leaves the User's other devices signed in.
     */
    #[Endpoint('Log Out', 'Revoke the token the request was made with')]
    #[ScribeResponse(status: 204)]
    #[ScribeResponse(content: ['message' => 'Unauthenticated.'], status: 401)]
    public function __invoke(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
