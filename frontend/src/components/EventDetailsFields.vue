<script setup lang="ts">
/**
 * What an Event is called and when it runs.
 *
 * Edits a value its parent owns and never writes to the API: the settings
 * screen sends only what changed, and the create-event wizard sends it all at
 * the end, so when and how is theirs to decide.
 *
 * The default slot sits between the name and the dates, which is where the
 * settings screen asks for its description. The wizard does not ask for one.
 */
import TextField from '@/components/TextField.vue';
import type { EventDetails, FieldErrors } from '@/lib/event-fields';

withDefaults(defineProps<{ errors?: FieldErrors }>(), { errors: () => ({}) });

const model = defineModel<EventDetails>({ required: true });

function update<Field extends keyof EventDetails>(field: Field, value: EventDetails[Field]): void {
  model.value = { ...model.value, [field]: value };
}
</script>

<template>
  <div class="flex flex-col gap-5">
    <TextField
      :model-value="model.name"
      label="Name"
      testid="event-name"
      :errors="errors.name"
      @update:model-value="update('name', $event)"
    />

    <slot />

    <TextField
      :model-value="model.starts_at"
      label="Starts"
      type="datetime-local"
      testid="event-starts-at"
      :errors="errors.starts_at"
      @update:model-value="update('starts_at', $event)"
    />

    <TextField
      :model-value="model.ends_at"
      label="Ends"
      type="datetime-local"
      testid="event-ends-at"
      :errors="errors.ends_at"
      @update:model-value="update('ends_at', $event)"
    />
  </div>
</template>
