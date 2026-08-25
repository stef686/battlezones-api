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
    class="flex items-center justify-between gap-3 border-b border-border bg-card px-4 py-3 text-sm"
  >
    <p class="text-muted-foreground-1">
      You are in without a password. Set one to keep this account.
    </p>

    <RouterLink
      :to="{ name: 'claim' }"
      data-testid="claim-prompt-link"
      class="inline-flex shrink-0 items-center rounded-lg border border-transparent bg-primary px-3 py-2 font-medium text-primary-foreground hover:bg-primary-hover focus:bg-primary-focus focus:outline-hidden"
    >
      Set one
    </RouterLink>
  </div>
</template>
