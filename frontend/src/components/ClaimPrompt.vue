<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

import { useSessionStore } from '@/stores/session';

/**
 * The standing offer to keep the account.
 *
 * An invited session works without a password, so nothing forces the issue —
 * which means without this, a Player finishes the Event and loses the account
 * when the Invite expires. It is a prompt everywhere rather than a wall
 * anywhere: the door stays open, the sign stays up.
 */
const session = useSessionStore();
const route = useRoute();

const visible = computed(() => session.isUnclaimed && session.invite !== null && route.name !== 'claim');
</script>

<template>
  <div
    v-if="visible"
    data-testid="claim-prompt"
    class="flex items-center justify-between gap-3 border-b border-border bg-surface-raised px-4 py-3"
  >
    <p class="text-sm text-ink-muted">
      You are in without a password. Set one to keep this account.
    </p>

    <RouterLink
      :to="{ name: 'claim' }"
      data-testid="claim-prompt-link"
      class="shrink-0 rounded-lg bg-accent px-3 py-2 text-sm font-semibold text-accent-ink"
    >
      Set one
    </RouterLink>
  </div>
</template>
