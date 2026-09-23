<?php

namespace App\Http\Controllers\Events;

use App\Enums\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\CountryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Events', 'APIs for Events')]
class ListCountriesController extends Controller
{
    #[Endpoint('List Countries', 'Every country a venue may be in, as an ISO 3166-1 alpha-2 code and the name to show for it, in name order. Public: it is the list a country picker offers.')]
    #[Response(['data' => [
        ['code' => 'AF', 'name' => 'Afghanistan'],
        ['code' => 'AX', 'name' => 'Åland Islands'],
    ]])]
    public function __invoke(): AnonymousResourceCollection
    {
        return CountryResource::collection(Country::cases());
    }
}
