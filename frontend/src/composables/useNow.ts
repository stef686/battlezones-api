import { onBeforeUnmount, onMounted, ref, type Ref } from 'vue';

/**
 * The time, kept current while the component is mounted.
 *
 * For a screen that sorts what it shows by the clock: a `computed` that
 * calls `new Date()` reads it once and holds it until something else it
 * depends on changes, so a list left open across an Event's start keeps it
 * in the wrong place. This ticks on the device's own clock and asks the API
 * nothing — live data still comes from `useEventPulse` alone.
 */
export function useNow(everyMs = 60_000): Ref<Date> {
    const now = ref(new Date());
    let timer: ReturnType<typeof setInterval> | undefined;

    onMounted(() => {
        timer = setInterval(() => {
            now.value = new Date();
        }, everyMs);
    });

    onBeforeUnmount(() => {
        clearInterval(timer);
    });

    return now;
}
