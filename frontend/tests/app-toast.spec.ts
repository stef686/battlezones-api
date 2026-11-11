import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';

import AppToast from '@/components/AppToast.vue';
import { useToastStore } from '@/stores/toast';

let pinia: ReturnType<typeof createPinia>;

beforeEach(() => {
    vi.useFakeTimers();
    pinia = createPinia();
    setActivePinia(pinia);
});

afterEach(() => {
    vi.useRealTimers();
});

describe('the toast', () => {
    it('says what just happened, politely, once a screen reports it', async () => {
        const view = mount(AppToast, { global: { plugins: [pinia] } });

        expect(view.find('[data-testid="toast"]').exists()).toBe(false);

        useToastStore().show('Event created');
        await nextTick();

        expect(view.get('[data-testid="toast"]').text()).toBe('Event created');
        // The region is there before the message is, which is what makes a
        // screen reader announce the message when it lands.
        expect(view.get('[data-testid="toast-region"]').attributes('role')).toBe('status');
    });

    it('goes of its own accord, so nobody has to dismiss good news', async () => {
        const view = mount(AppToast, { global: { plugins: [pinia] } });

        useToastStore().show('Event created');
        await nextTick();

        vi.advanceTimersByTime(5000);
        await nextTick();

        expect(view.find('[data-testid="toast"]').exists()).toBe(false);
    });

    it('gives a second message its full time rather than the first one\'s remainder', async () => {
        const view = mount(AppToast, { global: { plugins: [pinia] } });
        const toasts = useToastStore();

        toasts.show('Saved');
        vi.advanceTimersByTime(4000);
        toasts.show('Event created');
        await nextTick();
        vi.advanceTimersByTime(4000);
        await nextTick();

        expect(view.get('[data-testid="toast"]').text()).toBe('Event created');
    });
});
