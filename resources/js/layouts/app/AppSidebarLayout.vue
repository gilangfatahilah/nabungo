<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import SiteHeader from '@/components/SiteHeader.vue'
import {
    SidebarInset,
    SidebarProvider,
} from '@/components/ui/sidebar'

import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <SidebarProvider :style="{
        '--sidebar-width': 'calc(var(--spacing) * 72)',
        '--header-height': 'calc(var(--spacing) * 12)',
    }">
        <AppSidebar variant="inset" />
        <SidebarInset>
            <SiteHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </SidebarInset>
    </SidebarProvider>
    <!-- <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell> -->
</template>
