<script setup lang="ts">
import "vue-sonner/style.css";

import { Toaster } from "@/components/ui/sonner";
import AppLayout from "@/layouts/app/AppSidebarLayout.vue";
import type { BreadcrumbItemType } from "@/types";

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <slot />
  </AppLayout>

  <Toaster position="top-center" rich-colors />
</template>
