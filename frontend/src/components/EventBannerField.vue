<script setup lang="ts">
/**
 * The Banner an Event's header is drawn over, and the means to change it.
 *
 * It only says what the Organiser chose; it never uploads. The settings screen
 * sends a Banner the moment it is chosen, and the create-event wizard holds it
 * until there is an Event to send it to — so when is the parent's call.
 *
 * Type, size and dimensions are checked here first, against the same limits
 * the API enforces, so a file that could only be refused is refused without
 * the wait. That matters most in the create-event wizard, where the upload only
 * happens after the Event exists. Dimensions are read from the image's header
 * through an `<img>`, which is far lighter than decoding every pixel of a
 * phone photo. A browser that cannot read it leaves the dimensions to the API
 * rather than refusing a file that might be fine.
 *
 * Measuring takes a moment, so only the latest pick counts: a file chosen
 * before another, or before the banner was removed, is dropped when its
 * measurement comes back.
 */
import { Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import AppButton from '@/components/AppButton.vue';
import type { FieldErrors } from '@/lib/event-fields';

const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const MAX_BYTES = 8 * 1024 * 1024;
/** The smallest the API accepts (`StoreEventBannerRequest`), so the header is never upscaled. */
const MIN_WIDTH = 1200;
const MIN_HEIGHT = 400;

const props = withDefaults(defineProps<{
  banner: { large: string; small: string } | null;
  busy?: boolean;
  errors?: FieldErrors;
}>(), {
  busy: false,
  errors: () => ({}),
});

const emit = defineEmits<{
  choose: [file: File];
  remove: [];
}>();

const refused = ref<string | null>(null);

/** Bumped by every pick and every removal; a measurement from an older one is stale. */
let latestPick = 0;

const messages = computed(() => (refused.value === null ? props.errors.banner ?? [] : [refused.value]));

async function refusalOf(file: File): Promise<string | null> {
  if (!ACCEPTED_TYPES.includes(file.type)) {
    return 'The banner must be a JPEG, PNG or WebP image.';
  }

  if (file.size > MAX_BYTES) {
    return 'The banner must be 8MB or smaller.';
  }

  const size = await dimensionsOf(file);

  if (size !== null && (size.width < MIN_WIDTH || size.height < MIN_HEIGHT)) {
    return `The banner must be at least ${MIN_WIDTH} by ${MIN_HEIGHT} pixels.`;
  }

  return null;
}

/** The image's size, or null when this browser cannot read it. */
function dimensionsOf(file: File): Promise<{ width: number; height: number } | null> {
  if (typeof URL.createObjectURL !== 'function') {
    return Promise.resolve(null);
  }

  const url = URL.createObjectURL(file);

  return new Promise((resolve) => {
    const image = new Image();

    image.onload = () => {
      URL.revokeObjectURL(url);
      resolve({ width: image.naturalWidth, height: image.naturalHeight });
    };

    image.onerror = () => {
      URL.revokeObjectURL(url);
      resolve(null);
    };

    image.src = url;
  });
}

async function choose(payload: Event): Promise<void> {
  const input = payload.target as HTMLInputElement;
  const file = input.files?.[0];

  // Cleared so choosing the same file twice still fires a change.
  input.value = '';

  if (file === undefined || props.busy) {
    return;
  }

  const pick = ++latestPick;
  const refusal = await refusalOf(file);

  if (pick !== latestPick) {
    return;
  }

  refused.value = refusal;

  if (refusal === null) {
    emit('choose', file);
  }
}

function remove(): void {
  if (!props.busy) {
    latestPick++;
    refused.value = null;
    emit('remove');
  }
}
</script>

<template>
  <section class="flex flex-col gap-3">
    <h2 class="text-sm font-medium text-foreground">
      Banner
    </h2>

    <div
      v-if="banner"
      class="relative"
    >
      <img
        :src="banner.large"
        alt=""
        data-testid="event-banner-preview"
        class="aspect-[3/1] w-full rounded-lg object-cover"
      >

      <AppButton
        type="button"
        variant="danger"
        size="sm"
        data-testid="event-banner-remove"
        class="absolute end-2 top-2"
        :disabled="busy"
        @click="remove"
      >
        <Trash2 class="size-4 shrink-0" />
        <span class="sr-only">Remove banner</span>
      </AppButton>
    </div>

    <p class="text-xs text-muted-foreground">
      At least 1200 by 400, up to 8MB. JPEG, PNG or WebP.
    </p>

    <input
      type="file"
      :accept="ACCEPTED_TYPES.join(',')"
      data-testid="event-banner"
      :disabled="busy"
      class="block w-full text-sm text-muted-foreground-1 file:me-4 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground"
      @change="choose"
    >

    <p
      v-if="messages.length > 0"
      data-testid="event-banner-error"
      role="alert"
      class="text-sm text-destructive"
    >
      {{ messages.join(' ') }}
    </p>
  </section>
</template>
