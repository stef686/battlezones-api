<script setup lang="ts">
/**
 * A labelled picker with its validation messages attached.
 *
 * Options are `{ value, label }` rather than raw strings so the value sent to
 * the API and the words a Player reads never have to be the same thing.
 *
 * A native `<select>` on purpose. Preline's enhanced select is prettier, but
 * this is a form filled in on a phone at a venue, and the platform picker is
 * the one that already works with the keyboard, the screen reader and a
 * thumb.
 */
import { computed, useId } from 'vue';

const props = withDefaults(defineProps<{
  label: string;
  options: { value: string; label: string }[];
  placeholder?: string;
  testid?: string;
  errors?: string[];
  hint?: string | null;
  disabled?: boolean;
}>(), {
  placeholder: 'Choose one',
  testid: undefined,
  errors: () => [],
  hint: null,
  disabled: false,
});

const model = defineModel<string>({ required: true });

const fieldId = useId();
const hintId = `${fieldId}-hint`;
const errorId = `${fieldId}-error`;

const invalid = computed(() => props.errors.length > 0);

const describedBy = computed(() => {
  const ids = [props.hint ? hintId : null, invalid.value ? errorId : null].filter(Boolean);

  return ids.length > 0 ? ids.join(' ') : undefined;
});
</script>

<template>
  <div>
    <label
      :for="fieldId"
      class="mb-2 block text-sm font-medium text-foreground"
    >{{ label }}</label>

    <select
      :id="fieldId"
      v-model="model"
      :disabled="disabled"
      :data-testid="testid"
      :aria-invalid="invalid"
      :aria-describedby="describedBy"
      class="block w-full rounded-lg border bg-background-2 px-4 py-2.5 text-sm text-foreground focus:outline-hidden disabled:pointer-events-none disabled:opacity-50"
      :class="invalid
        ? 'border-destructive focus:border-destructive focus:ring-1 focus:ring-destructive'
        : 'border-border focus:border-primary focus:ring-1 focus:ring-primary'"
    >
      <option value="">
        {{ placeholder }}
      </option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>

    <p
      v-if="hint"
      :id="hintId"
      class="mt-2 text-sm text-muted-foreground"
    >
      {{ hint }}
    </p>

    <p
      v-for="message in errors"
      :id="errorId"
      :key="message"
      class="mt-2 text-sm text-destructive"
      :data-testid="testid ? `${testid}-error` : undefined"
    >
      {{ message }}
    </p>
  </div>
</template>
