<?php

namespace App\Exceptions;

use RuntimeException;

class AttendeeMemberAlreadyEntered extends RuntimeException
{
    public static function for(string $name): self
    {
        return new self("{$name} has already entered this event.");
    }
}
