<script setup lang="ts">
/**
 * The Event Colour: a preset, any hex, or none at all.
 *
 * Edits a value its parent owns and never writes to the API, like the other
 * Event fields. A hex is handed back only once it is a whole colour, in the
 * one spelling the API stores (lowercase `#rrggbb`), so a half-typed value
 * never repaints the screen or reaches a save. "Platform colour", or emptying
 * the hex, hands back null, which is how an Event goes back to looking like
 * every other.
 */
import { Check } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import TextField from '@/components/TextField.vue';
import { EVENT_COLOUR_PRESETS, eventColourOf } from '@/lib/event-colour';
import type { FieldErrors } from '@/lib/event-fields';

const props = withDefaults(defineProps<{ errors?: FieldErrors }>(), { errors: () => ({}) });

const model = defineModel<string | null>({ required: true });

const typed = ref(model.value ?? '');

watch(model, (colour) => {
  if (colour !== eventColourOf(typed.value)) {
    typed.value = colour ?? '';
  }
});

const incomplete = computed(() => typed.value.trim() !== '' && eventColourOf(typed.value) === null);

const hexErrors = computed(() => (incomplete.value
  ? ['Use a six-digit hex value like #c2410c.']
  : props.errors.colour ?? []));

function type(value: string): void {
  typed.value = value;

  // An emptied hex is the Organiser taking the colour away, exactly as
  // "Platform colour" does.
  if (value.trim() === '') {
    model.value = null;

    return;
  }

  const colour = eventColourOf(value);

  if (colour !== null && colour !== model.value) {
    model.value = colour;
  }
}

function choose(colour: string | null): void {
  typed.value = colour ?? '';
  model.value = colour;
}
</script>

<template>
  <fieldset class="flex flex-col gap-3">
    <legend class="mb-2 text-sm font-medium text-foreground">
      Event colour
    </legend>

    <div class="flex flex-wrap gap-2">
      <button
        type="button"
        data-testid="event-colour-none"
        :aria-pressed="model === null"
        class="flex h-10 items-center gap-2 rounded-lg border px-3 text-sm"
        :class="model === null ? 'border-primary text-foreground' : 'border-card-line text-muted-foreground-1'"
        @click="choose(null)"
      >
        Platform colour
      </button>

      <button
        v-for="preset in EVENT_COLOUR_PRESETS"
        :key="preset.hex"
        type="button"
        :data-testid="`event-colour-preset-${preset.hex}`"
        :aria-pressed="model === preset.hex"
        class="flex size-10 items-center justify-center rounded-lg border-2"
        :class="model === preset.hex ? 'border-foreground' : 'border-transparent'"
        :style="{ backgroundColor: preset.hex }"
        @click="choose(preset.hex)"
      >
        <span class="sr-only">{{ preset.name }}</span>
        <Check
          v-if="model === preset.hex"
          class="size-4 shrink-0 text-primary-foreground"
        />
      </button>
    </div>

    <div class="flex items-end gap-3">
      <div class="flex-1">
        <TextField
          :model-value="typed"
          label="Hex"
          placeholder="#c2410c"
          autocomplete="off"
          testid="event-colour-hex"
          :errors="hexErrors"
          @update:model-value="type"
        />
      </div>

      <span
        aria-hidden="true"
        data-testid="event-colour-swatch"
        class="mb-1 size-10 shrink-0 rounded-lg border border-card-line"
        :class="model === null ? 'bg-primary' : ''"
        :style="model === null ? undefined : { backgroundColor: model }"
      />
    </div>
  </fieldset>
</template>
