import type { ApiClient } from './client';

/**
 * Every IANA zone the API accepts for an Event, in identifier order. No
 * session needed. Asked of the API rather than the browser, because the two
 * disagree on old names and only the API's list is the one it validates.
 */
export function fetchTimezones(client: ApiClient): Promise<string[]> {
    return client.get<{ data: string[] }>('/api/timezones').then((response) => response.data);
}
