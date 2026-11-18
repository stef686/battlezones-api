<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class RoundHasResults extends RuntimeException
{
    public static function cannotBeUnpublished(int $number): self
    {
        return new self("Round {$number} already has results, so it cannot be unpublished.");
    }

    public static function cannotBePairedAgain(int $number): self
    {
        return new self("Round {$number} already has results, so it cannot be paired again.");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
