<script setup lang="ts">
/**
 * How an Event is played: how its Rounds are paired, how many there are, and
 * whether Players pick a side.
 *
 * Edits a value its parent owns and never writes to the API. Every pairing
 * format is offered, but only those the API has built can be chosen; the rest
 * are disabled and say they are coming soon, rather than being accepted and
 * paired as Swiss.
 */
import { useId } from 'vue';

import TextField from '@/components/TextField.vue';
import { PAIRING_FORMATS, type EventPlay, type FieldErrors } from '@/lib/event-fields';

withDefaults(defineProps<{
  errors?: FieldErrors;
}>(), {
  errors: () => ({}),
});

const model = defineModel<EventPlay>({ required: true });

function update<Field extends keyof EventPlay>(field: Field, value: EventPlay[Field]): void {
  model.value = { ...model.value, [field]: value };
}

const formatName = useId();
const allegianceId = useId();
const allegianceHintId = useId();
</script>

<template>
  <div class="flex flex-col gap-5">
    <fieldset>
      <legend class="mb-2 block text-sm font-medium text-foreground">
        Pairing format
      </legend>

      <div class="flex flex-col gap-2">
        <label
          v-for="format in PAIRING_FORMATS"
          :key="format.value"
          :data-testid="`event-pairing-format-${format.value}-option`"
          class="flex items-center gap-3 rounded-lg border border-card-line bg-card px-4 py-3 text-sm"
          :class="format.available ? 'cursor-pointer text-foreground' : 'cursor-not-allowed text-muted-foreground'"
        >
          <input
            type="radio"
            :name="formatName"
            :value="format.value"
            :checked="model.pairing_format === format.value"
            :disabled="!format.available"
            :data-testid="`event-pairing-format-${format.value}`"
            class="size-4 shrink-0 border-border bg-background-2 text-primary focus:ring-primary disabled:opacity-50"
            @change="update('pairing_format', format.value)"
          >
          <span class="flex-1">{{ format.label }}</span>
          <span
            v-if="!format.available"
            class="text-xs text-muted-foreground"
          >
            Coming soon
          </span>
        </label>
      </div>

      <p
        v-for="message in errors.pairing_format"
        :key="message"
        class="mt-2 text-sm text-destructive"
        data-testid="event-pairing-format-error"
      >
        {{ message }}
      </p>
    </fieldset>

    <TextField
      :model-value="model.round_count"
      label="Number of rounds"
      inputmode="numeric"
      hint="Leave empty to keep adding rounds until you finish."
      testid="event-round-count"
      :errors="errors['settings.round_count']"
      @update:model-value="update('round_count', $event)"
    />

    <div class="flex items-start justify-between gap-4">
      <div>
        <label
          :for="allegianceId"
          class="block text-sm font-medium text-foreground"
        >
          Players pick a side (Allegiance)
        </label>
        <p
          :id="allegianceHintId"
          class="mt-1 text-sm text-muted-foreground"
        >
          Each game is played between opposing sides.
        </p>
      </div>

      <!-- Preline's switch: a real checkbox under the drawn track, so the
           keyboard, the label and a screen reader all reach it. -->
      <span class="relative inline-block h-6 w-11 shrink-0">
        <input
          :id="allegianceId"
          type="checkbox"
          role="switch"
          :checked="model.requires_opposed_allegiance"
          :aria-describedby="allegianceHintId"
          data-testid="event-allegiance"
          class="peer absolute inset-0 z-10 size-full cursor-pointer opacity-0"
          @change="update('requires_opposed_allegiance', ($event.target as HTMLInputElement).checked)"
        >
        <span class="absolute inset-0 rounded-full bg-surface transition-colors duration-200 ease-in-out peer-checked:bg-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary peer-focus-visible:ring-offset-2" />
        <span class="absolute start-0.5 top-1/2 size-5 -translate-y-1/2 rounded-full bg-switch shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-full" />
      </span>
    </div>
  </div>
</template>
