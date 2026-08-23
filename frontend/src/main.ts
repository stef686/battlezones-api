import { VueQueryPlugin } from '@tanstack/vue-query';
import { createPinia } from 'pinia';
import { createApp } from 'vue';

import { createApiClient } from '@/api';
import App from '@/App.vue';
import { createAppRouter } from '@/router';
import { useSessionStore } from '@/stores/session';

import './style.css';

const app = createApp(App);
const router = createAppRouter();
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(VueQueryPlugin, {
    queryClientConfig: {
        defaultOptions: {
            queries: {
                // Venue wifi: a stale read is better than a spinner, and the
                // pulse endpoint (#95) will drive invalidation later.
                staleTime: 10_000,
                retry: 1,
                refetchOnWindowFocus: true,
            },
        },
    },
});

const client = createApiClient(router);

// Boot and resume are exactly when the token is most likely to have expired
// while the app was asleep, so it is renewed before the first request rather
// than after the first failure.
async function refreshAndLoad(): Promise<void> {
    await client.refreshIfDue().catch(() => {
        // Offline on boot is not a reason to refuse to start.
    });

    await useSessionStore(pinia).load(client);
}

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        void refreshAndLoad();
    }
});

void refreshAndLoad().finally(() => app.mount('#app'));
