import type { ApiClient } from './client';

export interface ArmyList {
    army_list: string | null;
    submitted_at: string | null;
    is_locked: boolean;
}

function eventPath(slug: string): string {
    return `/api/events/${slug}`;
}

/**
 * Submit this Player's own list, which locks it.
 *
 * There is no separate save: a list that could be edited after the field has
 * seen it would be no use to the opponents preparing against it. Correcting
 * one means asking an Organiser to reopen it.
 */
export function submitArmyList(client: ApiClient, slug: string, armyList: string): Promise<ArmyList> {
    return client.put<{ data: ArmyList }>(`${eventPath(slug)}/army-list`, { army_list: armyList })
        .then((response) => response.data);
}

/**
 * Open a team's lists to the field before every member has submitted.
 *
 * Lists reveal on their own once everyone has locked in; this is the way out
 * of a team held hostage by a partner who never opened their invitation.
 */
export function revealArmyLists(client: ApiClient, slug: string, attendeeId: number): Promise<unknown> {
    return client.post(`${eventPath(slug)}/attendees/${attendeeId}/army-lists/reveal`, {});
}

/** Reopen one Player's list so a mistake in it can be fixed. */
export function unlockArmyList(client: ApiClient, slug: string, attendeeId: number, memberId: number): Promise<ArmyList> {
    return client.post<{ data: ArmyList }>(`${eventPath(slug)}/attendees/${attendeeId}/members/${memberId}/army-list/unlock`, {})
        .then((response) => response.data);
}
