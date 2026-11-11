<script setup lang="ts">
/**
 * An Organiser's own Event, made in four steps and sent once.
 *
 * Nothing exists until Create is pressed (ADR 0006): the draft lives here, a
 * step passes only once what it asks for is sound, and Back keeps everything
 * typed. The server's refusals are mapped back to the step that asked for the
 * field, so an Organiser is sent to the mistake rather than told about it on
 * a summary they cannot edit.
 *
 * The fields are the same components the settings and format screens edit an
 * Event with, so an Event reads the same being made as being changed.
 *
 * The Banner is the one thing not sent with the Event. It is held here until
 * the Event exists and then uploaded to the Banner route; a failed upload
 * never undoes the Event (ADR 0006), the Organiser is told to add it in
 * Settings instead.
 *
 * Steps already reached can be jumped to from the step indicator, and each
 * part of Confirm links back to its own step. Leaving with something entered
 * asks first — in the app for a navigation, through the browser for closing
 * or reloading the tab — and stops asking once the Event exists.
 */
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { onBeforeRouteLeave, useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { createEvent, uploadBanner, type EventSummary } from '@/api/events';
import { fetchGameSystems } from '@/api/game-systems';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import EventBannerField from '@/components/EventBannerField.vue';
import EventDetailsFields from '@/components/EventDetailsFields.vue';
import EventShapeFields from '@/components/EventShapeFields.vue';
import EventVenueFields from '@/components/EventVenueFields.vue';
import ScoreTypeListEditor from '@/components/ScoreTypeListEditor.vue';
import TextField from '@/components/TextField.vue';
import { errorsUnder, teamSizeLabel, type FieldErrors } from '@/lib/event-fields';
import {
  WIZARD_STEPS,
  blankDraft,
  firstStepWith,
  isUntouched,
  isShownField,
  landingOf,
  problemsOf,
  submissionOf,
  type EventDraft,
  type WizardStep,
} from '@/lib/event-wizard';
import { formatDateRange } from '@/lib/dates';
import { useToastStore } from '@/stores/toast';

const client = useApiClient();
const queryClient = useQueryClient();
const router = useRouter();
const toasts = useToastStore();

/** The phone's own zone is the best guess at the hall's until it can be chosen. */
const blank = blankDraft(Intl.DateTimeFormat().resolvedOptions().timeZone);
const draft = ref<EventDraft>(structuredClone(blank));

const step = ref<WizardStep>('details');
const stepIndex = computed(() => WIZARD_STEPS.findIndex((candidate) => candidate.id === step.value));

/**
 * The furthest step the Organiser has got to. Anything up to it can be jumped
 * to from the step indicator; anything past it has not been asked yet.
 */
const furthestIndex = ref(0);

watch(stepIndex, (index) => {
  furthestIndex.value = Math.max(furthestIndex.value, index);
});

function isReachable(index: number): boolean {
  return index <= furthestIndex.value && index !== stepIndex.value;
}

const errors = ref<FieldErrors>({});
const scoreTypeErrors = computed(() => errorsUnder(errors.value, 'score_types'));

const submitting = ref(false);
const problem = ref<string | null>(null);

/**
 * A local URL for the held Banner, so it can be previewed before anything is
 * uploaded. Revoked whenever the file changes or the wizard goes away, since
 * the browser keeps the file alive for as long as the URL exists.
 */
const bannerPreview = ref<string | null>(null);

watch(() => draft.value.banner, (file) => {
  revokeBannerPreview();
  bannerPreview.value = file === null ? null : URL.createObjectURL(file);
});

onBeforeUnmount(revokeBannerPreview);

function revokeBannerPreview(): void {
  if (bannerPreview.value !== null) {
    URL.revokeObjectURL(bannerPreview.value);
    bannerPreview.value = null;
  }
}

const heldBanner = computed(() => (bannerPreview.value === null
  ? null
  : { large: bannerPreview.value, small: bannerPreview.value }));

/**
 * Whether leaving would throw work away. Nothing typed has nothing to lose,
 * and once the Event exists everything typed is on it.
 */
const isCreated = ref(false);
const hasUnsavedWork = computed(() => !isCreated.value && !isUntouched(draft.value, blank));

/**
 * Where the Organiser was going when they were asked whether to leave. Once
 * they say yes, that same navigation is made again and let through.
 */
const leaveOpen = ref(false);
let leavingFor: string | null = null;
let leaveConfirmed = false;

/**
 * Signed out — by logging out, or by the session being lost — nothing typed
 * can be created any more, so asking would only hold the Organiser on a
 * wizard that cannot submit, and a lost session's move to login would ask
 * again every time.
 */
onBeforeRouteLeave((to) => {
  if (!hasUnsavedWork.value || leaveConfirmed || !client.isAuthenticated()) {
    return true;
  }

  leavingFor = to.fullPath;
  leaveOpen.value = true;

  return false;
});

/**
 * Closing or reloading the tab is not a navigation the router sees, so the
 * browser is asked to check instead, for exactly as long as there is work to
 * lose. Browsers word that question themselves.
 */
function askBeforeUnload(event: BeforeUnloadEvent): void {
  event.preventDefault();
}

watch(hasUnsavedWork, (isUnsaved) => {
  if (isUnsaved) {
    window.addEventListener('beforeunload', askBeforeUnload);
  } else {
    window.removeEventListener('beforeunload', askBeforeUnload);
  }
});

onBeforeUnmount(() => window.removeEventListener('beforeunload', askBeforeUnload));

/**
 * Carries on with the navigation that was stopped, without leaving the
 * abandoned wizard in history for Back to reopen empty. A leave that was the
 * back button goes back again, since the guard undid that step; any other
 * takes the wizard's place.
 */
async function leave(): Promise<void> {
  if (leavingFor === null) {
    return;
  }

  leaveConfirmed = true;

  if (leavingFor === router.options.history.state.back) {
    router.back();
  } else {
    await router.replace(leavingFor);
  }
}

/** Look asks for nothing, so with nothing chosen moving on is skipping it. */
const nextLabel = computed(() => (step.value === 'look' && draft.value.banner === null ? 'Skip' : 'Next'));

function next(): void {
  const problems = problemsOf(step.value, draft.value);

  errors.value = problems;

  if (Object.keys(problems).length > 0) {
    return;
  }

  step.value = WIZARD_STEPS[stepIndex.value + 1]?.id ?? step.value;
}

/**
 * Straight to a step already reached. Going forward still holds every step in
 * between to what it asks for, so a change made after jumping back cannot be
 * carried past the step that would have refused it.
 */
function jumpTo(target: WizardStep): void {
  const { step: landing, problems } = landingOf(step.value, target, draft.value);

  errors.value = problems;
  step.value = landing;
}

function back(): void {
  errors.value = {};
  step.value = WIZARD_STEPS[stepIndex.value - 1]?.id ?? step.value;
}

async function submit(): Promise<void> {
  if (submitting.value) {
    return;
  }

  submitting.value = true;
  problem.value = null;

  try {
    const created = await createEvent(client, submissionOf(draft.value));
    const { event, bannerRefusal } = await withHeldBanner(created);

    isCreated.value = true;
    queryClient.setQueryData(keys.event(event.slug), event);
    toasts.show(bannerRefusal === null
      ? 'Event created'
      : ['Event created, but the banner didn\'t upload.', bannerRefusal, 'Add it in Settings.'].filter(Boolean).join(' '));

    // Replaced rather than pushed: Back from the hub must not reopen a
    // wizard for an Event that already exists.
    await router.replace({ name: 'organise', params: { eventSlug: created.slug } });
  } catch (caught) {
    if (caught instanceof ApiError && caught.kind === 'validation') {
      errors.value = caught.fields;
      step.value = firstStepWith(caught.fields);

      // A refusal no step shows would leave the Organiser on a step with
      // nothing to fix, so say it above the form instead.
      if (!Object.keys(caught.fields).some(isShownField)) {
        problem.value = caught.message;
      }
    } else {
      problem.value = caught instanceof ApiError ? caught.message : 'The event could not be created.';
    }
  } finally {
    submitting.value = false;
  }
}

/**
 * The new Event with the held Banner on it, if there is one to upload. A
 * refusal here is the Banner's alone: the Event already exists, so it is
 * reported rather than thrown into the create's own error handling.
 * `bannerRefusal` is null when nothing went wrong, and otherwise the API's
 * reason, or an empty string when it gave none.
 *
 * Only the Banner is taken from the upload's answer. The Banner route does not
 * count Attendees, so taking its whole Event would drop the count the 201
 * carried.
 */
async function withHeldBanner(created: EventSummary): Promise<{ event: EventSummary; bannerRefusal: string | null }> {
  if (draft.value.banner === null) {
    return { event: created, bannerRefusal: null };
  }

  try {
    const uploaded = await uploadBanner(client, created.slug, draft.value.banner);

    return { event: { ...created, banner: uploaded.banner }, bannerRefusal: null };
  } catch (caught) {
    const reason = caught instanceof ApiError && caught.kind === 'validation'
      ? caught.fields.banner?.[0] ?? ''
      : '';

    return { event: created, bannerRefusal: reason };
  }
}

const { data: gameSystems } = useQuery({
  queryKey: keys.gameSystems(),
  queryFn: () => fetchGameSystems(client),
  retry: false,
});

/**
 * What Confirm reads back, one part per step and in step order, so each part
 * can send the Organiser back to the step that asked for it. A row marked
 * `truncate` is a file name, cut short rather than wrapped.
 */
interface SummaryRow {
  label: string;
  value: string;
  truncate?: boolean;
}

const summary = computed((): { step: WizardStep; name: string; rows: SummaryRow[] }[] => {
  const { details, venue, shape, scoreTypes } = draft.value;
  const where = [venue.venue_name, venue.venue_city].filter((part) => part !== '').join(', ');
  const system = gameSystems.value?.find((candidate) => String(candidate.id) === shape.game_system_id);

  const rowsOf: Record<Exclude<WizardStep, 'confirm'>, SummaryRow[]> = {
    details: [
      { label: 'Name', value: details.name },
      { label: 'URL', value: `/events/${draft.value.slug}` },
      { label: 'When', value: `${formatDateRange(details.starts_at, details.ends_at)} (${draft.value.timezone})` },
      { label: 'Where', value: where === '' ? 'Not set yet' : where },
    ],
    look: [
      { label: 'Banner', value: draft.value.banner?.name ?? 'No banner', truncate: true },
    ],
    format: [
      { label: 'Game system', value: system?.name ?? '' },
      { label: 'Team size', value: teamSizeLabel(Number(shape.attendee_size)) },
      { label: 'Places', value: shape.max_attendees === '' ? 'No limit' : shape.max_attendees },
      { label: 'Scoring', value: scoreTypes.map((row) => row.name).join(', ') },
    ],
  };

  return WIZARD_STEPS
    .filter((candidate) => candidate.id !== 'confirm')
    .map((candidate) => ({ step: candidate.id, name: candidate.name, rows: rowsOf[candidate.id as keyof typeof rowsOf] }));
});
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <header class="flex flex-col gap-3">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        Run an event
      </h1>

      <ol
        aria-label="Steps"
        class="flex gap-2 text-sm"
      >
        <li
          v-for="(candidate, index) in WIZARD_STEPS"
          :key="candidate.id"
          :data-testid="`create-step-${candidate.id}`"
          :aria-current="candidate.id === step ? 'step' : undefined"
          class="flex-1 border-b-2 pb-1"
          :class="[
            index <= furthestIndex ? 'border-primary text-foreground' : 'border-card-line text-muted-foreground',
            index === stepIndex ? 'font-semibold' : '',
          ]"
        >
          <button
            v-if="isReachable(index)"
            type="button"
            class="w-full text-left hover:text-primary"
            @click="jumpTo(candidate.id)"
          >
            {{ candidate.name }}
          </button>
          <template v-else>
            {{ candidate.name }}
          </template>
        </li>
      </ol>
    </header>

    <AppAlert
      v-if="problem"
      tone="error"
      data-testid="create-problem"
    >
      {{ problem }}
    </AppAlert>

    <form
      class="flex flex-col gap-5"
      novalidate
      @submit.prevent="step === 'confirm' ? submit() : next()"
    >
      <template v-if="step === 'details'">
        <EventDetailsFields
          v-model="draft.details"
          :errors="errors"
        >
          <TextField
            v-model="draft.slug"
            label="URL"
            hint="Lowercase letters, numbers and hyphens."
            testid="create-slug"
            :errors="errors.slug"
          />
        </EventDetailsFields>

        <p
          data-testid="create-timezone"
          class="text-sm text-muted-foreground-1"
        >
          Times are in {{ draft.timezone }}.
        </p>

        <p
          v-if="errors.timezone"
          data-testid="create-timezone-error"
          class="text-sm text-destructive"
        >
          {{ errors.timezone.join(' ') }}
        </p>

        <EventVenueFields
          v-model="draft.venue"
          :errors="errors"
        />
      </template>

      <template v-else-if="step === 'look'">
        <p class="text-sm text-muted-foreground-1">
          Optional. It uploads once the event is created, and can be changed later in Settings.
        </p>

        <EventBannerField
          :banner="heldBanner"
          @choose="draft.banner = $event"
          @remove="draft.banner = null"
        />
      </template>

      <template v-else-if="step === 'format'">
        <EventShapeFields
          v-model="draft.shape"
          :errors="errors"
        />

        <section class="flex flex-col gap-3">
          <h2 class="text-sm font-semibold text-foreground">
            Scoring
          </h2>

          <ScoreTypeListEditor
            v-model="draft.scoreTypes"
            :errors="scoreTypeErrors"
          />

          <p
            v-if="errors.score_types"
            data-testid="create-score-types-error"
            class="text-sm text-destructive"
          >
            {{ errors.score_types[0] }}
          </p>
        </section>
      </template>

      <div
        v-else
        data-testid="create-summary"
        class="flex flex-col gap-4"
      >
        <section
          v-for="part in summary"
          :key="part.step"
          :data-testid="`create-summary-${part.step}`"
          class="rounded-lg border border-card-line bg-card"
        >
          <header class="flex items-center justify-between gap-4 border-b border-card-divider py-1 pr-1 pl-4">
            <h2 class="text-sm font-semibold text-foreground">
              {{ part.name }}
            </h2>
            <AppButton
              type="button"
              variant="ghost"
              size="sm"
              :data-testid="`create-edit-${part.step}`"
              @click="jumpTo(part.step)"
            >
              Edit<span class="sr-only"> {{ part.name }}</span>
            </AppButton>
          </header>

          <dl class="flex flex-col divide-y divide-card-divider">
            <div
              v-for="row in part.rows"
              :key="row.label"
              class="flex items-center justify-between gap-4 px-4 py-3 text-sm"
            >
              <dt class="shrink-0 text-muted-foreground-1">
                {{ row.label }}
              </dt>
              <dd
                class="min-w-0 text-right text-foreground"
                :class="row.truncate ? 'truncate' : 'break-words'"
              >
                {{ row.value }}
              </dd>
            </div>
          </dl>
        </section>

        <p
          data-testid="create-draft-note"
          class="text-sm text-muted-foreground-1"
        >
          Your event is saved as a draft that only its organisers can see until it is published.
        </p>
      </div>

      <div class="flex gap-3">
        <AppButton
          v-if="stepIndex > 0"
          type="button"
          variant="secondary"
          data-testid="create-back"
          @click="back"
        >
          Back
        </AppButton>

        <AppButton
          v-if="step !== 'confirm'"
          type="submit"
          data-testid="create-next"
          class="flex-1"
        >
          {{ nextLabel }}
        </AppButton>

        <AppButton
          v-else
          type="submit"
          data-testid="create-submit"
          class="flex-1"
          :disabled="submitting"
        >
          {{ submitting ? 'Creating…' : 'Create event' }}
        </AppButton>
      </div>
    </form>

    <ConfirmDialog
      v-model:open="leaveOpen"
      title="Leave without creating?"
      confirm-label="Leave"
      cancel-label="Keep editing"
      testid="create-leave"
      @confirm="leave"
    >
      Nothing has been saved yet, so what you've entered will be lost.
    </ConfirmDialog>
  </main>
</template>
