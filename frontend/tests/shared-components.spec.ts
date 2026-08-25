import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { createMemoryHistory, createRouter } from 'vue-router';

import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import AuthCard from '@/components/AuthCard.vue';

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
