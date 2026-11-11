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
import TimezoneField from '@/components/TimezoneField.vue';
import { blankScoreTypeRow, type ScoreTypeRow } from '@/lib/event-fields';

import { fakeImages } from './fake-images';

/** Answers only reads, so a write attempt shows up as a call that is not a GET. */
function stubReads() {
    const fetch = vi.fn((url: string) => {
        const path = String(url);
        const body = path.endsWith('/api/countries')
            ? { data: [{ code: 'FR', name: 'France' }, { code: 'GB', name: 'United Kingdom' }] }
            : path.endsWith('/api/timezones')
                ? { data: ['America/New_York', 'Asia/Tokyo', 'Europe/London'] }
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

    it('takes an emptied hex to mean the platform colour', async () => {
        const view = mount(EventColourField, { props: { modelValue: '#2563eb' }, global: plugins() });

        await view.get('[data-testid="event-colour-hex"]').setValue('');

        expect(view.emitted('update:modelValue')?.at(-1)).toEqual([null]);
        expect(view.find('[data-testid="event-colour-hex-error"]').exists()).toBe(false);

        await view.setProps({ modelValue: null });

        expect(view.get('[data-testid="event-colour-none"]').attributes('aria-pressed')).toBe('true');
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
        // Reading an image's size is asynchronous.
        await flushPromises();
    }

    let images: ReturnType<typeof fakeImages>;

    beforeEach(() => {
        images = fakeImages();
    });

    const hall = new File(['bytes'], 'hall.jpg', { type: 'image/jpeg' });
    const crowd = new File(['bytes'], 'crowd.jpg', { type: 'image/jpeg' });

    it('refuses an image too small to fill the header, in the words the api would use', async () => {
        images.measure(512, 512);
        const view = mount(EventBannerField, { props: { banner: null } });

        await choose(view, new File(['bytes'], 'logo.png', { type: 'image/png' }));

        expect(view.emitted('choose')).toBeUndefined();
        expect(view.get('[data-testid="event-banner-error"]').text())
            .toBe('The banner must be at least 1200 by 400 pixels.');
    });

    it('lets go of the url it measured the image through', async () => {
        const view = mount(EventBannerField, { props: { banner: null } });

        await choose(view, hall);

        expect(URL.revokeObjectURL).toHaveBeenCalledWith('blob:image-1');
    });

    it('passes on the latest pick when an earlier one finishes measuring after it', async () => {
        images.hold();
        const view = mount(EventBannerField, { props: { banner: null } });

        await choose(view, hall);
        await choose(view, crowd);

        images.finish(1, 1600, 534);
        await flushPromises();
        images.finish(0, 1600, 534);
        await flushPromises();

        expect(view.emitted('choose')).toEqual([[crowd]]);
    });

    it('does not bring a pick back once the banner has been removed mid-measure', async () => {
        images.hold();
        const view = mount(EventBannerField, {
            props: { banner: { large: 'https://cdn.test/l.webp', small: 'https://cdn.test/s.webp' } },
        });

        await choose(view, hall);
        await view.get('[data-testid="event-banner-remove"]').trigger('click');

        images.finish(0, 1600, 534);
        await flushPromises();

        expect(view.emitted('choose')).toBeUndefined();
        expect(view.emitted('remove')).toHaveLength(1);
    });

    it('passes on an image big enough to fill the header', async () => {
        const view = mount(EventBannerField, { props: { banner: null } });
        const file = new File(['bytes'], 'hall.jpg', { type: 'image/jpeg' });

        await choose(view, file);

        expect(view.emitted('choose')).toEqual([[file]]);
    });

    it('leaves the size to the api when the browser cannot read it', async () => {
        images.failToDecode();
        const view = mount(EventBannerField, { props: { banner: null } });
        const file = new File(['bytes'], 'hall.jpg', { type: 'image/jpeg' });

        await choose(view, file);

        expect(view.emitted('choose')).toEqual([[file]]);
    });

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

describe('the timezone field', () => {
    function zonesOf(view: ReturnType<typeof mount>): string[] {
        return view.findAll('[data-testid="event-timezone"] option').map((option) => option.attributes('value') ?? '');
    }

    it('offers the zones the api accepts, narrowed to what is searched for, and sends nothing', async () => {
        const fetch = stubReads();
        const view = mount(TimezoneField, { props: { modelValue: 'Europe/London' }, global: plugins() });
        await flushPromises();

        expect(zonesOf(view)).toEqual(expect.arrayContaining(['Europe/London', 'America/New_York', 'Asia/Tokyo']));

        await view.get('[data-testid="event-timezone-search"]').setValue('new york');

        expect(zonesOf(view)).toContain('America/New_York');
        expect(zonesOf(view)).not.toContain('Asia/Tokyo');

        await view.get('[data-testid="event-timezone"]').setValue('America/New_York');
        await flushPromises();

        expect(view.emitted('update:modelValue')).toEqual([['America/New_York']]);
        expect(writesOf(fetch)).toEqual([]);
    });

    it('keeps the chosen zone in the list whatever is searched for, so the select never loses it', async () => {
        stubReads();
        const view = mount(TimezoneField, { props: { modelValue: 'Europe/London' }, global: plugins() });
        await flushPromises();

        await view.get('[data-testid="event-timezone-search"]').setValue('tokyo');

        expect(zonesOf(view)).toEqual(expect.arrayContaining(['Europe/London', 'Asia/Tokyo']));
        expect(view.get<HTMLSelectElement>('[data-testid="event-timezone"]').element.value).toBe('Europe/London');
    });

    it('shows a fixed zone with the reason, and offers nothing to search', () => {
        stubReads();
        const view = mount(TimezoneField, {
            props: { modelValue: 'Europe/London', fixedReason: 'Fixed because the schedule is written in it.' },
            global: plugins(),
        });

        expect(view.get<HTMLSelectElement>('[data-testid="event-timezone"]').element.disabled).toBe(true);
        expect(view.find('[data-testid="event-timezone-search"]').exists()).toBe(false);
        expect(view.get('[data-testid="event-timezone-fixed"]').text()).toContain('schedule is written in it');
    });
});
