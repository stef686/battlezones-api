<script setup lang="ts">
/**
 * A labelled input with its validation messages attached.
 *
 * Every auth screen is the same shape, and the API returns errors per field,
 * so the pairing of the two belongs in one place rather than being retyped
 * — and forgotten — on each form.
 *
 * The label points at the input by id rather than wrapping it, which is what
 * lets the hint and the error messages be announced with the field through
 * `aria-describedby`: a Player using a screen reader hears why the form was
 * rejected, not just that it was.
 */
import { computed, useId } from 'vue';

const props = withDefaults(defineProps<{
  label: string;
  type?: string;
  autocomplete?: string;
  inputmode?: 'text' | 'email' | 'numeric';
  testid?: string;
  errors?: string[];
  hint?: string | null;
}>(), {
  type: 'text',
  autocomplete: undefined,
  inputmode: undefined,
  testid: undefined,
  errors: () => [],
  hint: null,
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

    <input
      :id="fieldId"
      v-model="model"
      :type="type"
      :autocomplete="autocomplete"
      :inputmode="inputmode"
      :data-testid="testid"
      :aria-invalid="invalid"
      :aria-describedby="describedBy"
      class="block w-full rounded-lg border bg-background-2 px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-hidden disabled:pointer-events-none disabled:opacity-50"
      :class="invalid
        ? 'border-destructive focus:border-destructive focus:ring-1 focus:ring-destructive'
        : 'border-border focus:border-primary focus:ring-1 focus:ring-primary'"
    >

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
