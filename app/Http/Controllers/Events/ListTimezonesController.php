<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Events', 'APIs for Events')]
class ListTimezonesController extends Controller
{
    /**
     * The zones the server's own timezone data holds, which is exactly what
     * `timezone:all` accepts. Served rather than left to the browser's list:
     * the two disagree on old names (Chrome reports "Asia/Calcutta"), and the
     * server's set of old names depends on how its PHP was packaged, so only
     * the server can say which names it takes.
     */
    #[Endpoint('List Timezones', 'Every IANA timezone an Event may be run in, in identifier order. Public: it is the list a timezone picker offers.')]
    #[Response(['data' => ['Africa/Abidjan', 'Africa/Accra']])]
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => DateTimeZone::listIdentifiers()]);
    }
}
