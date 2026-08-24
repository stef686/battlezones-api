import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import UpdateBanner from '@/components/UpdateBanner.vue';

describe('the new-version banner', () => {
    it('stays out of the way until there is a new version', () => {
        const view = mount(UpdateBanner, { props: { available: false } });

        expect(view.find('[data-testid="update-available"]').exists()).toBe(false);
    });

    it('offers the reload, and leaves taking it to the reader', async () => {
        const view = mount(UpdateBanner, { props: { available: true } });

        expect(view.get('[data-testid="update-available"]').text()).toContain('new version');

        // Never automatic: a Player halfway through submitting a result must
        // not have the page swapped under them.
        expect(view.emitted('reload')).toBeUndefined();

        await view.get('[data-testid="take-update"]').trigger('click');

        expect(view.emitted('reload')).toHaveLength(1);
    });

    it('can be dismissed, because the reader may be in the middle of something', async () => {
        const view = mount(UpdateBanner, { props: { available: true } });

        await view.get('[data-testid="dismiss-update"]').trigger('click');

        expect(view.find('[data-testid="update-available"]').exists()).toBe(false);
        expect(view.emitted('reload')).toBeUndefined();
    });
});
