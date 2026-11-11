<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ShowEventSlugAvailabilityRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseField;

/**
 * Whether a slug is free for a new Event, asked while an Organiser types it.
 *
 * `events.show` answers 404 for a Draft, so it cannot tell "taken" from
 * "free". This can, and says only that: it reveals that some Event, possibly
 * a Draft, holds the slug, and nothing about which. ADR 0006 accepts that leak,
 * and keeps it to signed-in Users.
 */
#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ShowEventSlugAvailabilityController extends Controller
{
    #[Endpoint('Check an Event slug', 'Signed-in Users only. Whether a new Event could be created at this slug. A slug held by any Event, Drafts included, is taken; nothing else about that Event is said. A malformed or reserved slug is refused with 422, as creating the Event would refuse it.')]
    #[Response(['data' => ['slug' => 'croydon-doubles', 'available' => true]])]
    #[ResponseField('data.slug', 'string', 'The slug that was checked.')]
    #[ResponseField('data.available', 'boolean', 'Whether no Event holds it.')]
    public function __invoke(ShowEventSlugAvailabilityRequest $request): JsonResponse
    {
        $slug = $request->string('slug')->value();

        return response()->json([
            'data' => [
                'slug' => $slug,
                'available' => ! Event::query()->where('slug', $slug)->exists(),
            ],
        ]);
    }
}
