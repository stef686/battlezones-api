<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchFactions, recordMyFaction } from '@/api/events';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
import SelectField from '@/components/SelectField.vue';
import { useMyTeam } from '@/composables/useMyTeam';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const { attendee, me, loading, refresh } = useMyTeam(() => props.eventSlug);

const { data: factions } = useQuery({
  queryKey: computed(() => keys.factions(props.eventSlug)),
  queryFn: () => fetchFactions(client, props.eventSlug),
  retry: false,
});

const factionOptions = computed(() => (factions.value ?? []).map((faction) => ({
  value: String(faction.id),
  label: faction.name,
})));

const myFactionId = ref('');

watch(me, (member) => {
  myFactionId.value = member?.faction === null || member?.faction === undefined ? '' : String(member.faction.id);
}, { immediate: true });

const saving = ref(false);
const saved = ref(false);
const failure = ref<ApiError | null>(null);

async function save(): Promise<void> {
  saving.value = true;
  saved.value = false;
  failure.value = null;

  try {
    await recordMyFaction(client, props.eventSlug, myFactionId.value === '' ? null : Number(myFactionId.value));
    await refresh();
    saved.value = true;
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <BackLink
      :to="{ name: 'my-team', params: { eventSlug: props.eventSlug } }"
      testid="back-to-my-team"
    >
      Back to my team
    </BackLink>

    <p
      v-if="loading"
      class="text-muted-foreground-1"
    >
      Loading your team…
    </p>

    <template v-else-if="attendee">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        My details
      </h1>

      <form
        class="flex flex-col gap-4"
        novalidate
        @submit.prevent="save"
      >
        <!-- The Faction is the Player's own, not the party's: a doubles team
             fields two of them under one Allegiance. -->
        <SelectField
          v-model="myFactionId"
          label="Your faction"
          placeholder="Not decided yet"
          testid="my-faction"
          :options="factionOptions"
          :errors="failure?.fields['faction_id'] ?? []"
        />

        <AppAlert
          v-if="failure && failure.kind !== 'validation'"
          data-testid="faction-error"
          tone="error"
        >
          {{ failure.message }}
        </AppAlert>

        <AppAlert
          v-if="saved"
          data-testid="faction-saved"
          tone="success"
        >
          Saved.
        </AppAlert>

        <AppButton
          type="submit"
          data-testid="save-faction"
          :disabled="saving"
          block
          class="mt-2"
        >
          {{ saving ? 'Saving…' : 'Save' }}
        </AppButton>
      </form>
    </template>
  </main>
</template>
