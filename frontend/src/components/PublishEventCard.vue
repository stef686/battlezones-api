<script setup lang="ts">
/**
 * Publishing a Draft, from the top of the organise hub.
 *
 * The checklist says what publishing needs before the button is pressed,
 * each item linking to where it is put right; the API is still the rule and
 * says so with a 422 if the two ever disagree. Only the Lead may publish, so
 * a co-Organiser sees the same card with the button held and the reason why,
 * rather than no card at all and no idea why the Event is still a Draft.
 *
 * The published Event replaces the cached one, so the header's Draft notice
 * and this card both go on their own.
 */
import { useQueryClient } from '@tanstack/vue-query';
import { CircleCheck, CircleDashed } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { type EventSummary, publishEvent } from '@/api/events';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { publishChecklist } from '@/lib/publishing';

const props = defineProps<{ event: EventSummary }>();

const client = useApiClient();
const queryClient = useQueryClient();

const checks = computed(() => publishChecklist(props.event));
const mayPublish = computed(() => props.event.viewer?.permissions.publish === true);
const ready = computed(() => checks.value.every((check) => check.met));

const confirming = ref(false);
const publishing = ref(false);
const problem = ref<string | null>(null);

async function publish(): Promise<void> {
  publishing.value = true;
  problem.value = null;

  try {
    const published = await publishEvent(client, props.event.slug);

    queryClient.setQueryData(keys.event(props.event.slug), published);
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'The event could not be published.';
  } finally {
    publishing.value = false;
  }
}
</script>

<template>
  <section
    data-testid="publish-card"
    class="flex flex-col gap-4 rounded-xl border border-card-line bg-card p-5 shadow-2xs"
  >
    <div class="flex flex-col gap-1">
      <h2 class="text-base font-semibold text-foreground">
        Publish
      </h2>
      <p class="text-sm text-muted-foreground-1">
        Only organisers can see this event until it is published.
      </p>
    </div>

    <ul class="flex flex-col gap-2">
      <li
        v-for="check in checks"
        :key="check.field"
        :data-testid="`publish-check-${check.field}`"
        :data-met="check.met"
        class="flex items-center gap-2 text-sm"
      >
        <CircleCheck
          v-if="check.met"
          class="size-4 shrink-0 text-success"
        />
        <CircleDashed
          v-else
          class="size-4 shrink-0 text-muted-foreground"
        />
        <span
          class="flex-1"
          :class="check.met ? 'text-foreground' : 'text-muted-foreground-1'"
        >
          {{ check.label }}
          <span class="sr-only">{{ check.met ? '(done)' : '(still needed)' }}</span>
        </span>
        <RouterLink
          v-if="!check.met"
          :to="check.fix"
          class="font-medium text-primary hover:underline focus:underline focus:outline-hidden"
        >
          Fix
        </RouterLink>
      </li>
    </ul>

    <AppButton
      data-testid="publish-button"
      :disabled="!mayPublish || !ready || publishing"
      block
      @click="confirming = true"
    >
      {{ publishing ? 'Publishing…' : 'Publish event' }}
    </AppButton>

    <p
      v-if="!mayPublish"
      class="text-sm text-muted-foreground-1"
    >
      Only the lead organiser can publish.
    </p>

    <AppAlert
      v-if="problem"
      data-testid="publish-problem"
      tone="error"
    >
      {{ problem }}
    </AppAlert>

    <ConfirmDialog
      v-model:open="confirming"
      :title="`Publish ${event.name}?`"
      confirm-label="Publish"
      confirm-variant="primary"
      testid="publish-confirm"
      @confirm="publish"
    >
      Players will be able to find and enter it.
    </ConfirmDialog>
  </section>
</template>
