/**
 * One shape for every failure, so no screen has to know how the API phrases
 * its errors — or whether the request reached it at all.
 */
export type ApiErrorKind =
    | 'validation'
    | 'unauthenticated'
    | 'forbidden'
    | 'not_found'
    | 'conflict'
    | 'rate_limited'
    | 'server'
    | 'network';

export interface NormalisedError {
    kind: ApiErrorKind;
    status: number | null;
    message: string;
    /** Validation messages by field, with dot paths kept as the API sends them. */
    fields: Record<string, string[]>;
    /** Seconds to wait before retrying, when the API said so. */
    retryAfter: number | null;
    /** The response body, so a caller that knows the endpoint can read more. */
    body: unknown;
}

export class ApiError extends Error implements NormalisedError {
    readonly kind: ApiErrorKind;
    readonly status: number | null;
    readonly fields: Record<string, string[]>;
    readonly retryAfter: number | null;
    readonly body: unknown;

    constructor(error: NormalisedError) {
        super(error.message);
        this.name = 'ApiError';
        this.kind = error.kind;
        this.status = error.status;
        this.fields = error.fields;
        this.retryAfter = error.retryAfter;
        this.body = error.body;
    }
}

const MESSAGES: Record<ApiErrorKind, string> = {
    validation: 'Some of that could not be accepted.',
    unauthenticated: 'Your session has ended. Log in again to carry on.',
    forbidden: 'You do not have permission to do that.',
    // Never "private": a 404 is what a missing thing and a hidden thing both
    // look like, and guessing which would leak the difference.
    not_found: 'That could not be found.',
    conflict: 'That has already been done.',
    rate_limited: 'Too many attempts. Give it a moment.',
    server: 'Something went wrong at our end.',
    network: 'You appear to be offline. Check your connection and try again.',
};

function kindFor(status: number): ApiErrorKind {
    switch (status) {
        case 401:
            return 'unauthenticated';
        case 403:
            return 'forbidden';
        case 404:
            return 'not_found';
        case 409:
            return 'conflict';
        case 422:
            return 'validation';
        case 429:
            return 'rate_limited';
        default:
            return 'server';
    }
}

/**
 * Laravel sends validation errors as `errors: { 'scores.9.victory-points': [...] }`.
 * The dot path is the field's identity in the form, so it is kept whole rather
 * than being split into something a nested form would have to reassemble.
 */
function fieldsFrom(body: unknown): Record<string, string[]> {
    if (typeof body !== 'object' || body === null || !('errors' in body)) {
        return {};
    }

    const errors = (body as { errors: unknown }).errors;

    if (typeof errors !== 'object' || errors === null) {
        return {};
    }

    return Object.fromEntries(
        Object.entries(errors as Record<string, unknown>).map(([field, messages]) => [
            field,
            (Array.isArray(messages) ? messages : [messages]).map(String),
        ]),
    );
}

function messageFrom(body: unknown, kind: ApiErrorKind): string {
    if (typeof body === 'object' && body !== null && 'message' in body) {
        const message = (body as { message: unknown }).message;

        if (typeof message === 'string' && message !== '') {
            return message;
        }
    }

    return MESSAGES[kind];
}

function retryAfterFrom(headers: Headers | null): number | null {
    const header = headers?.get('Retry-After');

    if (header === null || header === undefined) {
        return null;
    }

    const seconds = Number(header);

    return Number.isFinite(seconds) ? seconds : null;
}

export function normaliseResponse(status: number, body: unknown, headers: Headers | null = null): ApiError {
    const kind = kindFor(status);

    return new ApiError({
        kind,
        status,
        message: messageFrom(body, kind),
        fields: kind === 'validation' ? fieldsFrom(body) : {},
        retryAfter: kind === 'rate_limited' ? retryAfterFrom(headers) : null,
        body,
    });
}

/**
 * A request that never got an answer. Distinct from a 500 on purpose: the
 * caller may safely retry this one, and cannot know whether it was applied.
 */
export function normaliseNetworkFailure(cause: unknown): ApiError {
    return new ApiError({
        kind: 'network',
        status: null,
        message: MESSAGES.network,
        fields: {},
        retryAfter: null,
        body: cause,
    });
}
