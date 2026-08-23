import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import type { ApiClient } from '@/api/client';

export interface Viewer {
    id: number;
    public_name: string;
    is_claimed: boolean;
    email_verified: boolean;
    unread_notifications_count: number;
}

/**
 * Session and client-only state. Server data belongs to TanStack Query and is
 * never copied in here: two owners of the same fact is two chances to be stale.
 */
export const useSessionStore = defineStore('session', () => {
    const viewer = ref<Viewer | null>(null);
    const loading = ref(false);

    const isAuthenticated = computed(() => viewer.value !== null);

    async function load(client: ApiClient): Promise<void> {
        if (!client.isAuthenticated()) {
            viewer.value = null;

            return;
        }

        loading.value = true;

        try {
            const response = await client.get<{ data: Viewer }>('/api/profile');
            viewer.value = response.data;
        } catch {
            // A profile we cannot read is a session we cannot use. The client
            // has already cleared the token if it was the token's fault.
            viewer.value = null;
        } finally {
            loading.value = false;
        }
    }

    function clear(): void {
        viewer.value = null;
    }

    return { viewer, loading, isAuthenticated, load, clear };
});
