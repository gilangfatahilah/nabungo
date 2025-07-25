<script setup lang="ts">
import type { FunctionalComponent } from "vue";

defineProps<{
  icon: FunctionalComponent;
  text: string;
}>();
</script>

<template>
  <div class="flex items-center gap-2">
    <component :is="icon" class="w-4 h-4" />
    <span>{{ text }}</span>
  </div>
</template>
