<?php

namespace App\Events;

use App\Models\GameResultFlag;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A Player or Organiser claimed a Game's result is wrong.
 */
class ResultFlagged
{
    use Dispatchable;

    public function __construct(public GameResultFlag $flag) {}
}
