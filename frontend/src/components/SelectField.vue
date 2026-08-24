<script setup lang="ts">
/**
 * A labelled picker with its validation messages attached.
 *
 * Options are `{ value, label }` rather than raw strings so the value sent to
 * the API and the words a Player reads never have to be the same thing.
 */
withDefaults(defineProps<{
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
</script>

<template>
  <label class="flex flex-col gap-1.5">
    <span class="text-sm text-ink-muted">{{ label }}</span>

    <select
      v-model="model"
      :disabled="disabled"
      :data-testid="testid"
      class="rounded-lg border border-border bg-surface-sunken px-3 py-2.5 text-ink outline-none focus:border-accent disabled:opacity-60"
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
