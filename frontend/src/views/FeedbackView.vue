<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed, reactive, ref } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchFeedbackForm, submitFeedback, type FeedbackAnswer, type FeedbackQuestion } from '@/api/feedback';

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
      class="text-ink-muted"
    >
      Loading the form…
    </p>

    <section
      v-else-if="unusable"
      data-testid="feedback-unusable"
      class="flex flex-col gap-3 rounded-2xl bg-surface-raised p-6"
    >
      <h1 class="text-xl font-semibold tracking-tight text-ink">
        This link no longer works
      </h1>
      <p class="text-ink-muted">
        A feedback link works once, and expires a month after it is sent. This one has already been used or has
        expired. If you still want to say something, the organisers can send you a new one.
      </p>
    </section>

    <p
      v-else-if="error"
      data-testid="feedback-error"
      class="text-danger"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="form">
      <header class="flex flex-col gap-1">
        <p class="text-sm uppercase tracking-widest text-ink-faint">
          {{ form.event.name }}
        </p>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          How was it?
        </h1>
      </header>

      <section
        v-if="done"
        data-testid="feedback-thanks"
        class="flex flex-col gap-2 rounded-2xl bg-surface-raised p-6"
      >
        <p class="text-lg text-success">
          Thank you.
        </p>
        <p class="text-ink-muted">
          Your answers went to the organisers without your name on them.
        </p>
      </section>

      <template v-else>
        <section
          v-for="question in form.questions"
          :key="question.id"
          :data-testid="`question-${question.id}`"
          class="flex flex-col gap-3 rounded-2xl bg-surface-raised p-5"
        >
          <p class="text-ink">
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
              class="min-h-11 flex-1 rounded-lg border py-2.5 text-lg tabular-nums"
              :class="ratings[question.id] === rating
                ? 'border-accent bg-accent text-accent-ink'
                : 'border-border text-ink'"
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
            class="rounded-lg border border-border bg-surface-sunken px-3 py-2.5 text-ink outline-none focus:border-accent"
          />
        </section>

        <button
          type="button"
          data-testid="submit-feedback"
          :disabled="submitting"
          class="rounded-xl bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
          @click="submit"
        >
          {{ submitting ? 'Sending…' : 'Send my feedback' }}
        </button>

        <p class="text-center text-sm text-ink-faint">
          Answers are stored without your name. The link works once.
        </p>

        <p
          v-if="problem"
          data-testid="feedback-problem"
          class="text-danger"
        >
          {{ problem }}
        </p>
      </template>
    </template>
  </main>
</template>
