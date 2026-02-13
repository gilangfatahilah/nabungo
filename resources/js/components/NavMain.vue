<script setup lang="ts">
import type { Component } from "vue"
import { ref } from "vue"
import { IconCirclePlusFilled } from "@tabler/icons-vue"

import { Button } from '@/components/ui/button'
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'
import { Link, usePage } from "@inertiajs/vue3"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { ArrowDownCircle, ArrowUpCircle, ArrowLeftRight } from "lucide-vue-next"
import FormDialog from "@/pages/transaction/partials/FormDialog.vue"

interface NavItem {
    title: string
    url: string
    icon?: Component
}

defineProps<{
    items: NavItem[]
}>()

const page = usePage();
const dialogOpen = ref(false);
const transactionType = ref<'income' | 'expense' | 'transfer'>('income');

const openTransactionDialog = (type: 'income' | 'expense' | 'transfer') => {
    transactionType.value = type;
    dialogOpen.value = true;
}
</script>

<template>
    <SidebarGroup>
        <SidebarGroupContent class="flex flex-col gap-2">
            <SidebarMenu>
                <SidebarMenuItem class="flex items-center gap-2">
                    <DropdownMenu as-child>
                        <DropdownMenuTrigger as-child>
                            <Button size="sm" class="w-full justify-start">
                                <IconCirclePlusFilled class="size-5 mr-2" />
                                <span>New Transaction</span>
                            </Button>
                            <DropdownMenuContent align="end" class="w-52">
                                <DropdownMenuItem @click="openTransactionDialog('income')">
                                    <ArrowDownCircle class="size-4 mr-2" />
                                    <span>Income</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="openTransactionDialog('expense')">
                                    <ArrowUpCircle class="size-4 mr-2" />
                                    <span>Expense</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="openTransactionDialog('transfer')">
                                    <ArrowLeftRight class="size-4 mr-2" />
                                    <span>Transfer</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenuTrigger>
                    </DropdownMenu>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton :tooltip="item.title" as-child :is-active="item.url === page.url">
                        <Link :href="item.url">
                            <component :is="item.icon" v-if="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>

    <FormDialog
        v-model:open="dialogOpen"
        :header="{
            title: 'Create New Transaction',
            description: 'Add a new transaction to your account'
        }"
        :initial-type="transactionType"
    />
</template>
