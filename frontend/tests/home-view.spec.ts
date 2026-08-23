import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import HomeView from '@/views/HomeView.vue';

describe('HomeView', () => {
    it('renders the API it is built against', () => {
        const wrapper = mount(HomeView);

        expect(wrapper.text()).toContain('Battlezones');
        expect(wrapper.text()).toContain(import.meta.env.VITE_API_URL);
    });
});
