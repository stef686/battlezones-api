import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import type { ApiClient } from '@/api/client';
import { BrowserInviteStorage, type RememberedInvite } from '@/api/invite-storage';

export interface Viewer {
    id: number;
    public_name: string;
    is_claimed: boolean;
    email_verified: boolean;
    unread_notifications_count: number;
}

const inviteStorage = new BrowserInviteStorage();

/**
 * Session and client-only state. Server data belongs to TanStack Query and is
 * never copied in here: two owners of the same fact is two chances to be stale.
 */
export const useSessionStore = defineStore('session', () => {
    const viewer = ref<Viewer | null>(null);
    const loading = ref(false);
    const invite = ref<RememberedInvite | null>(inviteStorage.read());

    const isAuthenticated = computed(() => viewer.value !== null);

    /**
     * An account that entered on an Invite and has not set a password yet.
     *
     * Unknown counts as claimed. This is a product rule about where an
     * unclaimed Player is shown — the API enforces what they may actually do —
     * so a profile we failed to read should not strand someone behind a Claim
     * screen they may have no invite token for.
     */
    const isUnclaimed = computed(() => viewer.value !== null && !viewer.value.is_claimed);

    /**
     * The single in-flight profile read.
     *
     * Boot loads the profile and the route guard needs it loaded before it can
     * decide anything, and both happen at once on a cold start. Two reads
     * would be one wasted request on venue wifi.
     */
    let pending: Promise<void> | null = null;

    function load(client: ApiClient): Promise<void> {
        pending ??= read(client).finally(() => {
            pending = null;
        });

        return pending;
    }

    async function read(client: ApiClient): Promise<void> {
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

    function rememberInvite(remembered: RememberedInvite): void {
        invite.value = remembered;
        inviteStorage.write(remembered);
    }

    function forgetInvite(): void {
        invite.value = null;
        inviteStorage.clear();
    }

    function clear(): void {
        viewer.value = null;
        forgetInvite();
    }

    return { viewer, loading, invite, isAuthenticated, isUnclaimed, load, rememberInvite, forgetInvite, clear };
});
