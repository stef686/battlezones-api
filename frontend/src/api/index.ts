import type { Router } from 'vue-router';

import { ApiClient, type ApiClientOptions } from './client';

let client: ApiClient | null = null;

/**
 * The one client the app talks through.
 *
 * It needs the router to send a lost session to login, and the router needs
 * guards that ask the client whether there is a session, so the two are tied
 * together here rather than importing each other.
 */
export function createApiClient(router: Router, options: Omit<ApiClientOptions, 'onSessionLost'> = {}): ApiClient {
    client = new ApiClient({
        ...options,
        onSessionLost: () => {
            const current = router.currentRoute.value;

            if (current.name === 'login') {
                return;
            }

            void router.push({ name: 'login', query: { redirect: current.fullPath } });
        },
    });

    return client;
}

export function useApiClient(): ApiClient {
    if (client === null) {
        throw new Error('The API client is used before the app created it.');
    }

    return client;
}

export { ApiClient };
