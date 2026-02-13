<script setup lang="ts">
import { IconTrendingDown, IconTrendingUp } from "@tabler/icons-vue"
import { computed } from "vue"

import { Badge } from '@/components/ui/badge'
import {
    Card,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'

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

interface Props {
    data: CardData;
}

const props = defineProps<Props>();

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatPercentage = (value: number): string => {
    const sign = value >= 0 ? '+' : '';
    return `${sign}${value}%`;
};

const cards = computed(() => [
    {
        title: 'Total Balance',
        value: formatCurrency(props.data.totalBalance.value),
        change: props.data.totalBalance.change,
        trend: props.data.totalBalance.trend,
        description: 'Total kekayaan bersih',
        footer: props.data.totalBalance.trend === 'up'
            ? 'Naik dari bulan lalu'
            : 'Turun dari bulan lalu',
    },
    {
        title: 'Income',
        value: formatCurrency(props.data.income.value),
        change: props.data.income.change,
        trend: props.data.income.trend,
        description: 'Pemasukan bulan ini',
        footer: props.data.income.trend === 'up'
            ? 'Pemasukan meningkat'
            : 'Pemasukan menurun',
    },
    {
        title: 'Expense',
        value: formatCurrency(props.data.expense.value),
        change: props.data.expense.change,
        trend: props.data.expense.trend,
        description: 'Pengeluaran bulan ini',
        footer: props.data.expense.trend === 'up'
            ? 'Pengeluaran terkendali'
            : 'Pengeluaran meningkat',
    },
    {
        title: 'Savings Rate',
        value: `${props.data.savingsRate.value}%`,
        change: props.data.savingsRate.change,
        trend: props.data.savingsRate.trend,
        description: 'Tingkat tabungan',
        footer: props.data.savingsRate.trend === 'up'
            ? 'Target tabungan tercapai'
            : 'Perlu tingkatkan tabungan',
    },
]);
</script>

<template>
    <div
        class="*:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card dark:*:data-[slot=card]:bg-card grid grid-cols-1 gap-4 px-4 *:data-[slot=card]:bg-gradient-to-t *:data-[slot=card]:shadow-xs lg:px-6 @xl/main:grid-cols-2 @5xl/main:grid-cols-4">
        <Card v-for="(card, index) in cards" :key="index" class="@container/card">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardDescription>{{ card.title }}</CardDescription>
                    <Badge :variant="'outline'" :class="card.trend === 'up' ? 'text-green-600' : 'text-red-600'">
                        <IconTrendingUp v-if="card.trend === 'up'" class="size-4" />
                        <IconTrendingDown v-else class="size-4" />
                        {{ formatPercentage(card.change) }}
                    </Badge>
                </div>
                <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                    {{ card.value }}
                </CardTitle>
            </CardHeader>
            <CardFooter class="flex-col items-start gap-1.5 text-sm">
                <div class="line-clamp-1 flex gap-2 font-medium">
                    {{ card.footer }}
                    <IconTrendingUp v-if="card.trend === 'up'" class="size-4 text-green-600" />
                    <IconTrendingDown v-else class="size-4 text-red-600" />
                </div>
                <div class="text-muted-foreground">
                    {{ card.description }}
                </div>
            </CardFooter>
        </Card>
    </div>
</template>
