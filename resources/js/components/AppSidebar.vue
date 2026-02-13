<script setup lang="ts">
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'
import { mainNavItems } from "@/constants"
import { User } from '@/components/NavUser.vue';
import { usePage } from '@inertiajs/vue3';
import { IconInnerShadowTop } from '@tabler/icons-vue';


const page = usePage();
const user = page.props.auth.user as User;
</script>

<template>
    <Sidebar collapsible="offcanvas">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child class="data-[slot=sidebar-menu-button]:!p-1.5">
                        <a href="#">
                            <IconInnerShadowTop class="!size-5 text-primary" />
                            <span class="text-base font-semibold">Nabungo.</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <!-- <NavDocuments :items="data.documents" /> -->
            <!-- <NavSecondary :items="data.navSecondary" class="mt-auto" /> -->
        </SidebarContent>
        <SidebarFooter>
            <NavUser :user="user" />
        </SidebarFooter>
    </Sidebar>
</template>
