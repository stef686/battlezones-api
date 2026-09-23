import type { ApiClient } from './client';

export interface RoundSummary {
    id: number;
    number: number;
    name: string | null;
    status: string;
}

/**
 * A team as a score table needs it: a name, a verdict and a row of numbers.
 *
 * The Round's pairings and a single Game's detail both carry more than this,
 * and both are read by the same table, so what the table needs is named once
 * here rather than described again beside each of them.
 */
export interface ScoredAttendee {
    id: number;
    name: string;
    /** The team's badge, signed and expiring. Null where they have not uploaded one. */
    avatar?: string | null;
    /**
     * Decided by the API, which knows each Score Type's ranking order and
     * sort direction. A drawn Game — and one nobody has played, where every
     * score is equally absent — has no winner at all.
     */
    is_winner: boolean;
    scores: Record<string, number | string>;
}

export interface PairedAttendee extends ScoredAttendee {
    allegiance: string | null;
    members: { id: number; name: string; faction: { id: number; name: string } | null }[];
}

export interface Pairing {
    id: number;
    table_number: number | null;
    is_bye: boolean;
    /**
     * Absent for anyone who does not run the Event: whether a Game repeats a
     * pairing is what an Organiser checks before publishing a Draft, and the
     * API omits the key rather than sending a false. Presence is the
     * permission — the screen does not re-check who is looking.
     */
    is_rematch?: boolean;
    result: { submitted_at: string | null; is_flagged: boolean };
    attendees: PairedAttendee[];
}

export interface ScoreColumn {
    slug: string;
    name: string;
    /** The heading to show over the numbers, chosen by the Organiser. */
    abbreviation: string;
    /**
     * The one column a listing of Games leads with, of however many the Event
     * is scored on. Exactly one column carries it — the API resolves which,
     * so nothing here has to fall back when an Organiser has marked none.
     */
    is_primary: boolean;
}

/**
 * The columns a Game listing has room for: the primary one alone.
 *
 * A Round is a hall of Games read at a glance, and a row of every column the
 * Event scores on is a table to parse rather than a number to see. The Game
 * itself still shows them all.
 */
export function listedColumns(columns: ScoreColumn[]): ScoreColumn[] {
    return columns.filter((column) => column.is_primary);
}

export interface RoundDetail extends RoundSummary {
    /**
     * What every Game in the Round is scored on, sent whether or not any
     * result has landed. A Game nobody has played yet still shows the columns
     * it is waiting on rather than collapsing to nothing.
     */
    score_types: ScoreColumn[];
    games: Pairing[];
}

/**
 * The Rounds this reader may see.
 *
 * Draft Rounds are filtered by the API, not here: a Player is never sent one,
 * so there is nothing for the screen to hide. An Organiser is sent them, and
 * they arrive marked as drafts.
 */
export function fetchRounds(client: ApiClient, slug: string): Promise<RoundSummary[]> {
    return client.get<{ data: RoundSummary[] }>(`${eventPath(slug)}/rounds`).then((response) => response.data);
}

/**
 * The Round a reader arriving at the Rounds tab wants: the last one that has
 * been published.
 *
 * Drafts are skipped rather than led with. Only an Organiser is ever sent one,
 * and landing them on pairings nobody else can see yet — while the Round being
 * played sits one chevron behind — reads as the Event having moved on when it
 * has not. An Organiser with nothing but a Draft still gets it, because it is
 * the only Round there is.
 */
export function latestPlayable(rounds: RoundSummary[]): RoundSummary | null {
    const ordered = byNumber(rounds);
    const published = ordered.filter((round) => round.status !== 'draft');

    return published.at(-1) ?? ordered.at(-1) ?? null;
}

/** Rounds in the order they are played, whatever order they arrived in. */
export function byNumber(rounds: RoundSummary[]): RoundSummary[] {
    return [...rounds].sort((left, right) => left.number - right.number);
}

/**
 * A score column's heading, short enough to sit over a number.
 *
 * The Organiser's own abbreviation, which the API sends with every column and
 * fills in from the name where none was written. Taken as typed — an Organiser
 * who writes "VPs" means the lower case — and only fallen back on where a
 * payload predates the field, since a heading is not something to invent
 * twice: an Event declares its own Score Types, and the name is not what a
 * column of numbers has room for.
 */
export function columnLabel(column: { name: string; abbreviation?: string }): string {
    const written = column.abbreviation?.trim() ?? '';

    if (written !== '') {
        return written;
    }

    const words = column.name.split(/\s+/).filter((word) => word.length > 0);

    // One word has no initials to take — "Kills" abbreviated to "K" says less
    // than the first three letters of it do — so a single-word Score Type is
    // shortened rather than reduced to its first letter.
    const short = words.length > 1
        ? words.map((word) => word[0]).join('')
        : (words[0] ?? column.name).slice(0, 3);

    return short.slice(0, 3).toUpperCase() || column.name.slice(0, 3).toUpperCase();
}

/**
 * Where a Game is being played, as a reader crossing a hall asks for it.
 *
 * A Bye is not at a table at all. A Game that is at one and has no number yet
 * says so rather than reading as "Table null": an unnumbered table is a
 * pairing an Organiser has not finished placing, which is a different thing
 * from a team sitting out.
 */
export function tableLabel(game: Pick<Pairing, 'is_bye' | 'table_number'>): string {
    if (game.is_bye) {
        return 'Bye';
    }

    return game.table_number === null ? 'Table TBC' : `Table ${game.table_number}`;
}

/** What a Round is called, falling back to its number rather than to blank. */
export function roundTitle(round: Pick<RoundSummary, 'number' | 'name'>): string {
    return round.name ?? `Round ${round.number}`;
}

export function fetchRound(client: ApiClient, slug: string, roundId: number): Promise<RoundDetail> {
    return client.get<{ data: RoundDetail }>(`${eventPath(slug)}/rounds/${roundId}`).then((response) => response.data);
}

/**
 * Pair the field into a new Draft Round.
 *
 * Refused, with a message naming what to put right, while the current Round
 * is unpublished or still has results outstanding.
 */
export function generateRound(client: ApiClient, slug: string): Promise<RoundDetail> {
    return client.post<{ data: RoundDetail }>(`${eventPath(slug)}/rounds`).then((response) => response.data);
}

export function publishRound(client: ApiClient, slug: string, roundId: number): Promise<RoundDetail> {
    return client.post<{ data: RoundDetail }>(`${eventPath(slug)}/rounds/${roundId}/publish`)
        .then((response) => response.data);
}

/**
 * Recombine two Games of a Draft Round.
 *
 * The exchange itself is not a choice — the API performs the only legal one —
 * so this sends the two Games and nothing else.
 */
export function swapPairings(client: ApiClient, slug: string, roundId: number, gameIds: [number, number]): Promise<RoundDetail> {
    return client.post<{ data: RoundDetail }>(`${eventPath(slug)}/rounds/${roundId}/swap`, { game_ids: gameIds })
        .then((response) => response.data);
}

/** Refused once any result is in: by then the Round has been played on. */
export function unpublishRound(client: ApiClient, slug: string, roundId: number): Promise<RoundDetail> {
    return client.delete<{ data: RoundDetail }>(`${eventPath(slug)}/rounds/${roundId}/publish`)
        .then((response) => response.data);
}

function eventPath(slug: string): string {
    return `/api/events/${encodeURIComponent(slug)}`;
}
