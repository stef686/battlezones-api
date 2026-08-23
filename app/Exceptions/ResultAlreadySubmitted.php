<?php

namespace App\Exceptions;

use App\Models\Game;
use RuntimeException;

/**
 * A second submission reached a Game that is already claimed.
 *
 * First-write-wins is deliberate, and so is the 409: two Players who disagree
 * about a score have a genuine conflict, and the way out is a flag an
 * Organiser resolves, not an overwrite.
 *
 * The other way this fires is a Player whose own submission succeeded and
 * whose response never arrived — commonplace on venue wifi — retrying. The
 * controller answers with the stored Game so that client can recognise its own
 * result in a single round-trip, rather than making a second request at the
 * exact moment the network is failing and being told to dispute itself.
 */
class ResultAlreadySubmitted extends RuntimeException
{
    public function __construct(public readonly Game $game)
    {
        parent::__construct('A result has already been submitted for this game. Flag it if it needs correcting.');
    }
}
