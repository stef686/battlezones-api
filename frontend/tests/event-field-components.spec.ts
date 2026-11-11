import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createMemoryHistory, createRouter } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import EventBannerField from '@/components/EventBannerField.vue';
import EventColourField from '@/components/EventColourField.vue';
import EventDetailsFields from '@/components/EventDetailsFields.vue';
import EventShapeFields from '@/components/EventShapeFields.vue';
import EventVenueFields from '@/components/EventVenueFields.vue';
import ScoreTypeListEditor from '@/components/ScoreTypeListEditor.vue';
import { blankScoreTypeRow, type ScoreTypeRow } from '@/lib/event-fields';

/** Answers only reads, so a write attempt shows up as a call that is not a GET. */
function stubReads() {
    const fetch = vi.fn((url: string) => {
        const body = String(url).endsWith('/api/countries')
            ? { data: [{ code: 'FR', name: 'France' }, { code: 'GB', name: 'United Kingdom' }] }
            : { data: [{ id: 4, name: 'Horus Heresy', slug: 'horus-heresy' }] };

        return Promise.resolve({ ok: true, status: 200, headers: new Headers(), json: () => Promise.resolve(body) });
    });

    vi.stubGlobal('fetch', fetch);

    return fetch;
}

function writesOf(fetch: ReturnType<typeof stubReads>): unknown[] {
    const calls = fetch.mock.calls as unknown as [string, RequestInit | undefined][];

    return calls.filter(([, init]) => (init?.method ?? 'GET') !== 'GET');
}

let queryClient: QueryClient;

function plugins() {
    return { plugins: [[VueQueryPlugin, { queryClient }] as never] };
}

beforeEach(() => {
    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    const router = createRouter({ history: createMemoryHistory(), routes: [{ path: '/', component: { template: '<p />' } }] });
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the event details fields', () => {
    it('hands an edit back to its parent and sends nothing', async () => {
        const fetch = stubReads();
        const view = mount(EventDetailsFields, {
            props: { modelValue: { name: 'London GT', starts_at: '', ends_at: '' } },
            global: plugins(),
        });

        await view.get('[data-testid="event-name"]').setValue('Croydon GT');
        await flushPromises();

        expect(view.emitted('update:modelValue')?.at(-1)).toEqual([{ name: 'Croydon GT', starts_at: '', ends_at: '' }]);
        expect(writesOf(fetch)).toEqual([]);
    });

    it('shows a message under the field it is keyed to', () => {
        const view = mount(EventDetailsFields, {
            props: { modelValue: { name: '', starts_at: '', ends_at: '' }, errors: { ends_at: ['Too early.'] } },
            global: plugins(),
        });

        expect(view.get('[data-testid="event-ends-at-error"]').text()).toBe('Too early.');
    });
});

describe('the event venue fields', () => {
    it('offers the countries itself, and hands a pick back without sending it', async () => {
        const fetch = stubReads();
        const venue = { venue_name: 'The Hall', venue_address: '', venue_city: '', venue_country: 'GB' };
        const view = mount(EventVenueFields, { props: { modelValue: venue }, global: plugins() });
        await flushPromises();

        await view.get('[data-testid="event-venue-country"]').setValue('FR');

        expect(view.emitted('update:modelValue')?.at(-1)).toEqual([{ ...venue, venue_country: 'FR' }]);
        expect(writesOf(fetch)).toEqual([]);
    });
});

describe('the event shape fields', () => {
    const shape = { game_system_id: '4', attendee_size: '2', max_attendees: '32' };

    it('hands a new size back without sending it', async () => {
        const fetch = stubReads();
        const view = mount(EventShapeFields, { props: { modelValue: shape }, global: plugins() });
        await flushPromises();

        await view.get('[data-testid="event-attendee-size"]').setValue('1');

        expect(view.emitted('update:modelValue')?.at(-1)).toEqual([{ ...shape, attendee_size: '1' }]);
        expect(writesOf(fetch)).toEqual([]);
    });

    it('fixes the game system and team size when locked, and says why, but leaves places open', async () => {
        stubReads();
        const view = mount(EventShapeFields, { props: { modelValue: shape, locked: true }, global: plugins() });
        await flushPromises();

        expect(view.find('[data-testid="event-shape-locked"]').exists()).toBe(true);
        expect(view.get('[data-testid="event-game-system"]').attributes('disabled')).toBeDefined();
        expect(view.get('[data-testid="event-attendee-size"]').attributes('disabled')).toBeDefined();

        await view.get('[data-testid="event-max-attendees"]').setValue('40');

        expect(view.emitted('update:modelValue')?.at(-1)).toEqual([{ ...shape, max_attendees: '40' }]);
    });
});

describe('the event colour field', () => {
    it('hands a preset back without sending it', async () => {
        const fetch = stubReads();
        const view = mount(EventColourField, { props: { modelValue: null }, global: plugins() });

        await view.get('[data-testid="event-colour-preset-#2563eb"]').trigger('click');

        expect(view.emitted('update:modelValue')?.at(-1)).toEqual(['#2563eb']);
        expect(writesOf(fetch)).toEqual([]);
    });

    it('marks the preset the event already wears', () => {
        const view = mount(EventColourField, { props: { modelValue: '#2563eb' }, global: plugins() });

        expect(view.get('[data-testid="event-colour-preset-#2563eb"]').attributes('aria-pressed')).toBe('true');
        expect(view.get('[data-testid="event-colour-none"]').attributes('aria-pressed')).toBe('false');
    });

    it('takes any hex typed in, whatever its case, and stores it the one way the api does', async () => {
        const view = mount(EventColourField, { props: { modelValue: null }, global: plugins() });

        await view.get('[data-testid="event-colour-hex"]').setValue('#7C3AED');

        expect(view.emitted('update:modelValue')?.at(-1)).toEqual(['#7c3aed']);
    });

    it('holds back a hex until it is whole, and says what it wants', async () => {
        const view = mount(EventColourField, { props: { modelValue: '#2563eb' }, global: plugins() });

        await view.get('[data-testid="event-colour-hex"]').setValue('#7c3');

        expect(view.emitted('update:modelValue')).toBeUndefined();
        expect(view.get('[data-testid="event-colour-hex-error"]').text()).toContain('#c2410c');
    });

    it('goes back to the platform colour', async () => {
        const view = mount(EventColourField, { props: { modelValue: '#2563eb' }, global: plugins() });

        await view.get('[data-testid="event-colour-none"]').trigger('click');

        expect(view.emitted('update:modelValue')?.at(-1)).toEqual([null]);
    });

    it('shows a refusal from the api under the hex', () => {
        const view = mount(EventColourField, {
            props: { modelValue: '#2563eb', errors: { colour: ['The colour must be a hex value like #c2410c.'] } },
            global: plugins(),
        });

        expect(view.get('[data-testid="event-colour-hex-error"]').text()).toBe('The colour must be a hex value like #c2410c.');
    });
});

describe('the banner field', () => {
    async function choose(view: ReturnType<typeof mount>, file: File): Promise<void> {
        const input = view.get<HTMLInputElement>('[data-testid="event-banner"]');

        Object.defineProperty(input.element, 'files', { value: [file], configurable: true });
        await input.trigger('change');
    }

    it('says which file was chosen and uploads nothing', async () => {
        const fetch = stubReads();
        const view = mount(EventBannerField, { props: { banner: null } });
        const file = new File(['bytes'], 'hall.jpg', { type: 'image/jpeg' });

        await choose(view, file);

        expect(view.emitted('choose')).toEqual([[file]]);
        expect(fetch).not.toHaveBeenCalled();
    });

    it('refuses a file that is not an image the api takes, without passing it on', async () => {
        const view = mount(EventBannerField, { props: { banner: null } });

        await choose(view, new File(['bytes'], 'hall.gif', { type: 'image/gif' }));

        expect(view.emitted('choose')).toBeUndefined();
        expect(view.get('[data-testid="event-banner-error"]').text()).toContain('JPEG, PNG or WebP');
    });

    it('refuses a file over 8MB, without passing it on', async () => {
        const view = mount(EventBannerField, { props: { banner: null } });
        const oversized = new File([new Uint8Array(8 * 1024 * 1024 + 1)], 'hall.png', { type: 'image/png' });

        await choose(view, oversized);

        expect(view.emitted('choose')).toBeUndefined();
        expect(view.get('[data-testid="event-banner-error"]').text()).toContain('8MB');
    });

    it('asks for the banner to be removed rather than removing it', async () => {
        const fetch = stubReads();
        const view = mount(EventBannerField, {
            props: { banner: { large: 'https://cdn.test/l.webp', small: 'https://cdn.test/s.webp' } },
        });

        await view.get('[data-testid="event-banner-remove"]').trigger('click');

        expect(view.emitted('remove')).toHaveLength(1);
        expect(fetch).not.toHaveBeenCalled();
    });
});

describe('the score type editor', () => {
    function rows(): ScoreTypeRow[] {
        return [
            { ...blankScoreTypeRow(), id: 7, name: 'Match Points', is_primary: true },
            { ...blankScoreTypeRow(), id: 8, name: 'Victory Points' },
        ];
    }

    it('hands a reordered set back without sending it', async () => {
        const fetch = stubReads();
        const view = mount(ScoreTypeListEditor, { props: { modelValue: rows() } });

        await view.get('[data-testid="score-down-0"]').trigger('click');

        const [reordered] = view.emitted<[ScoreTypeRow[]]>('update:modelValue')?.at(-1) ?? [];

        expect(reordered?.map((row) => row.name)).toEqual(['Victory Points', 'Match Points']);
        expect(fetch).not.toHaveBeenCalled();
    });

    it('moves the game listing lead rather than letting two rows hold it', async () => {
        const view = mount(ScoreTypeListEditor, { props: { modelValue: rows() } });

        await view.get('[data-testid="score-toggle-1"]').trigger('click');
        await view.get('[data-testid="score-primary-1"]').trigger('click');

        const [claimed] = view.emitted<[ScoreTypeRow[]]>('update:modelValue')?.at(-1) ?? [];

        expect(claimed?.map((row) => row.is_primary)).toEqual([false, true]);
    });

    it('shows a message keyed by row under that row, unfolded', () => {
        const view = mount(ScoreTypeListEditor, {
            props: { modelValue: rows(), errors: { '1.name': ['Already taken.'] } },
        });

        expect(view.get('[data-testid="score-name-1-error"]').text()).toBe('Already taken.');
        expect(view.find('[data-testid="score-name-0"]').exists()).toBe(false);
    });
});
