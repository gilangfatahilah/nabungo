<script lang="ts">
export const iframeHeight = "800px"
export const description = "A financial dashboard with analytics cards and charts."
</script>

<script setup lang="ts">
import ChartAreaInteractive from '@/components/ChartAreaInteractive.vue'
import SectionCards from '@/components/SectionCards.vue'
import AccountChart from './partials/AccountChart.vue'
import IncomeExpenseChart from './partials/IncomeExpenseChart.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'

interface CardItem {
    value: number;
    change: number;
    trend: 'up' | 'down';
}

interface CardData {
    totalBalance: CardItem;
    income: CardItem;
    expense: CardItem;
    savingsRate: CardItem;
}

interface NetworthItem {
    date: string;
    networth: number;
}

interface AccountItem {
    id: number;
    name: string;
    type: string;
    balance: number;
    percentage: number;
}

interface IncomeExpenseItem {
    date: string;
    income: number;
    expense: number;
}

interface Props {
    errors: { [key: string]: string | string[] | undefined };
    cardData: CardData;
    networthData: NetworthItem[];
    accountData: AccountItem[];
    incomeExpenseData: IncomeExpenseItem[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Dashboard",
        href: "/dashboard",
    },
];
</script>

<template>
    <AppLayout :errors="errors" :breadcrumbs="breadcrumbs">

        <div class="flex flex-1 flex-col">
            <div class="@container/main flex flex-1 flex-col gap-2">
                <div class="flex flex-col gap-4 py-4 md:gap-6 md:py-6">
                    <SectionCards :data="cardData" />
                    <div class="px-4 lg:px-6 grid grid-cols-1 lg:grid-cols-6 gap-4 lg:gap-6">
                        <div class="lg:col-span-4">
                            <ChartAreaInteractive :data="networthData" />
                        </div>
                        <div class="lg:col-span-2">
                            <AccountChart :data="accountData" />
                        </div>
                    </div>
                    <div class="px-4 lg:px-6">
                        <IncomeExpenseChart :data="incomeExpenseData" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

</template>
