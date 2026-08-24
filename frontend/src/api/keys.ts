/**
 * Query keys, shaped like the API's own resources.
 *
 * `['events', slug, 'rounds', 4]` nests inside `['events', slug, 'rounds']`,
 * which nests inside `['events', slug]`. That is what makes invalidation
 * precise: the pulse can retire every Round of one Event without touching its
 * Standings, and without a screen having to know which keys its neighbours
 * happened to choose.
 */
export const keys = {
    event: (slug: string) => ['events', slug] as const,
    pulse: (slug: string) => ['events', slug, 'pulse'] as const,
    schedule: (slug: string) => ['events', slug, 'schedule'] as const,
    factions: (slug: string) => ['events', slug, 'factions'] as const,
    attendees: (slug: string, search: string, page: number) =>
        ['events', slug, 'attendees', { search, page }] as const,
    attendee: (slug: string, attendeeId: number) => ['events', slug, 'attendees', attendeeId] as const,
    rounds: (slug: string) => ['events', slug, 'rounds'] as const,
    round: (slug: string, roundId: number) => ['events', slug, 'rounds', roundId] as const,
    standings: (slug: string) => ['events', slug, 'standings'] as const,
    myGame: (slug: string) => ['events', slug, 'my-game'] as const,
    flags: (slug: string) => ['events', slug, 'flags'] as const,
    polls: (slug: string) => ['events', slug, 'polls'] as const,
    pollCandidates: (slug: string, pollId: number) => ['events', slug, 'polls', pollId, 'candidates'] as const,
    pollResults: (slug: string, pollId: number) => ['events', slug, 'polls', pollId, 'results'] as const,
};
