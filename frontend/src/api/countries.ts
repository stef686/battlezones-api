import type { ApiClient } from './client';

/** A country a venue may be in: the ISO 3166-1 alpha-2 code sent back, and the name read. */
export interface Country {
    code: string;
    name: string;
}

/** Every country the API accepts, in name order. No session needed. */
export function fetchCountries(client: ApiClient): Promise<Country[]> {
    return client.get<{ data: Country[] }>('/api/countries').then((response) => response.data);
}
