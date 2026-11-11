<?php

namespace App\Http\Requests\Events\Concerns;

use App\Models\Event;

/**
 * The Event Colour as it is held wherever it is set: a lowercase `#rrggbb`,
 * or null for the platform colour.
 *
 * Creating an Event and editing it take the colour under the same rule and
 * refuse it in the same words, so a colour the wizard accepted is one Settings
 * would too. Each request says whether the field may be left out.
 */
trait ValidatesEventColour
{
    /**
     * @return list<string>
     */
    protected function colourRules(): array
    {
        return ['nullable', 'string', 'regex:'.Event::COLOUR_PATTERN];
    }

    /**
     * @return array<string, string>
     */
    protected function colourMessages(): array
    {
        return [
            'colour.regex' => 'The colour must be a hex value like #c2410c.',
        ];
    }
}
