<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\GameSystemResource;
use App\Models\GameSystem;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Events', 'APIs for Events')]
class ListGameSystemsController extends Controller
{
    #[Endpoint('List Game Systems', 'Every Game System the platform knows, in name order. Public: the Event listing already filters on a Game System slug.')]
    #[Response(['data' => [
        ['id' => 4, 'name' => 'Horus Heresy', 'slug' => 'horus-heresy'],
        ['id' => 1, 'name' => 'Warhammer 40,000', 'slug' => 'warhammer-40000'],
    ]])]
    public function __invoke(): AnonymousResourceCollection
    {
        return GameSystemResource::collection(GameSystem::query()->orderBy('name')->get());
    }
}
