import { defineStore } from 'pinia';
import { ref } from 'vue';

/** Long enough to read a sentence, short enough not to sit over the next screen. */
const SHOWN_FOR_MS = 5000;

/**
 * One short outcome, said once and gone of its own accord.
 *
 * For news that outlives the screen it happened on — "Event created" is said
 * on the screen the Organiser lands on, not the wizard they just left. A
 * screen reporting on itself uses an AppAlert instead.
 */
export const useToastStore = defineStore('toast', () => {
    const message = ref<string | null>(null);

    let timer: ReturnType<typeof setTimeout> | undefined;

    function show(text: string): void {
        clearTimeout(timer);
        message.value = text;
        timer = setTimeout(dismiss, SHOWN_FOR_MS);
    }

    function dismiss(): void {
        clearTimeout(timer);
        message.value = null;
    }

    return { message, show, dismiss };
});
