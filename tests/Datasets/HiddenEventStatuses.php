<?php

use App\Enums\EventStatus;

/**
 * The statuses in which an Event exists only for its Organisers: every read
 * of it lets them in and 404s everyone else, its own Players included.
 */
dataset('hidden event statuses', [
    'draft' => [EventStatus::Draft],
    'cancelled' => [EventStatus::Cancelled],
]);
