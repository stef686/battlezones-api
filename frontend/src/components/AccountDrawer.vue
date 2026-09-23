<script setup lang="ts">
/**
 * The signed-in User's own corner of the app, opened from the tab bar's
 * avatar and sliding in from the right.
 *
 * It is a native `<dialog>` opened with `showModal()`, so the browser owns
 * the focus trap, Esc, the inert page behind it and handing focus back to the
 * avatar on close. Vue owns only whether it is open.
 *
 * The close button takes the opening focus. With every destination inert,
 * the browser would otherwise hand it to Log out, and one stray tap on Enter
 * would sign the reader out.
 *
 * "Player" and "Organiser" head the sections as copy, not as roles: there is
 * no platform-wide Player or Organiser, and every User sees every section.
 * An unclaimed viewer is told, before they tap Log out, that the invite email
 * is their only way back in, since they have no password to sign in with.
 * The warning does not stand in the way of logging out.
 *
 * The destinations ship visibly inert, as the tab bar's do, until their
 * screens exist.
 */
import {
    CalendarDays,
    CalendarPlus,
    ClipboardList,
    IdCard,
    Settings,
    Shield,
    Users,
    X,
    type LucideIcon,
} from 'lucide-vue-next';
import { ref, useId, watch } from 'vue';
import { RouterLink } from 'vue-router';

import AppAvatar from '@/components/AppAvatar.vue';
import AppButton from '@/components/AppButton.vue';
import { useLogout } from '@/composables/useLogout';
import type { Viewer } from '@/stores/session';

interface Destination {
    label: string;
    icon: LucideIcon;
}

interface Section {
    heading: string;
    destinations: Destination[];
}

defineProps<{ viewer: Viewer }>();

const SECTIONS: Section[] = [
    {
        heading: 'Player',
        destinations: [
            { label: 'Events', icon: CalendarDays },
            { label: 'Clubs', icon: Shield },
            { label: 'Friends', icon: Users },
        ],
    },
    {
        heading: 'Organiser',
        destinations: [
            { label: 'Events', icon: ClipboardList },
            { label: 'Run an event', icon: CalendarPlus },
        ],
    },
    {
        heading: 'Account',
        destinations: [
            { label: 'Details', icon: IdCard },
            { label: 'Settings', icon: Settings },
        ],
    },
];

const headingIdPrefix = useId();

const logout = useLogout();

async function logOut(): Promise<void> {
    open.value = false;
    await logout();
}

const open = defineModel<boolean>('open', { required: true });

const dialog = ref<HTMLDialogElement | null>(null);

watch(open, (isOpen) => {
    if (isOpen) {
        dialog.value?.showModal();
    } else {
        dialog.value?.close();
    }
});
</script>

<template>
  <dialog
    ref="dialog"
    data-testid="account-drawer"
    aria-label="Account"
    class="fixed inset-y-0 right-0 left-auto m-0 h-dvh max-h-none w-80 max-w-[85vw] border-l border-overlay-line bg-overlay p-0 text-foreground transition-transform duration-200 ease-out backdrop:bg-drawer-backdrop starting:open:translate-x-full"
    @close="open = false"
    @click.self="open = false"
  >
    <!-- Fills the dialog edge to edge, so a click that reaches the dialog
         itself can only have landed on the backdrop. -->
    <div class="flex h-full flex-col">
      <div class="flex items-start gap-3 border-b border-overlay-divider px-5 pt-[calc(env(safe-area-inset-top)+1.25rem)] pb-5">
        <AppAvatar
          :name="viewer.public_name"
          size="sm"
        />
        <div class="min-w-0 flex-1">
          <p
            data-testid="account-drawer-name"
            class="truncate font-semibold"
          >
            {{ viewer.public_name }}
          </p>
          <p
            v-if="!viewer.is_claimed"
            data-testid="account-drawer-unclaimed"
            class="mt-1 text-sm text-muted-foreground-1"
          >
            You haven't set a password yet — you'll need your invite email to get back in.
            <RouterLink
              :to="{ name: 'claim' }"
              class="font-medium text-foreground underline"
              @click="open = false"
            >
              Set a password
            </RouterLink>
          </p>
          <AppButton
            variant="secondary"
            size="sm"
            data-testid="account-drawer-logout"
            class="mt-2"
            @click="logOut"
          >
            Log out
          </AppButton>
        </div>
        <AppButton
          variant="ghost"
          size="sm"
          data-testid="account-drawer-close"
          autofocus
          @click="open = false"
        >
          <X class="size-5 shrink-0" />
          <span class="sr-only">Close</span>
        </AppButton>
      </div>

      <nav
        aria-label="Account"
        class="flex-1 overflow-y-auto px-5 py-4"
      >
        <section
          v-for="(section, index) in SECTIONS"
          :key="section.heading"
          data-testid="account-drawer-section"
          class="py-2"
        >
          <h2
            :id="`${headingIdPrefix}-${index}`"
            class="pb-1 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
          >
            {{ section.heading }}
          </h2>
          <ul :aria-labelledby="`${headingIdPrefix}-${index}`">
            <li
              v-for="destination in section.destinations"
              :key="destination.label"
            >
              <!-- Not a link and not focusable: there is nowhere to go yet. -->
              <span
                aria-disabled="true"
                tabindex="-1"
                class="flex items-center gap-3 py-2.5 text-muted-foreground opacity-60"
              >
                <component
                  :is="destination.icon"
                  class="size-5 shrink-0"
                />
                {{ destination.label }}
              </span>
            </li>
          </ul>
        </section>
      </nav>
    </div>
  </dialog>
</template>
