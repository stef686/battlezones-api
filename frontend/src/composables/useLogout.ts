import { useQueryClient } from '@tanstack/vue-query';
import { useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { useSessionStore } from '@/stores/session';

/**
 * Signing out of this device, in the one order that leaves nothing behind.
 *
 * The token is revoked and forgotten first, then the viewer, then everything
 * TanStack Query read on their behalf, because a cached My team or Draft Round
 * would otherwise be drawn for whoever picks the device up next. It lands on
 * sign in with `replace`, so Back cannot return to a screen that was the
 * previous viewer's.
 *
 * It never fails: `ApiClient.logout` forgets the token whether or not the API
 * answered, so an offline venue cannot trap someone signed in.
 */
export function useLogout(): () => Promise<void> {
    const client = useApiClient();
    const session = useSessionStore();
    const queryClient = useQueryClient();
    const router = useRouter();

    return async () => {
        await client.logout();
        session.clear();
        queryClient.clear();
        await router.replace({ name: 'login' });
    };
}
