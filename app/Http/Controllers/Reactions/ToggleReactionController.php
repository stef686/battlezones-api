<?php

namespace App\Http\Controllers\Reactions;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Reactions', 'APIs for Reactions')]
class ToggleReactionController extends Controller
{
    #[Endpoint('Toggle Reaction', 'Toggle a reaction on a photo. Adds a reaction if none exists, removes it otherwise.')]
    #[Response(['reactions_count' => 5, 'has_reacted' => true])]
    public function __invoke(Request $request, Photo $photo): JsonResponse
    {
        $user = $request->user();

        $deleted = $photo->reactions()
            ->where('user_id', $user->id)
            ->delete();

        if (! $deleted) {
            $photo->reactions()->create(['user_id' => $user->id]);
        }

        $photo->loadCount('reactions');

        return response()->json([
            'reactions_count' => $photo->reactions_count,
            'has_reacted' => ! $deleted,
        ]);
    }
}
