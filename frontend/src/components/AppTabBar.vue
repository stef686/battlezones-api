<script setup lang="ts">
/**
 * The app's global nav, and the only chrome that reaches beyond an Event.
 *
 * Home, Events and Messages are where the app is going; the Event's own
 * sections are the Event nav's job now. Those three ship visibly inert rather
 * than hidden: telegraphing what is coming is honest, and a bar that grows a
 * slot later would move the others under a Player's thumb.
 *
 * The slots are fixed for exactly that reason. Dropping one mid-round means a
 * tap aimed at Messages lands on Home, so all four are always drawn whoever
 * is looking.
 *
 * The avatar is the load-bearing slot. Signed out, it is the only chrome that
 * routes to signing in; a signed-out viewer would otherwise have to hit a
 * guarded route and be bounced to find the login screen. Signed in, it draws
 * the viewer's initials and opens the account drawer, which is the only way
 * to log out.
 *
 * That is why the bar shows at every width. It was built for a phone, but
 * until there is desktop chrome of its own (#143) a bar hidden on a laptop
 * would leave nobody there able to sign in or out.
 *
 * The slots are icons alone. Their labels stay in the markup as `sr-only`,
 * because an icon with no accessible name is a mystery to a screen reader —
 * and because the account slot's name is the viewer's own, which no icon can
 * say. Do not delete the labels to save the markup.
 */
import { CalendarDays, CircleUser, House, Send, type LucideIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';

import AccountDrawer from '@/components/AccountDrawer.vue';
import AppAvatar from '@/components/AppAvatar.vue';
import { useSessionStore } from '@/stores/session';

interface Slot {
  key: string;
  label: string;
  icon: LucideIcon;
}

const session = useSessionStore();

/** The three destinations with no screen behind them yet. */
const coming: Slot[] = [
  { key: 'home', label: 'Home', icon: House },
  { key: 'events', label: 'Events', icon: CalendarDays },
  { key: 'messages', label: 'Messages', icon: Send },
];

const viewer = computed(() => session.viewer);

/** Whether the signed-in viewer's account drawer is showing. */
const drawerOpen = ref(false);

const SLOT_CLASSES = 'flex flex-col items-center px-1 py-3.5';
</script>

<template>
  <nav
    data-testid="tab-bar"
    aria-label="Battlezones"
    class="fixed inset-x-0 bottom-0 z-40 border-t border-navbar-line bg-navbar pb-[env(safe-area-inset-bottom)]"
  >
    <ul class="mx-auto flex w-full max-w-md items-stretch">
      <li
        v-for="slot in coming"
        :key="slot.key"
        class="flex-1"
      >
        <!-- Not a link and not focusable: there is nowhere to go yet, and a
             focus stop that does nothing is worse than no focus stop. -->
        <span
          :data-testid="`tab-${slot.key}`"
          aria-disabled="true"
          tabindex="-1"
          :class="[SLOT_CLASSES, 'text-muted-foreground opacity-60']"
        >
          <component
            :is="slot.icon"
            class="size-6 shrink-0"
          />
          <span class="sr-only">{{ slot.label }}</span>
        </span>
      </li>

      <li class="flex-1">
        <RouterLink
          v-if="viewer === null"
          :to="{ name: 'login' }"
          data-testid="tab-account"
          :class="[SLOT_CLASSES, 'text-muted-foreground-1 hover:text-navbar-nav-foreground focus:text-navbar-nav-foreground focus:outline-hidden']"
        >
          <CircleUser class="size-6 shrink-0" />
          <span class="sr-only">Sign in</span>
        </RouterLink>

        <button
          v-else
          type="button"
          data-testid="tab-account"
          aria-haspopup="dialog"
          :aria-expanded="drawerOpen"
          :class="[SLOT_CLASSES, 'w-full text-navbar-nav-foreground focus:outline-hidden']"
          @click="drawerOpen = true"
        >
          <AppAvatar
            :name="viewer.public_name"
            size="xs"
          />
          <span class="sr-only">{{ viewer.public_name }}</span>
        </button>
      </li>
    </ul>
  </nav>

  <AccountDrawer
    v-if="viewer !== null"
    v-model:open="drawerOpen"
    :viewer="viewer"
  />
</template>
