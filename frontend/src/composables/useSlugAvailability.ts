import { onScopeDispose, ref, toValue, watch, type MaybeRefOrGetter, type Ref } from 'vue';

import { useApiClient } from '@/api';
import { checkEventSlug } from '@/api/events';
import { slugProblem } from '@/lib/event-wizard';

/**
 * What is known about whether a slug is free: nothing yet, being asked, or
 * the API's answer.
 */
export type SlugAvailability = 'unknown' | 'checking' | 'available' | 'taken';

/** How long typing has to pause before the API is asked. */
export const SLUG_CHECK_DELAY_MS = 400;

/**
 * Whether the slug being typed is free, asked of the API once typing pauses.
 *
 * A slug the API would refuse for its shape is never asked about: the answer
 * is already known, and the wizard says so itself. An answer that arrives
 * for a slug the Organiser has since changed is dropped, so a slow reply can
 * never mark the slug now in the field. A failed check says nothing either
 * way; the create holds the last word on a taken slug regardless.
 */
export function useSlugAvailability(slug: MaybeRefOrGetter<string>): Ref<SlugAvailability> {
    const client = useApiClient();
    const availability = ref<SlugAvailability>('unknown');

    let timer: ReturnType<typeof setTimeout> | undefined;

    watch(() => toValue(slug), (candidate) => {
        clearTimeout(timer);

        if (slugProblem(candidate) !== null) {
            availability.value = 'unknown';

            return;
        }

        availability.value = 'checking';
        timer = setTimeout(() => {
            void checkEventSlug(client, candidate)
                .then(({ available }) => available ? 'available' as const : 'taken' as const)
                .catch(() => 'unknown' as const)
                .then((answer) => {
                    if (toValue(slug) === candidate) {
                        availability.value = answer;
                    }
                });
        }, SLUG_CHECK_DELAY_MS);
    });

    onScopeDispose(() => clearTimeout(timer));

    return availability;
}
