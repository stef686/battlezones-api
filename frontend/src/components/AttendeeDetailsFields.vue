<script setup lang="ts">
/**
 * An Attendee's name and Allegiance, as a Player edits their own team and an
 * Organiser edits anybody's.
 *
 * One component so the two screens cannot drift: the Allegiance choices, the
 * lock once a Round is Live and the words explaining it are the same rule
 * whoever is holding the phone.
 */
import type { Allegiance } from '@/api/events';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';

withDefaults(defineProps<{
  /** Whether the Event opposes Attendees, which is the only time a side is asked for. */
  requiresAllegiance: boolean;
  /**
   * Allegiance is a pairing constraint, so it is frozen once the Event is
   * under way — Games already played were paired on it. The field closes
   * rather than accepting a change the API would refuse.
   */
  allegianceLocked?: boolean;
  errors?: { name?: string[]; allegiance?: string[] };
}>(), {
  allegianceLocked: false,
  errors: () => ({}),
});

const name = defineModel<string>('name', { required: true });
const allegiance = defineModel<string>('allegiance', { required: true });

const ALLEGIANCES: { value: Allegiance; label: string }[] = [
  { value: 'loyalist', label: 'Loyalist' },
  { value: 'traitor', label: 'Traitor' },
];
</script>

<template>
  <TextField
    v-model="name"
    label="Team name"
    testid="team-name-field"
    :errors="errors.name ?? []"
  />

  <SelectField
    v-if="requiresAllegiance"
    v-model="allegiance"
    label="Allegiance"
    placeholder="Choose a side"
    testid="team-allegiance"
    :options="ALLEGIANCES"
    :disabled="allegianceLocked"
    :hint="allegianceLocked ? 'Frozen now the event has begun.' : 'Frozen once the event begins.'"
    :errors="errors.allegiance ?? []"
  />
</template>
