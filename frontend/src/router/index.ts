import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';

import { useApiClient } from '@/api';

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        redirect: { name: 'my-game', params: { eventSlug: 'end-to-end-open' } },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/LoginView.vue'),
        meta: { public: true },
    },
    {
        path: '/events/:eventSlug/my-game',
        name: 'my-game',
        component: () => import('@/views/MyGameView.vue'),
        props: true,
    },
    {
        path: '/events/:eventSlug/standings',
        name: 'standings',
        component: () => import('@/views/StandingsView.vue'),
        props: true,
        meta: { public: true },
    },
];

export function createAppRouter() {
    const router = createRouter({
        history: createWebHistory(),
        routes,
    });

    router.beforeEach((to) => {
        if (to.meta.public === true) {
            return true;
        }

        if (useApiClient().isAuthenticated()) {
            return true;
        }

        // Where they were going, so login can put them back there rather than
        // dropping them on a home screen they did not ask for.
        return { name: 'login', query: { redirect: to.fullPath } };
    });

    return router;
}

export { routes };
