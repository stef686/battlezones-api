<script setup lang="ts">
/**
 * One Player in a team, as an Organiser repairing that team sees them.
 *
 * A Player who has not answered their invitation is still the team's best
 * guess — a name and an address typed on a phone — so an Organiser corrects
 * any of it here. Correcting the address is how one Player is swapped for
 * another: the seat moves to the new address, Faction and list intact, and the
 * old invitation stops working. A Player who has claimed their account owns
 * their name and address, and the API refuses to change them, so they are
 * reported rather than offered.
 *
 * Every change is its own request, made the moment it is asked for, and the
 * card emits `changed` once the API has accepted it so the screen can reread
 * the team.
 */
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { formFailure, type FormFailure } from '@/api/errors';
import { amendMember, removeMember, resendMemberInvite, type AttendeeMember } from '@/api/events';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';

const props = defineProps<{
  eventSlug: string;
  attendeeId: number;
  member: AttendeeMember;
  factionOptions: { value: string; label: string }[];
  /**
   * Whether this Player may be taken out. Not the last one left: a team with
   * nobody in it is a team to delete or drop, which the screen offers itself.
   */
  removable: boolean;
}>();

const emit = defineEmits<{ changed: [] }>();

const client = useApiClient();

/** Keyed by the seat, which is what every request here addresses. */
const testid = computed(() => `member-${props.member.membership_id}`);

const invited = computed(() => props.member.invite_outstanding === true);

interface PlayerDetails {
  name: string;
  email: string;
  factionId: string;
}

const name = ref('');
const email = ref('');
const factionId = ref('');

const form = computed<PlayerDetails>(() => ({ name: name.value, email: email.value, factionId: factionId.value }));

function sameDetails(left: PlayerDetails | null, right: PlayerDetails): boolean {
  return left !== null && left.name === right.name && left.email === right.email && left.factionId === right.factionId;
}

/**
 * What the form was last filled with from the API. The team is reread after
 * every change to any of its Players, and on window focus, so a reread must
 * not wipe what an Organiser is half-way through typing: the form follows the
 * API only while it still holds what the API last said.
 */
let seeded: PlayerDetails | null = null;

watch(() => props.member, (player) => {
  const latest = {
    name: player.name,
    email: player.email ?? '',
    factionId: player.faction === null ? '' : String(player.faction.id),
  };

  if (seeded === null || sameDetails(seeded, form.value)) {
    name.value = latest.name;
    email.value = latest.email;
    factionId.value = latest.factionId;
  }

  seeded = latest;
}, { immediate: true });

/**
 * Whether saving would send an invitation somewhere new. Compared as the API
 * compares it — trimmed and regardless of case — so the warning appears
 * exactly when a new email is about to go out.
 */
const addressChanged = computed(() => props.member.email !== undefined
  && email.value.trim().toLowerCase() !== props.member.email.toLowerCase());

/**
 * What one request here last reported. Field messages sit under their
 * fields; anything else is said once, including a failure that never reached
 * the API, which would otherwise fail without a word.
 */
const failure = ref<FormFailure | null>(null);

function fieldErrors(field: string): string[] {
  return failure.value?.fields[field] ?? [];
}

const saving = ref(false);

/**
 * What was last saved, so "Saved." describes the form only while the form
 * still holds it: a keystroke afterwards is a change nobody has saved yet.
 */
const savedAs = ref<PlayerDetails | null>(null);
const saved = computed(() => sameDetails(savedAs.value, form.value));

const resending = ref(false);
const resent = ref(false);

async function save(): Promise<void> {
  if (props.member.membership_id === undefined) {
    return;
  }

  const submitted = form.value;

  saving.value = true;
  savedAs.value = null;
  resent.value = false;
  failure.value = null;

  try {
    await amendMember(client, props.eventSlug, props.attendeeId, props.member.membership_id, {
      name: submitted.name,
      email: submitted.email,
      faction_id: submitted.factionId === '' ? null : Number(submitted.factionId),
    });

    // The form now holds what the API holds, so the reread that follows may
    // replace it with the API's own spelling of it.
    seeded = submitted;
    savedAs.value = submitted;
    emit('changed');
  } catch (caught) {
    failure.value = formFailure(caught, 'Their details could not be saved.');
  } finally {
    saving.value = false;
  }
}

/** A fresh invitation to the address on file, for a Player who never answered the first. */
async function resend(): Promise<void> {
  if (props.member.membership_id === undefined) {
    return;
  }

  resending.value = true;
  resent.value = false;
  savedAs.value = null;
  failure.value = null;

  try {
    await resendMemberInvite(client, props.eventSlug, props.attendeeId, props.member.membership_id);
    resent.value = true;
  } catch (caught) {
    failure.value = formFailure(caught, 'The invitation could not be sent.');
  } finally {
    resending.value = false;
  }
}

const confirmingRemove = ref(false);
const removing = ref(false);
const removeFailure = ref<string | null>(null);

async function remove(): Promise<void> {
  removing.value = true;
  removeFailure.value = null;

  try {
    await removeMember(client, props.eventSlug, props.attendeeId, props.member.id);
  } catch (caught) {
    removeFailure.value = caught instanceof Error ? caught.message : 'They could not be removed.';

    return;
  } finally {
    removing.value = false;
  }

  emit('changed');
}
</script>

<template>
  <section
    class="flex flex-col gap-4 rounded-xl border border-card-line bg-card p-5 shadow-2xs"
    :data-testid="testid"
  >
    <form
      v-if="invited"
      class="flex flex-col gap-4"
      novalidate
      :data-testid="`${testid}-form`"
      @submit.prevent="save"
    >
      <p class="text-sm text-muted-foreground-1">
        Has not accepted their invitation yet.
      </p>

      <TextField
        v-model="name"
        label="Name"
        :testid="`${testid}-name`"
        :errors="fieldErrors('name')"
      />

      <TextField
        v-model="email"
        label="Email"
        type="email"
        inputmode="email"
        :testid="`${testid}-email`"
        :hint="addressChanged ? 'A new invitation will be sent to this address, and the old one will stop working.' : undefined"
        :errors="fieldErrors('email')"
      />

      <SelectField
        v-model="factionId"
        label="Faction"
        placeholder="Not decided yet"
        :testid="`${testid}-faction`"
        :options="factionOptions"
        :errors="fieldErrors('faction_id')"
      />

      <AppAlert
        v-if="failure?.message"
        :data-testid="`${testid}-error`"
        tone="error"
      >
        {{ failure.message }}
      </AppAlert>

      <AppAlert
        v-if="saved"
        :data-testid="`${testid}-saved`"
        tone="success"
      >
        Saved.
      </AppAlert>

      <AppButton
        type="submit"
        :data-testid="`${testid}-save`"
        :disabled="saving"
        block
      >
        {{ saving ? 'Saving…' : 'Save player' }}
      </AppButton>

      <AppButton
        :data-testid="`${testid}-resend`"
        variant="secondary"
        :disabled="resending"
        block
        @click="resend"
      >
        {{ resending ? 'Sending…' : 'Send the invitation again' }}
      </AppButton>

      <AppAlert
        v-if="resent"
        :data-testid="`${testid}-resent`"
        tone="success"
      >
        Sent. Their earlier link has stopped working.
      </AppAlert>
    </form>

    <template v-else>
      <p class="text-sm font-medium text-foreground">
        {{ member.name }}
      </p>
      <p class="text-sm text-muted-foreground-1">
        {{ member.faction?.name ?? 'Faction not chosen' }}
      </p>
      <p class="text-sm text-muted-foreground">
        They have their own account, so only they can change their details.
      </p>
    </template>

    <template v-if="removable">
      <AppButton
        :data-testid="`${testid}-remove`"
        variant="danger"
        :disabled="removing"
        block
        @click="confirmingRemove = true"
      >
        Remove from the team
      </AppButton>

      <AppAlert
        v-if="removeFailure"
        :data-testid="`${testid}-remove-error`"
        tone="error"
      >
        {{ removeFailure }}
      </AppAlert>

      <ConfirmDialog
        v-model:open="confirmingRemove"
        :title="`Remove ${member.name}?`"
        confirm-label="Remove"
        :testid="`${testid}-remove-confirm`"
        @confirm="remove"
      >
        They leave this team. Their account is not touched, and they can be added again.
      </ConfirmDialog>
    </template>
  </section>
</template>
