<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseField;

#[Group('Events', 'APIs for Events')]
class ShowEventController extends Controller
{
    #[Endpoint('Show Event', 'Get a publicly visible event by slug.')]
    #[Response(['data' => [
        'id' => 1,
        'name' => 'London Grand Tournament',
        'slug' => 'london-grand-tournament',
        'description' => 'A two-day Horus Heresy doubles event.',
        'status' => 'published',
        'pairing_format' => 'swiss',
        'starts_at' => '2026-09-12T09:00:00Z',
        'ends_at' => '2026-09-13T18:00:00Z',
        'max_attendees' => 32,
        'venue' => [
            'name' => 'The Hall',
            'address' => '1 Example Street',
            'city' => 'London',
            'country' => 'GB',
        ],
        'game_system' => ['id' => 1, 'name' => 'Horus Heresy', 'slug' => 'horus-heresy'],
        'documents' => [],
        'created_at' => '2026-06-01T10:00:00Z',
        'updated_at' => '2026-06-01T10:00:00Z',
        'viewer' => [
            'is_organiser' => true,
            'is_lead_organiser' => false,
            'is_attendee' => true,
            'attendee_id' => 9,
            'permissions' => [
                'organise' => true,
                'register' => false,
                'manage_organisers' => false,
            ],
        ],
    ]])]
    #[ResponseField('data.viewer', 'object', 'What the reader may see and do at this Event. Null for an anonymous request.', nullable: true)]
    #[ResponseField('data.viewer.is_organiser', 'boolean', 'Whether the reader runs this Event.')]
    #[ResponseField('data.viewer.is_lead_organiser', 'boolean', 'Whether the reader leads it, and so may appoint Organisers.')]
    #[ResponseField('data.viewer.is_attendee', 'boolean', 'Whether the reader is competing here.')]
    #[ResponseField('data.viewer.attendee_id', 'integer', 'The party the reader competes as, if any.', nullable: true)]
    #[ResponseField('data.viewer.permissions.organise', 'boolean', 'Publish Rounds, correct results, open Polls, read tallies.')]
    #[ResponseField('data.viewer.permissions.register', 'boolean', 'Enter the Event.')]
    #[ResponseField('data.viewer.permissions.manage_organisers', 'boolean', 'Appoint and remove Organisers.')]
    public function __invoke(Request $request, Event $event): EventResource
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $event->load(['gameSystem', 'documents']);

        return EventResource::make($event)->withViewer();
    }
}
