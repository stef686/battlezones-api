<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { amendAttendee, removeTeamAvatar, uploadTeamAvatar, type Allegiance, type Attendee } from '@/api/events';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
import SelectField from '@/components/SelectField.vue';
import TeamAvatar from '@/components/TeamAvatar.vue';
import TextField from '@/components/TextField.vue';
import { useMyTeam } from '@/composables/useMyTeam';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const { event, attendee, attendeeId, loading, refresh } = useMyTeam(() => props.eventSlug);

const ALLEGIANCES: { value: Allegiance; label: string }[] = [
  { value: 'loyalist', label: 'Loyalist' },
  { value: 'traitor', label: 'Traitor' },
];

const partyName = ref('');
const allegiance = ref('');

/**
 * Allegiance is a pairing constraint, so it is frozen once the Event is under
 * way — Games already played were paired on it. The field closes rather than
 * accepting a change the API would refuse: being told afterwards is a worse
 * way to learn it than seeing it was never on offer.
 */
const allegianceLocked = computed(() => attendee.value?.allegiance_locked === true);

// The form mirrors what the API last returned rather than holding its own
// idea of the team: a correction made on another device wins on the next read.
watch(attendee, (loaded) => {
  if (loaded === undefined) {
    return;
  }

  partyName.value = loaded.name ?? '';
  allegiance.value = loaded.allegiance ?? '';
}, { immediate: true });

/**
 * The Avatar is its own multipart request, fired the moment a file is chosen
 * rather than held until Save: one button that sometimes makes two requests
 * and can half-succeed is worse than two that each say what they did.
 */
const avatarBusy = ref(false);
const avatarErrors = ref<string[]>([]);

async function chooseAvatar(payload: Event): Promise<void> {
  const input = payload.target as HTMLInputElement;
  const file = input.files?.[0];

  if (file === undefined || attendeeId.value === null || avatarBusy.value) {
    return;
  }

  await withAvatar(() => uploadTeamAvatar(client, props.eventSlug, attendeeId.value as number, file));

  // Cleared so choosing the same file twice still fires a change.
  input.value = '';
}

async function dropAvatar(): Promise<void> {
  if (attendeeId.value !== null && !avatarBusy.value) {
    await withAvatar(() => removeTeamAvatar(client, props.eventSlug, attendeeId.value as number));
  }
}

async function withAvatar(change: () => Promise<Attendee>): Promise<void> {
  avatarBusy.value = true;
  avatarErrors.value = [];

  try {
    await change();
    await refresh();
  } catch (caught) {
    avatarErrors.value = caught instanceof ApiError
      ? (caught.fields['avatar'] ?? [caught.message])
      : ['That could not be uploaded.'];
  } finally {
    avatarBusy.value = false;
  }
}

const saving = ref(false);
const saved = ref(false);
const failure = ref<ApiError | null>(null);

function fieldErrors(field: string): string[] {
  return failure.value?.fields[field] ?? [];
}

async function save(): Promise<void> {
  if (attendeeId.value === null) {
    return;
  }

  saving.value = true;
  saved.value = false;
  failure.value = null;

  try {
    await amendAttendee(client, props.eventSlug, attendeeId.value, {
      name: partyName.value === '' ? null : partyName.value,
      allegiance: allegiance.value === '' ? null : (allegiance.value as Allegiance),
    });

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

    <template v-else-if="attendee && event">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        Team details
      </h1>

      <section class="flex flex-col items-start gap-3">
        <h2 class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          Team avatar
        </h2>

        <div class="flex items-center gap-3">
          <TeamAvatar
            :name="attendee.name ?? ''"
            :src="attendee.avatar"
            size="lg"
          />

          <AppButton
            v-if="attendee.avatar"
            data-testid="remove-team-avatar"
            variant="danger"
            size="sm"
            :disabled="avatarBusy"
            @click="dropAvatar"
          >
            <Trash2 class="size-4 shrink-0" />
            <span class="sr-only">Remove avatar</span>
          </AppButton>
        </div>

        <p class="text-xs text-muted-foreground">
          At least 128 by 128, up to 8MB. JPEG, PNG or WebP.
        </p>

        <input
          type="file"
          accept="image/jpeg,image/png,image/webp"
          data-testid="team-avatar-input"
          :disabled="avatarBusy"
          class="block w-full text-sm text-muted-foreground-1 file:me-4 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground"
          @change="chooseAvatar"
        >

        <AppAlert
          v-if="avatarErrors.length > 0"
          data-testid="team-avatar-error"
          tone="error"
        >
          {{ avatarErrors.join(' ') }}
        </AppAlert>
      </section>

      <form
        class="flex flex-col gap-4"
        novalidate
        @submit.prevent="save"
      >
        <TextField
          v-model="partyName"
          label="Team name"
          testid="team-name-field"
          :errors="fieldErrors('name')"
        />

        <SelectField
          v-if="event.requires_allegiance"
          v-model="allegiance"
          label="Allegiance"
          placeholder="Choose a side"
          testid="team-allegiance"
          :options="ALLEGIANCES"
          :disabled="allegianceLocked"
          :hint="allegianceLocked ? 'Frozen now the event has begun.' : 'Frozen once the event begins.'"
          :errors="fieldErrors('allegiance')"
        />

        <AppAlert
          v-if="failure && failure.kind !== 'validation'"
          data-testid="team-error"
          tone="error"
        >
          {{ failure.message }}
        </AppAlert>

        <AppAlert
          v-if="saved"
          data-testid="team-saved"
          tone="success"
        >
          Saved.
        </AppAlert>

        <AppButton
          type="submit"
          data-testid="save-team"
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
