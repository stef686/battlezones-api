<?php

namespace App\Http\Controllers\Reactions;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;

#[Group('Reactions')]
class ToggleReactionController extends Controller
{
    public function __invoke(Request $request, Photo $photo): JsonResponse
    {
        $user = $request->user();

        $existing = $photo->reactions()
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            $photo->reactions()->create(['user_id' => $user->id]);
        }

        return response()->json([
            'reactions_count' => $photo->reactions()->count(),
        ]);
    }
}
