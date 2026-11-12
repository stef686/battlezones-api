<script setup lang="ts">
/**
 * A question that has to be answered before anything else happens: do the
 * thing, or don't.
 *
 * It is a native `<dialog>` opened with `showModal()`, as AccountDrawer is, so
 * the browser owns the focus trap, Esc and the inert page behind it. Vue owns
 * only whether it is open. Esc, a tap on the backdrop and the cancel button all
 * close it without confirming, and the cancel button takes the opening focus,
 * so a stray Enter never does the thing being asked about.
 */
import { ref, useId, watch } from 'vue';

import AppButton from '@/components/AppButton.vue';

withDefaults(defineProps<{
  title: string;
  confirmLabel: string;
  cancelLabel?: string;
  /** Danger by default; a question about something that is not destructive asks in primary. */
  confirmVariant?: 'primary' | 'danger';
  /** Names the dialog and, suffixed `-confirm` and `-cancel`, its buttons. */
  testid: string;
}>(), {
  cancelLabel: 'Cancel',
  confirmVariant: 'danger',
});

const emit = defineEmits<{ confirm: [] }>();

const open = defineModel<boolean>('open', { required: true });

const dialog = ref<HTMLDialogElement | null>(null);
const titleId = useId();

watch(open, (isOpen) => {
  if (isOpen) {
    dialog.value?.showModal();
  } else {
    dialog.value?.close();
  }
});

function confirm(): void {
  open.value = false;
  emit('confirm');
}
</script>

<template>
  <dialog
    ref="dialog"
    :data-testid="testid"
    :aria-labelledby="titleId"
    class="m-auto w-full max-w-sm rounded-xl border border-overlay-line bg-overlay p-0 text-foreground backdrop:bg-drawer-backdrop"
    @close="open = false"
    @click.self="open = false"
  >
    <!-- Fills the dialog edge to edge, so a click that reaches the dialog
         itself can only have landed on the backdrop. -->
    <div class="flex flex-col gap-4 p-5">
      <h2
        :id="titleId"
        class="text-lg font-semibold"
      >
        {{ title }}
      </h2>

      <div class="text-sm text-muted-foreground-1">
        <slot />
      </div>

      <div class="flex justify-end gap-3">
        <AppButton
          variant="secondary"
          :data-testid="`${testid}-cancel`"
          autofocus
          @click="open = false"
        >
          {{ cancelLabel }}
        </AppButton>
        <AppButton
          :variant="confirmVariant"
          :data-testid="`${testid}-confirm`"
          @click="confirm"
        >
          {{ confirmLabel }}
        </AppButton>
      </div>
    </div>
  </dialog>
</template>
