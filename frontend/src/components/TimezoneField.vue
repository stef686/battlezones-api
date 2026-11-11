<script setup lang="ts">
/**
 * The IANA timezone an Event is run in, picked from a list narrowed by a search.
 *
 * Edits a value its parent owns and never writes to the API. The zones are the
 * browser's own (`Intl.supportedValuesOf`), which the API accepts with
 * backward-compatible names included, so a phone that still calls Kolkata
 * "Asia/Calcutta" can send what it reports.
 *
 * A search box over a native `<select>` rather than a hand-built combobox, for
 * the same reason `SelectField` is native: the platform picker already works
 * with a thumb, the keyboard and a screen reader. The chosen zone is always
 * kept in the list, so narrowing the search never empties the select under it.
 *
 * `fixedReason` is the parent's answer to whether the zone can still change;
 * given, the zone is shown disabled with that reason above it and no search.
 */
import { computed, ref } from 'vue';

import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';
import { timezoneLabel, timezones } from '@/lib/timezones';

const props = withDefaults(defineProps<{
  errors?: string[];
  fixedReason?: string | null;
}>(), {
  errors: () => [],
  fixedReason: null,
});

const model = defineModel<string>({ required: true });

const search = ref('');

const options = computed(() => {
  const needle = search.value.trim().toLowerCase().replace(/[\s_]+/g, ' ');
  const matching = timezones().filter((zone) => needle === ''
    || zone === model.value
    || timezoneLabel(zone).toLowerCase().includes(needle));
  const zones = matching.includes(model.value) ? matching : [model.value, ...matching];

  return zones.map((zone) => ({ value: zone, label: timezoneLabel(zone) }));
});
</script>

<template>
  <div class="flex flex-col gap-3">
    <p
      v-if="props.fixedReason"
      data-testid="event-timezone-fixed"
      class="text-sm text-muted-foreground-1"
    >
      {{ props.fixedReason }}
    </p>

    <TextField
      v-else
      v-model="search"
      label="Search timezones"
      label-hidden
      placeholder="Search timezones, e.g. London"
      testid="event-timezone-search"
    />

    <SelectField
      v-model="model"
      label="Timezone"
      :options="options"
      :disabled="props.fixedReason !== null"
      hint="The event's times are in this zone."
      testid="event-timezone"
      :errors="errors"
    />
  </div>
</template>
