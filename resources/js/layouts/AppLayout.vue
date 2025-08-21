<script setup lang="ts">
import "vue-sonner/style.css";

import { Toaster } from "@/components/ui/sonner";
import AppLayout from "@/layouts/app/AppSidebarLayout.vue";
import type { BreadcrumbItemType } from "@/types";
import { watch } from "vue";
import { toast } from "vue-sonner";

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
  errors: { [key: string]: string | string[] | undefined };
}

const props = withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});

watch(
  () => props.errors,
  (newErrors) => {
    if (newErrors && Object.keys(newErrors).length > 0) {
      toast.error("Something went wrong with the following errors:", {
        description: Object.values(newErrors).flat().join(", "),
      });
    }
  }
);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <slot />
  </AppLayout>

  <Toaster position="top-center" rich-colors expand />
</template>
