import type { ApiClient } from './client';

export type FeedbackQuestionType = 'rating' | 'text';

export interface FeedbackQuestion {
    id: number;
    key: string;
    prompt: string;
    type: FeedbackQuestionType;
}

export interface FeedbackForm {
    event: { id: number; name: string; slug: string };
    expires_at: string;
    questions: FeedbackQuestion[];
}

/** A rating question is answered with a rating, a text one with text. */
export interface FeedbackAnswer {
    question_id: number;
    rating?: number;
    answer?: string;
}

function tokenPath(token: string): string {
    return `/api/feedback/${encodeURIComponent(token)}`;
}

/**
 * The questions behind a feedback link.
 *
 * The token is the whole credential — there is no session behind this, and
 * the reader may never have signed in on the device the email opened on. A
 * link that is unknown, spent or expired all answer 404 alike, which is why
 * the screen explains the three together rather than picking one.
 */
export function fetchFeedbackForm(client: ApiClient, token: string): Promise<FeedbackForm> {
    return client.get<{ data: FeedbackForm }>(tokenPath(token)).then((response) => response.data);
}

/** Spends the link: answers are stored against the Event, never the Player. */
export function submitFeedback(client: ApiClient, token: string, answers: FeedbackAnswer[]): Promise<unknown> {
    return client.post(tokenPath(token), { answers });
}
