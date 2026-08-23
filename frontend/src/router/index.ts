import { createRouter, createWebHistory, type RouteLocationNormalized, type RouteRecordRaw } from 'vue-router';

import { useApiClient } from '@/api';
import { useSessionStore } from '@/stores/session';

declare module 'vue-router' {
    interface RouteMeta {
        /** Readable without a session at all. */
        public?: boolean;
        /**
         * Reachable by an unclaimed session wherever it is, because it is part
         * of getting a password onto the account rather than a place to roam.
         */
        unclaimed?: boolean;
    }
}

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        // Placeholder until there is a home screen: the Event page is the hub
        // everything else hangs off, so it is the least wrong landing.
        redirect: { name: 'event', params: { eventSlug: 'end-to-end-open' } },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/LoginView.vue'),
        meta: { public: true, unclaimed: true },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('@/views/ForgotPasswordView.vue'),
        meta: { public: true, unclaimed: true },
    },
    {
        // The API mails this one with `?token=&email=`, so the path carries no
        // parameters of its own and stays stable for deep linking.
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('@/views/ResetPasswordView.vue'),
        meta: { public: true, unclaimed: true },
    },
    {
        // Top-level and stable: an Invite link has to survive being emailed,
        // forwarded, and later claimed by a Universal Links association, which
        // cannot cover a token nested under an Event.
        path: '/invites/:token',
        name: 'invite',
        component: () => import('@/views/InviteView.vue'),
        props: true,
        meta: { public: true, unclaimed: true },
    },
    {
        path: '/claim',
        name: 'claim',
        component: () => import('@/views/ClaimView.vue'),
        meta: { public: true, unclaimed: true },
    },
    {
        path: '/events/:eventSlug',
        name: 'event',
        component: () => import('@/views/EventView.vue'),
        props: true,
        meta: { public: true },
    },
    {
        path: '/events/:eventSlug/schedule',
        name: 'schedule',
        component: () => import('@/views/ScheduleView.vue'),
        props: true,
        meta: { public: true },
    },
    {
        path: '/events/:eventSlug/attendees',
        name: 'attendees',
        component: () => import('@/views/AttendeesView.vue'),
        props: true,
        meta: { public: true },
    },
    {
        path: '/events/:eventSlug/attendees/:attendeeId',
        name: 'attendee',
        component: () => import('@/views/AttendeeView.vue'),
        props: true,
        meta: { public: true },
    },
    {
        path: '/events/:eventSlug/register',
        name: 'register',
        component: () => import('@/views/RegisterView.vue'),
        props: true,
    },
    {
        path: '/events/:eventSlug/my-team',
        name: 'my-team',
        component: () => import('@/views/MyTeamView.vue'),
        props: true,
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

    /**
     * One guard, two questions: is there a session, and is it allowed here.
     *
     * The restricted mode for an unclaimed account lives here rather than in
     * each screen. A screen that re-checks is a screen that can forget to, and
     * the rule — your own Event, plus the way out of restriction — is the same
     * everywhere.
     */
    router.beforeEach(async (to) => {
        const client = useApiClient();

        if (!client.isAuthenticated()) {
            if (to.meta.public === true) {
                return true;
            }

            // Where they were going, so login can put them back there rather than
            // dropping them on a home screen they did not ask for.
            return { name: 'login', query: { redirect: to.fullPath } };
        }

        const session = useSessionStore();

        // On a cold load the profile has not arrived yet, and an unclaimed
        // account that is not known to be unclaimed is not confined at all.
        // The guard is the thing that has to know, so the guard waits.
        if (session.viewer === null) {
            await session.load(client);
        }

        if (!session.isUnclaimed || to.meta.unclaimed === true) {
            return true;
        }

        return isTheirOwnEvent(to, session.invite?.eventSlug) ? true : { name: 'claim' };
    });

    return router;
}

/**
 * An unclaimed session is confined to the Event it was invited to. Anything
 * without an Event in its path is a public surface by definition, so it is not
 * theirs either.
 */
function isTheirOwnEvent(to: RouteLocationNormalized, eventSlug: string | undefined): boolean {
    return typeof eventSlug === 'string' && to.params.eventSlug === eventSlug;
}

export { routes };
