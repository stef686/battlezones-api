<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Somebody tried to enter, be added to or be invited into a Draft Event.
 *
 * A Draft is being set up: nobody comes in until it is published. Only its
 * Organisers ever get this far — everyone else is answered 404 before it —
 * so the message speaks to them.
 */
class EventIsDraft extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Publish the event first.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
