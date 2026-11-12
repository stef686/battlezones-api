<?php

use App\Enums\EventStatus;

/**
 * The statuses in which an Event's Rounds are its Organisers' alone: every
 * one before it is under way, plus Cancelled. Published is among them because
 * nothing moves an Event to Active by itself.
 */
dataset('statuses before rounds are public', [
    'draft' => [EventStatus::Draft],
    'published' => [EventStatus::Published],
    'cancelled' => [EventStatus::Cancelled],
]);
