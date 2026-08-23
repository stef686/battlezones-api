<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\SendFeedbackRequests;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class SendEventFeedbackRequestsController extends Controller
{
    public function __construct(private SendFeedbackRequests $sendFeedbackRequests) {}

    #[Endpoint('Send Feedback Requests', 'Organisers only. Emails every Player their own one-time link, valid for 30 days. Players who have already answered are left alone.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(['data' => ['invited' => 24]])]
    public function __invoke(Event $event): JsonResponse
    {
        Gate::authorize('organise', $event);

        $invited = $this->sendFeedbackRequests->execute($event);

        return response()->json(['data' => ['invited' => $invited]]);
    }
}
