<script setup lang="ts">
/**
 * A labelled input with its validation messages attached.
 *
 * Every auth screen is the same shape, and the API returns errors per field,
 * so the pairing of the two belongs in one place rather than being retyped
 * — and forgotten — on each form.
 */
withDefaults(defineProps<{
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
</script>

<template>
  <label class="flex flex-col gap-1.5">
    <span class="text-sm text-ink-muted">{{ label }}</span>

    <input
      v-model="model"
      :type="type"
      :autocomplete="autocomplete"
      :inputmode="inputmode"
      :data-testid="testid"
      class="rounded-lg border border-border bg-surface-sunken px-3 py-2.5 text-ink outline-none focus:border-accent"
    >

    <span
      v-if="hint"
      class="text-sm text-ink-faint"
    >{{ hint }}</span>

    <span
      v-for="message in errors"
      :key="message"
      class="text-sm text-danger"
      :data-testid="testid ? `${testid}-error` : undefined"
    >{{ message }}</span>
  </label>
</template>
