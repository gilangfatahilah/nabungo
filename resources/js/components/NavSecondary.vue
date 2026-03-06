<script setup lang="ts">
import type { Component } from "vue"

import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'
import { Link } from "@inertiajs/vue3"

interface NavItem {
    title: string
    url: string
    icon?: Component
}

defineProps<{
    items: NavItem[]
}>()
</script>

<template>
    <SidebarGroup>
        <SidebarGroupLabel>
            Others
        </SidebarGroupLabel>
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton as-child>
                        <Link :href="item.url">
                            <component :is="item.icon" v-if="item.icon" />
                            {{ item.title }}
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
