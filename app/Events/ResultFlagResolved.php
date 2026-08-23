<?php

namespace App\Events;

use App\Models\GameResultFlag;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An Organiser closed a flag, whether or not they changed the result.
 */
class ResultFlagResolved
{
    use Dispatchable;

    public function __construct(
        public GameResultFlag $flag,
        public User $resolvedBy,
    ) {}
}
