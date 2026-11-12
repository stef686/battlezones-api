<script setup lang="ts">
/**
 * The party being entered: its name, its Allegiance where the Event opposes
 * them, and one row per Player.
 *
 * Shared by a Player registering their own team and an Organiser adding one,
 * so the two forms cannot drift. The `voice` is the only difference. A Player
 * is the first row, with their own address fixed, because registering a party
 * is entering it. An Organiser fills in every row — they need not be playing —
 * and may record each Player's army list as they take the entry.
 */
import { computed } from 'vue';

import type { Allegiance } from '@/api/events';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';

export interface PartyPlayer {
  name: string;
  email: string;
  factionId: string;
  armyList: string;
}

const props = withDefaults(defineProps<{
  voice: 'player' | 'organiser';
  /** Whether the Event opposes Attendees, which is the only time a side is asked for. */
  requiresAllegiance: boolean;
  factionOptions: { value: string; label: string }[];
  /** The API's validation messages keyed by field, `players.1.email` and all. */
  errors?: Record<string, string[]>;
}>(), {
  errors: () => ({}),
});

const name = defineModel<string>('name', { required: true });
const allegiance = defineModel<string>('allegiance', { required: true });
const players = defineModel<PartyPlayer[]>('players', { required: true });

const ALLEGIANCES: { value: Allegiance; label: string }[] = [
  { value: 'loyalist', label: 'Loyalist' },
  { value: 'traitor', label: 'Traitor' },
];

const organising = computed(() => props.voice === 'organiser');

function fieldErrors(field: string): string[] {
  return props.errors[field] ?? [];
}

/** The registering Player's own row, which is theirs and not editable. */
function isOwnRow(index: number): boolean {
  return !organising.value && index === 0;
}
</script>

<template>
  <section class="flex flex-col gap-4">
    <TextField
      v-model="name"
      label="Team name"
      testid="party-name"
      :hint="organising ? 'Optional. Left blank, the players\' names are used.' : 'Optional. Left blank, your names are used.'"
      :errors="fieldErrors('name')"
    />

    <SelectField
      v-if="requiresAllegiance"
      v-model="allegiance"
      label="Allegiance"
      placeholder="Choose a side"
      testid="allegiance"
      :options="ALLEGIANCES"
      hint="This event pairs Loyalist against Traitor."
      :errors="fieldErrors('allegiance')"
    />
  </section>

  <section
    v-for="(player, index) in players"
    :key="index"
    class="flex flex-col gap-4 rounded-xl border border-card-line bg-card p-5 shadow-2xs"
    :data-testid="`player-${index}`"
  >
    <h2 class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
      {{ isOwnRow(index) ? 'You' : `Player ${index + 1}` }}
    </h2>

    <p
      v-if="isOwnRow(index)"
      data-testid="my-email"
      class="text-sm text-foreground"
    >
      {{ player.email }}
    </p>

    <template v-else>
      <TextField
        v-model="player.name"
        :label="organising ? 'Name' : 'Their name'"
        :testid="`player-${index}-name`"
        :errors="fieldErrors(`players.${index}.name`)"
      />

      <TextField
        v-model="player.email"
        :label="organising ? 'Email' : 'Their email'"
        type="email"
        inputmode="email"
        :testid="`player-${index}-email`"
        :hint="organising
          ? 'Without an account, they are sent an invitation to this address.'
          : 'They get their own invitation — they do not share yours.'"
        :errors="fieldErrors(`players.${index}.email`)"
      />
    </template>

    <SelectField
      v-model="player.factionId"
      label="Faction"
      placeholder="Not decided yet"
      :testid="`player-${index}-faction`"
      :options="factionOptions"
      :errors="fieldErrors(`players.${index}.faction_id`)"
    />

    <TextField
      v-if="organising"
      v-model="player.armyList"
      label="Army list"
      :rows="6"
      placeholder="Optional. Detachments, units, wargear…"
      :testid="`player-${index}-army-list`"
      :errors="fieldErrors(`players.${index}.army_list`)"
    />
  </section>

  <p
    v-for="message in fieldErrors('players')"
    :key="message"
    data-testid="players-error"
    role="alert"
    class="text-sm text-destructive"
  >
    {{ message }}
  </p>
</template>
