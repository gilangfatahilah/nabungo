<script setup lang="ts">
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { PlusIcon } from "lucide-vue-next";

interface Props {
  title: string;
  description?: string;
  addButton?: string;
}

defineProps<Props>();

defineEmits<{
  "click:add": [];
}>();
</script>

<template>
  <div class="flex items-center justify-between">
    <div class="mb-4 space-y-0.5">
      <h2 class="text-xl font-semibold tracking-tight">{{ title }}</h2>
      <p v-if="description" class="text-sm text-muted-foreground">
        {{ description }}
      </p>
    </div>

    <Button
      v-if="addButton"
      class="group relative flex items-center gap-2 px-6"
      @click="$emit('click:add')"
    >
      <div
        class="absolute left-0 z-10 flex h-full w-6 items-center justify-center rounded-md bg-primary pl-3 duration-300 group-hover:w-full group-hover:pl-0 cursor-pointer"
      >
        <PlusIcon />
      </div>

      <p class="translate-x-2">
        {{ addButton }}
      </p>
    </Button>
  </div>
  <Separator class="mb-4" />
</template>
