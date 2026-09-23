<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { addMember, amendMember, fetchFactions, resendMemberInvite } from '@/api/events';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';
import { useMyTeam } from '@/composables/useMyTeam';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const { attendee, attendeeId, partner, loading, refresh } = useMyTeam(() => props.eventSlug);

const { data: factions } = useQuery({
  queryKey: computed(() => keys.factions(props.eventSlug)),
  queryFn: () => fetchFactions(client, props.eventSlug),
  retry: false,
});

const factionOptions = computed(() => (factions.value ?? []).map((faction) => ({
  value: String(faction.id),
  label: faction.name,
})));

/**
 * A partner is editable only until they answer their invitation.
 *
 * Their name and address are the Captain's best guess until then — typed from
 * memory into a phone — and nobody else can put a mistake right. The moment
 * they claim the account it is theirs, and the API refuses this too.
 */
const editable = computed(() => partner.value === null || partner.value.invite_outstanding === true);

const name = ref('');
const email = ref('');
const factionId = ref('');

// The address is shown as it stands rather than left blank: the reason to
// open this screen is usually that it was typed wrong, and a Captain cannot
// correct what they cannot see. The API sends it only while the invitation is
// outstanding, which is exactly while it is still the team's to change.
watch(partner, (mate) => {
  name.value = mate?.name ?? '';
  email.value = mate?.email ?? '';
  factionId.value = mate?.faction === null || mate?.faction === undefined ? '' : String(mate.faction.id);
}, { immediate: true });

/**
 * Whether saving would reissue the invitation. Compared the way the API
 * compares it — trimmed and regardless of case — so the notice appears exactly
 * when a new email is about to go out, and not for a stray capital.
 */
const addressChanged = computed(() => {
  const onFile = partner.value?.email;

  return onFile !== undefined && onFile !== null
    && email.value.trim().toLowerCase() !== onFile.toLowerCase();
});

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

  const faction = factionId.value === '' ? null : Number(factionId.value);

  try {
    const mate = partner.value;

    if (mate === null) {
      await addMember(client, props.eventSlug, attendeeId.value, {
        name: name.value,
        email: email.value,
        faction_id: faction,
      });
    } else if (mate.membership_id !== undefined) {
      // The address goes back whether or not it changed; the API reissues the
      // invitation only when it actually differs from the one on file.
      await amendMember(client, props.eventSlug, attendeeId.value, mate.membership_id, {
        name: name.value,
        email: email.value,
        faction_id: faction,
      });
    }

    await refresh();
    saved.value = true;
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;
  } finally {
    saving.value = false;
  }
}

const resending = ref(false);
const resent = ref(false);

async function resend(): Promise<void> {
  const mate = partner.value;

  if (attendeeId.value === null || mate === null || mate.membership_id === undefined) {
    return;
  }

  resending.value = true;
  resent.value = false;
  failure.value = null;

  try {
    await resendMemberInvite(client, props.eventSlug, attendeeId.value, mate.membership_id);
    resent.value = true;
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;
  } finally {
    resending.value = false;
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
        Partner
      </h1>

      <!-- Answered their invitation: everything here is theirs to change now,
           so the screen reports rather than offers. -->
      <template v-if="partner && !editable">
        <div
          data-testid="partner-details"
          class="flex flex-col gap-3 rounded-xl border border-card-line bg-card p-5 shadow-2xs"
        >
          <p class="text-sm font-medium text-foreground">
            {{ partner.name }}
          </p>
          <p class="text-sm text-muted-foreground-1">
            {{ partner.faction?.name ?? 'Faction not chosen' }}
          </p>
          <p
            class="text-sm"
            :class="partner.army_list_locked ? 'text-success' : 'text-muted-foreground'"
          >
            {{ partner.army_list_locked ? 'List in' : 'List not submitted' }}
          </p>
        </div>

        <p class="text-sm text-muted-foreground">
          They have their own account now. They choose their own faction and send their own list.
        </p>
      </template>

      <template v-else>
        <p
          data-testid="partner-state"
          class="text-sm text-muted-foreground-1"
        >
          {{ partner
            ? 'They have not accepted their invitation, so you can still edit their details.'
            : 'Name your partner and we will email them an invitation.' }}
        </p>

        <form
          class="flex flex-col gap-4"
          novalidate
          @submit.prevent="save"
        >
          <TextField
            v-model="name"
            label="Their name"
            testid="partner-name"
            :errors="fieldErrors('name')"
          />

          <TextField
            v-model="email"
            label="Their email address"
            type="email"
            testid="partner-email"
            :hint="addressChanged ? 'A new invitation will be sent to this new email address.' : undefined"
            :errors="fieldErrors('email')"
          />

          <SelectField
            v-model="factionId"
            label="Their faction"
            placeholder="Not decided yet"
            testid="partner-faction"
            :options="factionOptions"
            :errors="fieldErrors('faction_id')"
          />

          <AppAlert
            v-if="failure && failure.kind !== 'validation'"
            data-testid="partner-error"
            tone="error"
          >
            {{ failure.message }}
          </AppAlert>

          <AppAlert
            v-if="saved"
            data-testid="partner-saved"
            tone="success"
          >
            Saved.
          </AppAlert>

          <AppButton
            type="submit"
            data-testid="save-partner"
            :disabled="saving"
            block
            class="mt-2"
          >
            {{ saving ? 'Saving…' : (partner ? 'Save' : 'Invite them') }}
          </AppButton>
        </form>

        <template v-if="partner">
          <AppButton
            data-testid="resend-invite"
            variant="secondary"
            :disabled="resending"
            block
            @click="resend"
          >
            {{ resending ? 'Sending…' : 'Send the invitation again' }}
          </AppButton>

          <AppAlert
            v-if="resent"
            data-testid="invite-resent"
            tone="success"
          >
            Sent. Their earlier link has stopped working.
          </AppAlert>
        </template>
      </template>
    </template>
  </main>
</template>
