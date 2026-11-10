<script setup lang="ts">
/**
 * The shape an Event is run at: its Game System, its team size and its places.
 *
 * Edits a value its parent owns and never writes to the API. The Game System
 * list is reference data, read here under the shared key so no parent has to.
 *
 * `locked` is the parent's answer to whether anybody has entered — the API
 * refuses a new Game System or team size from then on, because every entry
 * was built at the old one. Places stay open: a limit can move after entries.
 */
import { useQuery } from '@tanstack/vue-query';
import { TriangleAlert } from 'lucide-vue-next';
import { computed } from 'vue';

import { useApiClient } from '@/api';
import { fetchGameSystems } from '@/api/game-systems';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';
import type { EventShape, FieldErrors } from '@/lib/event-fields';

const props = withDefaults(defineProps<{
  locked?: boolean;
  errors?: FieldErrors;
}>(), {
  locked: false,
  errors: () => ({}),
});

const model = defineModel<EventShape>({ required: true });

function update<Field extends keyof EventShape>(field: Field, value: EventShape[Field]): void {
  model.value = { ...model.value, [field]: value };
}

const client = useApiClient();

const { data: gameSystems } = useQuery({
  queryKey: keys.gameSystems(),
  queryFn: () => fetchGameSystems(client),
  enabled: computed(() => !props.locked),
  retry: false,
});

const gameSystemOptions = computed(() => (gameSystems.value ?? [])
  .map((system) => ({ value: String(system.id), label: system.name })));

/** One Player is singles; anything more is a team of that size. */
const partySizeOptions = Array.from({ length: 8 }, (_, index) => ({
  value: String(index + 1),
  label: index === 0 ? 'Singles — one player' : `Teams of ${index + 1}`,
}));

/**
 * What a place is called follows the size being chosen, not the size the
 * Event was saved at: an Organiser who has just picked doubles is buying
 * teams from that moment, and a field still counting Attendees reads as the
 * limit meaning something other than it does.
 */
const placesLabel = computed(() => (Number(model.value.attendee_size) > 1 ? 'Max Teams' : 'Max Attendees'));
</script>

<template>
  <div class="flex flex-col gap-5">
    <!-- Above the two fields it explains rather than under them: a disabled
         select says nothing about why it will not open, and a reason read
         after the reader has already tried is a reason late. -->
    <AppAlert
      v-if="locked"
      data-testid="event-shape-locked"
      tone="warning"
    >
      <span class="flex items-start gap-x-2">
        <TriangleAlert class="mt-px size-4 shrink-0" />
        Game system and team size are now fixed.
      </span>
    </AppAlert>

    <SelectField
      :model-value="model.game_system_id"
      label="Game system"
      :options="gameSystemOptions"
      placeholder="Choose a game"
      :disabled="locked"
      testid="event-game-system"
      :errors="errors.game_system_id"
      @update:model-value="update('game_system_id', $event)"
    />

    <SelectField
      :model-value="model.attendee_size"
      label="Team size"
      :options="partySizeOptions"
      :disabled="locked"
      testid="event-attendee-size"
      :errors="errors.attendee_size"
      @update:model-value="update('attendee_size', $event)"
    />

    <TextField
      :model-value="model.max_attendees"
      :label="placesLabel"
      inputmode="numeric"
      hint="Leave empty for no limit."
      testid="event-max-attendees"
      :errors="errors.max_attendees"
      @update:model-value="update('max_attendees', $event)"
    />
  </div>
</template>
