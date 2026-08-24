import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import FeedbackView from '@/views/FeedbackView.vue';

const TOKEN = 'a-very-long-random-token';

const FORM = {
    data: {
        event: { id: 1, name: 'London Grand Tournament', slug: 'london-grand-tournament' },
        expires_at: '2026-10-13T09:00:00Z',
        questions: [
            { id: 1, key: 'overall', prompt: 'Overall, how was the event?', type: 'rating' },
            { id: 2, key: 'venue', prompt: 'How were the venue and the tables?', type: 'rating' },
            { id: 8, key: 'anything_else', prompt: 'Anything else?', type: 'text' },
        ],
    },
};

const NOT_FOUND = { status: 404, body: { message: 'Not Found.' } };

function stubApi(routes: Record<string, { status: number; body?: unknown }>) {
    const fetch = vi.fn((url: string, init?: RequestInit) => {
        void init;

        const path = String(url).replace('https://api.test', '').split('?')[0] ?? '';
        const match = Object.entries(routes).find(([pattern]) => path.endsWith(pattern));
        const { status, body } = match?.[1] ?? NOT_FOUND;

        return Promise.resolve({
            ok: status >= 200 && status < 300,
            status,
            headers: new Headers(),
            json: () => Promise.resolve(body ?? null),
        });
    });

    vi.stubGlobal('fetch', fetch);

    return fetch;
}

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

function mountView() {
    return mount(FeedbackView as never, ({
        props: { token: TOKEN },
        global: { plugins: [pinia, router, [VueQueryPlugin, { queryClient }]] },
    }) as never);
}

beforeEach(async () => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);

    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    router = createAppRouter();

    // No session at all: the link arrives by email, on a device that may never
    // have signed in.
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });

    await router.push(`/feedback/${TOKEN}`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the feedback form', () => {
    it('opens from the link alone, and asks the event\'s own questions', async () => {
        stubApi({ [`/api/feedback/${TOKEN}`]: { status: 200, body: FORM } });

        const view = mountView();
        await flushPromises();

        expect(view.text()).toContain('London Grand Tournament');
        expect(view.get('[data-testid="question-1"]').text()).toContain('Overall, how was the event?');

        // A rating question is answered by tapping, a text one by typing.
        expect(view.findAll('[data-testid="rating-1-3"]')).toHaveLength(1);
        expect(view.find('[data-testid="answer-8"]').exists()).toBe(true);
        expect(view.find('[data-testid="rating-8-3"]').exists()).toBe(false);
    });

    it('sends only what was answered, and says thank you', async () => {
        const fetch = stubApi({ [`/api/feedback/${TOKEN}`]: { status: 200, body: FORM } });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="rating-1-4"]').trigger('click');
        await view.get('[data-testid="answer-8"]').setValue('The missions were excellent.');
        await view.get('[data-testid="submit-feedback"]').trigger('click');
        await flushPromises();

        const sent = fetch.mock.calls.find(([, init]) => init?.method === 'POST')!;

        // A question left alone is left out: the API refuses a rating question
        // answered with nothing, and an untouched question is not an answer.
        expect(JSON.parse(sent[1]?.body as string)).toEqual({
            answers: [
                { question_id: 1, rating: 4 },
                { question_id: 8, answer: 'The missions were excellent.' },
            ],
        });

        expect(view.get('[data-testid="feedback-thanks"]').text()).toContain('Thank you');
        expect(view.find('[data-testid="submit-feedback"]').exists()).toBe(false);
    });

    it('explains a link that has been used or has run out, rather than failing blankly', async () => {
        stubApi({});

        const view = mountView();
        await flushPromises();

        const notice = view.get('[data-testid="feedback-unusable"]').text();

        expect(notice).toContain('already been used');
        expect(notice).toContain('expired');
    });

    it('keeps what was typed when the submission fails, so nobody retypes it', async () => {
        stubApi({ [`/api/feedback/${TOKEN}`]: { status: 200, body: FORM } });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="answer-8"]').setValue('Worth saying twice.');

        stubApi({ [`/api/feedback/${TOKEN}`]: { status: 500, body: { message: 'Server Error.' } } });

        await view.get('[data-testid="submit-feedback"]').trigger('click');
        await flushPromises();

        expect(view.find('[data-testid="feedback-problem"]').exists()).toBe(true);
        expect((view.get('[data-testid="answer-8"]').element as HTMLTextAreaElement).value).toBe('Worth saying twice.');
    });
});
