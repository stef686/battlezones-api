/**
 * The create-event wizard's draft, and what it takes for each step to pass.
 *
 * Kept out of the view so the rules a step is held to can be read in one
 * place. Nothing here talks to the API: the draft lives on the client until
 * the last step submits it whole (ADR 0006), and the server's own answer is
 * the final word — these checks only spare an Organiser a round trip for a
 * mistake the screen can already see.
 */
import type { NewEvent } from '@/api/events';
import { dayInZone, eventTimestamp } from '@/lib/dates';
import {
    blankScoreTypeRow,
    scoreTypeChangeOf,
    type EventDetails,
    type EventShape,
    type EventVenue,
    type FieldErrors,
    type ScoreTypeRow,
} from '@/lib/event-fields';

export type WizardStep = 'details' | 'format' | 'confirm';

export const WIZARD_STEPS: { id: WizardStep; name: string }[] = [
    { id: 'details', name: 'Details' },
    { id: 'format', name: 'Format' },
    { id: 'confirm', name: 'Confirm' },
];

export interface EventDraft {
    details: EventDetails;
    slug: string;
    timezone: string;
    venue: EventVenue;
    shape: EventShape;
    scoreTypes: ScoreTypeRow[];
}

/** How far ahead a new Event is pencilled in, before the Organiser picks a day. */
const DAYS_AHEAD = 7;

/** The same rules the API holds a slug to: words joined by single hyphens. */
const SLUG_PATTERN = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;

/** Slugs whose `/events/<slug>` address an SPA route already holds, as the API reserves them. */
const RESERVED_SLUGS: readonly string[] = ['new'];

/** The top-level fields a wizard step asks for, and so can show a refusal under. */
const SHOWN_FIELDS: readonly string[] = [
    'name',
    'slug',
    'starts_at',
    'ends_at',
    'timezone',
    'venue_name',
    'venue_address',
    'venue_city',
    'venue_country',
    'game_system_id',
    'attendee_size',
    'max_attendees',
    'score_types',
];

/** The parts of a scoring row the editor puts a message under. */
const SHOWN_SCORE_TYPE_FIELD = /^score_types\.\d+\.(name|abbreviation|sort_direction|is_derived|win_points|draw_points|loss_points)$/;

/**
 * A blank Event, pencilled in a week from today and run 09:00–18:00 in the
 * zone it is given, scored the way most Events are: Match Points worked out
 * from the result and ranking first, then the Victory Points actually played.
 */
export function blankDraft(timezone: string, now: Date = new Date()): EventDraft {
    const day = dayAfter(dayInZone(now.toISOString(), timezone), DAYS_AHEAD);

    return {
        details: { name: '', starts_at: `${day}T09:00`, ends_at: `${day}T18:00` },
        slug: '',
        timezone,
        venue: { venue_name: '', venue_address: '', venue_city: '', venue_country: '' },
        shape: { game_system_id: '', attendee_size: '1', max_attendees: '' },
        scoreTypes: [
            {
                ...blankScoreTypeRow(),
                name: 'Match Points',
                abbreviation: 'MP',
                is_derived: true,
                is_primary: true,
                win_points: '3',
                draw_points: '1',
                loss_points: '0',
            },
            {
                ...blankScoreTypeRow(),
                name: 'Victory Points',
                abbreviation: 'VP',
            },
        ],
    };
}

/**
 * What stops a step from passing, keyed as the API keys its own refusals, so
 * a message from either lands under the same field.
 */
export function problemsOf(step: WizardStep, draft: EventDraft, now: Date = new Date()): FieldErrors {
    if (step === 'details') {
        return detailsProblems(draft, now);
    }

    if (step === 'format') {
        return formatProblems(draft);
    }

    return {};
}

/** The step a refused field is asked on, so a 422 can send the Organiser back to it. */
export function stepOf(field: string): WizardStep {
    return field === 'game_system_id'
        || field === 'attendee_size'
        || field === 'max_attendees'
        || field === 'score_types'
        || field.startsWith('score_types.')
        ? 'format'
        : 'details';
}

/**
 * Whether a refused field has somewhere on the wizard to say so. A refusal of
 * anything else (a field no step asks for) would otherwise send the Organiser
 * to a step with no message on it.
 */
export function isShownField(field: string): boolean {
    return SHOWN_FIELDS.includes(field) || SHOWN_SCORE_TYPE_FIELD.test(field);
}

/** The first step, in wizard order, holding any of these refusals. */
export function firstStepWith(errors: FieldErrors): WizardStep {
    const steps = new Set(Object.keys(errors).map(stepOf));

    return WIZARD_STEPS.find((step) => steps.has(step.id))?.id ?? 'details';
}

/** The draft as `POST /events` takes it. */
export function submissionOf(draft: EventDraft): NewEvent {
    return {
        name: draft.details.name,
        slug: draft.slug,
        starts_at: momentOf(draft.details.starts_at, draft.timezone),
        ends_at: momentOf(draft.details.ends_at, draft.timezone),
        timezone: draft.timezone,
        venue_name: blankToNull(draft.venue.venue_name),
        venue_address: blankToNull(draft.venue.venue_address),
        venue_city: blankToNull(draft.venue.venue_city),
        venue_country: blankToNull(draft.venue.venue_country),
        game_system_id: Number(draft.shape.game_system_id),
        attendee_size: Number(draft.shape.attendee_size),
        max_attendees: draft.shape.max_attendees === '' ? null : Number(draft.shape.max_attendees),
        score_types: draft.scoreTypes.map(scoreTypeChangeOf),
    };
}

function detailsProblems(draft: EventDraft, now: Date): FieldErrors {
    const problems: FieldErrors = {};
    const { name, starts_at: startsAt, ends_at: endsAt } = draft.details;

    if (name.trim() === '') {
        problems.name = ['Give the event a name.'];
    }

    if (draft.slug === '') {
        problems.slug = ['Choose a URL for the event.'];
    } else if (draft.slug.length < 3 || draft.slug.length > 60 || !SLUG_PATTERN.test(draft.slug)) {
        problems.slug = ['Use 3 to 60 lowercase letters and numbers, with single hyphens between words.'];
    } else if (RESERVED_SLUGS.includes(draft.slug)) {
        problems.slug = ['That URL is reserved.'];
    }

    if (startsAt === '') {
        problems.starts_at = ['Choose when the event starts.'];
    } else if (timeOf(startsAt, draft.timezone) < now.getTime()) {
        problems.starts_at = ['The event cannot start in the past.'];
    }

    if (endsAt === '') {
        problems.ends_at = ['Choose when the event ends.'];
    } else if (startsAt !== '' && timeOf(endsAt, draft.timezone) < timeOf(startsAt, draft.timezone)) {
        problems.ends_at = ['The event cannot end before it starts.'];
    }

    return problems;
}

function formatProblems(draft: EventDraft): FieldErrors {
    const problems: FieldErrors = {};

    if (draft.shape.game_system_id === '') {
        problems.game_system_id = ['Choose a game system.'];
    }

    if (draft.shape.max_attendees !== '' && !/^[1-9]\d*$/.test(draft.shape.max_attendees)) {
        problems.max_attendees = ['Use a whole number, or leave it empty for no limit.'];
    }

    if (draft.scoreTypes.length === 0) {
        problems.score_types = ['Add at least one score.'];
    }

    draft.scoreTypes.forEach((row, index) => {
        if (row.name.trim() === '') {
            problems[`score_types.${index}.name`] = ['Give this score a name.'];
        }

        if (!row.is_derived) {
            return;
        }

        for (const field of ['win_points', 'draw_points', 'loss_points'] as const) {
            if (row[field] === '') {
                problems[`score_types.${index}.${field}`] = ['A worked-out column needs its win, draw and loss points.'];
            }
        }
    });

    return problems;
}

/**
 * A datetime-local value as the moment the hall will read it, written with
 * the Event's offset for that date rather than the offset of the machine
 * doing the typing.
 */
function momentOf(local: string, timezone: string): string {
    const [date = '', time = ''] = local.split('T');

    return eventTimestamp(date, time, timezone);
}

/**
 * The instant a datetime-local value names, compared as instants rather than
 * as strings: an Event straddling a clock change has two offsets in it.
 */
function timeOf(local: string, timezone: string): number {
    return new Date(momentOf(local, timezone)).getTime();
}

/** A `YYYY-MM-DD` some days on, worked field by field so no zone can move it. */
function dayAfter(date: string, days: number): string {
    const [year = 1970, month = 1, day = 1] = date.split('-').map(Number);

    return new Date(Date.UTC(year, month - 1, day + days)).toISOString().slice(0, 10);
}

function blankToNull(value: string): string | null {
    return value === '' ? null : value;
}
