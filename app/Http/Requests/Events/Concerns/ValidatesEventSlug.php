<?php

namespace App\Http\Requests\Events\Concerns;

use Illuminate\Validation\Rule;

/**
 * The shape an Event's slug is held to, wherever one is proposed.
 *
 * Creating an Event and asking whether a slug is free refuse the same
 * malformed and reserved slugs with the same words, so the wizard's live
 * check never says "free" to a slug the create would then refuse. Whether it
 * is taken is left to each request: the create refuses a taken slug, while the
 * availability check answers it.
 */
trait ValidatesEventSlug
{
    /**
     * Slugs an Event can never hold, because the SPA route they would sit at
     * is, or is kept for, one of its own: `/events/new` is the create wizard,
     * declared before `/events/:eventSlug`, so an Event at that address could
     * never be opened. Mirrored by `RESERVED_SLUGS` in the SPA.
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = ['new', 'create'];

    /**
     * @return list<mixed>
     */
    protected function slugShapeRules(): array
    {
        return [
            'required',
            'string',
            'between:3,60',
            // Hyphens only between words: no leading, trailing or doubled ones.
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            Rule::notIn(self::RESERVED_SLUGS),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function slugMessages(): array
    {
        return [
            'slug.between' => 'The URL must be between 3 and 60 characters.',
            'slug.regex' => 'The URL may only use lowercase letters, numbers and single hyphens between them.',
            'slug.not_in' => 'That URL is reserved.',
            'slug.unique' => 'That URL is already taken.',
        ];
    }
}
