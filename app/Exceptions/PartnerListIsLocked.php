<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * A team mate tried to change the Faction of a Player whose army list is locked.
 *
 * The Faction locks with the list, since opponents prepare against both. A
 * Player cannot change their own once it is in, so a team mate cannot do it
 * for them either: only an Organiser, who can reopen the list, may.
 */
class PartnerListIsLocked extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Their list is locked. Ask an organiser to reopen it if their Faction needs changing.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 403);
    }
}
