<script setup lang="ts">
import {
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from "@/components/ui/dialog";
import { ScrollArea } from "@/components/ui/scroll-area";

interface Props {
  header: {
    title: string;
    description?: string;
  };
}

defineProps<Props>();
</script>

<template>
  <DialogContent class="sm:max-w-2xl">
    <ScrollArea class="max-h-[80dvh]">
      <DialogHeader class="py-3 px-2">
        <DialogTitle>{{ header.title }}</DialogTitle>
        <DialogDescription v-if="header.description">
          {{ header.description }}
        </DialogDescription>
      </DialogHeader>
      <div class="px-2">
        <slot />
      </div>
    </ScrollArea>
  </DialogContent>
</template>
