<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed, reactive, ref } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchFeedbackForm, submitFeedback, type FeedbackAnswer, type FeedbackQuestion } from '@/api/feedback';
import AppButton from '@/components/AppButton.vue';

const props = defineProps<{ token: string }>();

const client = useApiClient();

const { data: form, isPending, error } = useQuery({
  queryKey: computed(() => ['feedback', props.token]),
  queryFn: () => fetchFeedbackForm(client, props.token),
  retry: false,
});

/**
 * Unknown, spent and expired all answer 404, and the screen says so as one
 * thing. Which of the three it is would only matter to somebody holding a
 * token they were not sent.
 */
const unusable = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');

const ratings = reactive<Record<number, number>>({});
const answers = reactive<Record<number, string>>({});

const RATINGS = [1, 2, 3, 4, 5];

const submitting = ref(false);
const done = ref(false);
const problem = ref<string | null>(null);

function rate(question: FeedbackQuestion, rating: number): void {
  ratings[question.id] = rating;
}

/**
 * Only what was actually answered is sent.
 *
 * The API refuses a rating question answered with nothing, and a question
 * nobody touched is not an answer — leaving it out is the difference between
 * "no opinion" and a zero in somebody's dashboard.
 */
function answered(): FeedbackAnswer[] {
  return (form.value?.questions ?? []).flatMap((question): FeedbackAnswer[] => {
    if (question.type === 'rating') {
      const rating = ratings[question.id];

      return rating === undefined ? [] : [{ question_id: question.id, rating }];
    }

    const answer = (answers[question.id] ?? '').trim();

    return answer === '' ? [] : [{ question_id: question.id, answer }];
  });
}

async function submit(): Promise<void> {
  submitting.value = true;
  problem.value = null;

  try {
    await submitFeedback(client, props.token, answered());
    done.value = true;
  } catch (caught) {
    // What they typed stays on the screen: this is the one chance the link
    // gives them, and retyping it is how feedback stops being given.
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be sent.';
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-6 p-5">
    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading the form…
    </p>

    <!-- Deliberately not MissingNotice: that wording exists so a screen cannot
         leak whether an Event is hidden, and "we could not find that page" is
         useless to somebody holding a spent feedback link. -->
    <section
      v-else-if="unusable"
      data-testid="feedback-unusable"
      class="flex flex-col gap-3 rounded-xl border border-card-line bg-card p-6 shadow-2xs"
    >
      <h1 class="text-xl font-semibold tracking-tight text-foreground">
        This link no longer works
      </h1>
      <p class="text-sm text-muted-foreground-1">
        A feedback link works once, and expires a month after it is sent. This one has already been used or has
        expired. If you still want to say something, the organisers can send you a new one.
      </p>
    </section>

    <p
      v-else-if="error"
      data-testid="feedback-error"
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="form">
      <header>
        <p class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          {{ form.event.name }}
        </p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">
          How was it?
        </h1>
      </header>

      <section
        v-if="done"
        data-testid="feedback-thanks"
        class="flex flex-col gap-2 rounded-xl border border-card-line bg-card p-6 shadow-2xs"
      >
        <p class="text-lg font-semibold text-success">
          Thank you.
        </p>
        <p class="text-sm text-muted-foreground-1">
          Your answers went to the organisers without your name on them.
        </p>
      </section>

      <template v-else>
        <section
          v-for="question in form.questions"
          :key="question.id"
          :data-testid="`question-${question.id}`"
          class="flex flex-col gap-3 rounded-xl border border-card-line bg-card p-5 shadow-2xs"
        >
          <p class="text-sm font-medium text-foreground">
            {{ question.prompt }}
          </p>

          <!-- Five targets across a phone, each thumb-sized: this is filled in
               on a train home rather than at a desk. -->
          <div
            v-if="question.type === 'rating'"
            class="flex gap-2"
          >
            <button
              v-for="rating in RATINGS"
              :key="rating"
              type="button"
              :data-testid="`rating-${question.id}-${rating}`"
              :aria-pressed="ratings[question.id] === rating"
              class="min-h-11 flex-1 rounded-lg border py-2.5 text-lg font-medium tabular-nums focus:outline-hidden"
              :class="ratings[question.id] === rating
                ? 'border-primary bg-primary text-primary-foreground'
                : 'border-border text-foreground hover:bg-muted-hover focus:bg-muted-hover'"
              @click="rate(question, rating)"
            >
              {{ rating }}
            </button>
          </div>

          <textarea
            v-else
            v-model="answers[question.id]"
            :data-testid="`answer-${question.id}`"
            rows="4"
            class="block w-full rounded-lg border border-border bg-background-2 px-4 py-2.5 text-sm text-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-hidden"
          />
        </section>

        <AppButton
          data-testid="submit-feedback"
          :disabled="submitting"
          block
          @click="submit"
        >
          {{ submitting ? 'Sending…' : 'Send my feedback' }}
        </AppButton>

        <p class="text-center text-sm text-muted-foreground">
          Answers are stored without your name. The link works once.
        </p>

        <p
          v-if="problem"
          data-testid="feedback-problem"
          role="alert"
          class="text-destructive"
        >
          {{ problem }}
        </p>
      </template>
    </template>
  </main>
</template>
