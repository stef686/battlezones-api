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
