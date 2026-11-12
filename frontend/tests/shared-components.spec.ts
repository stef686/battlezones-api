import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { createMemoryHistory, createRouter } from 'vue-router';

import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import AuthCard from '@/components/AuthCard.vue';
import TabStrip from '@/components/TabStrip.vue';
import TextField from '@/components/TextField.vue';

function router() {
    return createRouter({
        history: createMemoryHistory(),
        routes: [{ path: '/', name: 'home', component: { template: '<p />' } }],
    });
}

describe('the button', () => {
    it('is a button when it does something', () => {
        const view = mount(AppButton, { props: { type: 'submit' }, slots: { default: 'Save' } });

        expect(view.element.tagName).toBe('BUTTON');
        expect(view.attributes('type')).toBe('submit');
        expect(view.text()).toBe('Save');
    });

    it('is a real anchor when it goes somewhere, so it can be opened the way a link can', async () => {
        const routes = router();
        await routes.push('/');

        const view = mount(AppButton, {
            props: { to: { name: 'home' } },
            slots: { default: 'Home' },
            global: { plugins: [routes] },
        });

        expect(view.element.tagName).toBe('A');
        expect(view.attributes('href')).toBe('/');
    });

    it('refuses the click when disabled', async () => {
        const view = mount(AppButton, { props: { disabled: true }, slots: { default: 'Save' } });

        await view.trigger('click');

        expect(view.attributes('disabled')).toBeDefined();
    });

    it('carries a different surface per variant', () => {
        const primary = mount(AppButton, { slots: { default: 'a' } });
        const danger = mount(AppButton, { props: { variant: 'danger' }, slots: { default: 'a' } });

        expect(primary.classes()).toContain('bg-primary');
        expect(danger.classes()).toContain('bg-destructive');
    });
});

describe('the alert', () => {
    it('interrupts for a failure, because the reader is waiting on it', () => {
        const view = mount(AppAlert, { props: { tone: 'error' }, slots: { default: 'It broke.' } });

        expect(view.attributes('role')).toBe('alert');
        expect(view.text()).toBe('It broke.');
    });

    it('stays polite for anything that merely confirms', () => {
        expect(mount(AppAlert, { props: { tone: 'success' } }).attributes('role')).toBe('status');
        expect(mount(AppAlert).attributes('role')).toBe('status');
    });
});

describe('the auth card', () => {
    it('heads the card and draws what it is given', () => {
        const view = mount(AuthCard, {
            props: { title: 'Set a password', subtitle: 'Keeps your account.' },
            slots: { default: '<p data-testid="body">a form</p>' },
        });

        expect(view.get('h1').text()).toBe('Set a password');
        expect(view.text()).toContain('Keeps your account.');
        expect(view.get('[data-testid="body"]').text()).toBe('a form');
    });

    it('leaves the footer rule off when there is nothing under the form', () => {
        const bare = mount(AuthCard, { props: { title: 'Battlezones' } });
        const footed = mount(AuthCard, { props: { title: 'Battlezones' }, slots: { footer: '<a>Help</a>' } });

        expect(bare.findAll('.border-t')).toHaveLength(0);
        expect(footed.findAll('.border-t')).toHaveLength(1);
    });
});

describe('the text field', () => {
    it('becomes a labelled textarea given rows, with its errors still announced', () => {
        const view = mount(TextField, {
            props: {
                modelValue: '',
                label: 'Army list',
                rows: 6,
                testid: 'army-list',
                hint: 'Optional.',
                errors: ['The army list is too long.'],
            },
        });

        const area = view.get('textarea[data-testid="army-list"]');
        const label = view.get('label');

        expect(area.attributes('rows')).toBe('6');
        expect(label.attributes('for')).toBe(area.attributes('id'));
        expect(area.attributes('aria-invalid')).toBe('true');

        const describedBy = (area.attributes('aria-describedby') ?? '').split(' ');
        expect(describedBy).toContain(view.get('[data-testid="army-list-error"]').attributes('id'));
        expect(describedBy).toContain(view.get('[data-testid="army-list-hint"]').attributes('id'));
        expect(view.find('input').exists()).toBe(false);
    });
});

describe('the tab strip', () => {
    const ITEMS = [
        { id: 'a', name: 'First' },
        { id: 'b', name: 'Second', disabled: true },
        { id: 'c', name: 'Third' },
        { id: 'd', name: 'Fourth', disabled: true },
    ];

    function mountStrip(selected = 0) {
        return mount(TabStrip as never, ({
            props: {
                items: ITEMS,
                label: 'Things',
                idPrefix: 'things',
                modelValue: selected,
                'onUpdate:modelValue': () => undefined,
            },
            attachTo: document.body,
        }) as never);
    }

    function emitted(strip: ReturnType<typeof mountStrip>): number[] {
        return (strip.emitted('update:modelValue') ?? []).map(([index]) => index as number);
    }

    it('marks a disabled tab as such, and ignores a click on it', async () => {
        const strip = mountStrip();
        const second = strip.get('[data-testid="things-tab-b"]');

        expect(second.attributes('aria-disabled')).toBe('true');
        expect(strip.get('[data-testid="things-tab-a"]').attributes('aria-disabled')).toBeUndefined();

        await second.trigger('click');

        expect(emitted(strip)).toEqual([]);
    });

    it('steps over disabled tabs on the arrow keys, Home and End', async () => {
        const strip = mountStrip();
        const tabs = strip.get('[role="tablist"]');

        await tabs.trigger('keydown', { key: 'ArrowRight' });
        await tabs.trigger('keydown', { key: 'ArrowLeft' });
        await tabs.trigger('keydown', { key: 'End' });

        expect(emitted(strip)).toEqual([2, 2, 2]);

        strip.unmount();
    });

    it('wraps past disabled tabs at either end', async () => {
        const strip = mountStrip(2);
        const tabs = strip.get('[role="tablist"]');

        await tabs.trigger('keydown', { key: 'ArrowRight' });
        await tabs.trigger('keydown', { key: 'Home' });

        expect(emitted(strip)).toEqual([0, 0]);

        strip.unmount();
    });

    it('opens no panel when every tab is disabled', () => {
        const strip = mount(TabStrip as never, ({
            props: { items: ITEMS.map((item) => ({ ...item, disabled: true })), label: 'Things', idPrefix: 'things', modelValue: -1 },
        }) as never);

        expect(strip.find('[role="tabpanel"]').exists()).toBe(false);
        expect(strip.findAll('[aria-selected="true"]')).toHaveLength(0);
    });
});
