<script setup lang="ts">
/**
 * Where an Event happens.
 *
 * Edits a value its parent owns and never writes to the API. The country list
 * is read here rather than handed in: it is reference data, cached for the
 * session under the shared key, so every screen with a venue reads it once.
 */
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';

import { useApiClient } from '@/api';
import { fetchCountries } from '@/api/countries';
import { keys } from '@/api/keys';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';
import type { EventVenue, FieldErrors } from '@/lib/event-fields';

withDefaults(defineProps<{ errors?: FieldErrors }>(), { errors: () => ({}) });

const model = defineModel<EventVenue>({ required: true });

function update<Field extends keyof EventVenue>(field: Field, value: EventVenue[Field]): void {
  model.value = { ...model.value, [field]: value };
}

const client = useApiClient();

const { data: countries } = useQuery({
  queryKey: keys.countries(),
  queryFn: () => fetchCountries(client),
  staleTime: Infinity,
  retry: false,
});

const countryOptions = computed(() => (countries.value ?? [])
  .map((country) => ({ value: country.code, label: country.name })));
</script>

<template>
  <div class="flex flex-col gap-5">
    <TextField
      :model-value="model.venue_name"
      label="Venue"
      testid="event-venue-name"
      :errors="errors.venue_name"
      @update:model-value="update('venue_name', $event)"
    />

    <TextField
      :model-value="model.venue_address"
      label="Address"
      testid="event-venue-address"
      :errors="errors.venue_address"
      @update:model-value="update('venue_address', $event)"
    />

    <TextField
      :model-value="model.venue_city"
      label="Town or city"
      testid="event-venue-city"
      :errors="errors.venue_city"
      @update:model-value="update('venue_city', $event)"
    />

    <SelectField
      :model-value="model.venue_country"
      label="Country"
      :options="countryOptions"
      placeholder="No country"
      testid="event-venue-country"
      :errors="errors.venue_country"
      @update:model-value="update('venue_country', $event)"
    />
  </div>
</template>
