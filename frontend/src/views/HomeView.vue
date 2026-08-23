<script setup lang="ts">
import { readEnvironment } from '@/env';

const { apiUrl } = readEnvironment();
</script>

<template>
  <main class="mx-auto flex min-h-full max-w-md flex-col justify-center gap-2 p-6">
    <h1 class="text-2xl font-semibold">
      Battlezones
    </h1>
    <p class="text-sm text-gray-600">
      Talking to {{ apiUrl }}
    </p>
  </main>
</template>
