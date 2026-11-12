<script setup lang="ts">
/**
 * An Organiser filling an empty seat in a team: a Player who never turned up
 * replaced, or a partner named on the day.
 *
 * The new Player is named by address, because most have no account when they
 * are named: the API invites them, which is what creates one. Organisers are
 * not held to the registration window, so this works mid-Event too. Emits
 * `added` once the API has accepted them, so the screen can reread the team.
 */
import { ref } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { addMember } from '@/api/events';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';

const props = defineProps<{
  eventSlug: string;
  attendeeId: number;
  factionOptions: { value: string; label: string }[];
}>();

const emit = defineEmits<{ added: [] }>();

const client = useApiClient();

const name = ref('');
const email = ref('');
const factionId = ref('');

const adding = ref(false);
const failure = ref<ApiError | null>(null);

function fieldErrors(field: string): string[] {
  return failure.value?.fields[field] ?? [];
}

async function add(): Promise<void> {
  adding.value = true;
  failure.value = null;

  try {
    await addMember(client, props.eventSlug, props.attendeeId, {
      name: name.value,
      email: email.value,
      faction_id: factionId.value === '' ? null : Number(factionId.value),
    });
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;

    return;
  } finally {
    adding.value = false;
  }

  name.value = '';
  email.value = '';
  factionId.value = '';
  emit('added');
}
</script>

<template>
  <form
    class="flex flex-col gap-4 rounded-xl border border-card-line bg-card p-5 shadow-2xs"
    novalidate
    data-testid="add-player-form"
    @submit.prevent="add"
  >
    <h3 class="text-sm font-medium text-foreground">
      Add a player
    </h3>

    <TextField
      v-model="name"
      label="Name"
      testid="add-player-name"
      :errors="fieldErrors('name')"
    />

    <TextField
      v-model="email"
      label="Email"
      type="email"
      inputmode="email"
      testid="add-player-email"
      hint="Without an account, they are sent an invitation to this address."
      :errors="fieldErrors('email')"
    />

    <SelectField
      v-model="factionId"
      label="Faction"
      placeholder="Not decided yet"
      testid="add-player-faction"
      :options="factionOptions"
      :errors="fieldErrors('faction_id')"
    />

    <AppAlert
      v-if="failure && failure.kind !== 'validation'"
      data-testid="add-player-error"
      tone="error"
    >
      {{ failure.message }}
    </AppAlert>

    <AppButton
      type="submit"
      data-testid="add-player"
      :disabled="adding"
      block
    >
      {{ adding ? 'Adding…' : 'Add player' }}
    </AppButton>
  </form>
</template>
