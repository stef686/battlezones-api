import { ref, type Ref } from 'vue';

/**
 * The service worker, and the one thing the app wants from it.
 *
 * Registration is deliberately the only place the virtual module is imported,
 * and it is imported dynamically: it exists only in a production build, so a
 * static import would be a broken specifier in tests and in dev.
 *
 * The worker precaches the app shell and nothing else — API responses are
 * network-only, because server state is the query layer's to cache and two
 * caches that disagree produce stale standings nobody can debug at a venue.
 */
export interface ServiceWorkerState {
    /** A new bundle is waiting. The reader is asked, never interrupted. */
    updateAvailable: Ref<boolean>;
    register: () => Promise<void>;
    /** Activate the waiting worker and reload once. */
    applyUpdate: () => Promise<void>;
}

export function useServiceWorker(): ServiceWorkerState {
    const updateAvailable = ref(false);

    let update: ((reloadPage?: boolean) => Promise<void>) | null = null;

    async function register(): Promise<void> {
        if (!import.meta.env.PROD || !('serviceWorker' in navigator)) {
            return;
        }

        const { registerSW } = await import('virtual:pwa-register');

        update = registerSW({
            onNeedRefresh(): void {
                updateAvailable.value = true;
            },
        });
    }

    async function applyUpdate(): Promise<void> {
        updateAvailable.value = false;

        await update?.(true);
    }

    return { updateAvailable, register, applyUpdate };
}
