<script setup lang="ts">
import type {
    ChartConfig,
} from "@/components/ui/chart"

import { VisAxis, VisGroupedBar, VisXYContainer } from "@unovis/vue"
import { IconTrendingUp, IconTrendingDown } from "@tabler/icons-vue"
import { computed } from "vue"
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import {
    ChartContainer,
    ChartCrosshair,
    ChartTooltip,
    ChartTooltipContent,
    componentToString,
} from "@/components/ui/chart"

interface IncomeExpenseItem {
    date: string;
    income: number;
    expense: number;
}

interface Props {
    data: IncomeExpenseItem[];
}

const props = defineProps<Props>();

const chartData = computed(() => {
    return props.data.map(item => ({
        date: new Date(item.date),
        income: item.income,
        expense: item.expense,
    }));
});

type Data = { date: Date; income: number; expense: number };

const chartConfig = {
    income: {
        label: "Pemasukan",
        color: "var(--chart-2)",
    },
    expense: {
        label: "Pengeluaran",
        color: "var(--chart-1)",
    },
} satisfies ChartConfig

const formatCurrency = (value: number): string => {
    if (value >= 1000000000) {
        return `Rp ${(value / 1000000000).toFixed(1)}M`;
    } else if (value >= 1000000) {
        return `Rp ${(value / 1000000).toFixed(1)}jt`;
    } else if (value >= 1000) {
        return `Rp ${(value / 1000).toFixed(0)}rb`;
    }
    return `Rp ${value.toFixed(0)}`;
};

const formatFullCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

// Calculate totals and trends
const totals = computed(() => {
    const totalIncome = props.data.reduce((acc, curr) => acc + curr.income, 0);
    const totalExpense = props.data.reduce((acc, curr) => acc + curr.expense, 0);
    const netSavings = totalIncome - totalExpense;

    // Calculate trend (comparing last 3 months vs previous 3 months)
    const dataLength = props.data.length;
    let recentNetSavings = 0;
    let previousNetSavings = 0;

    if (dataLength >= 6) {
        const recentData = props.data.slice(-3);
        const previousData = props.data.slice(-6, -3);

        recentNetSavings = recentData.reduce((acc, curr) => acc + (curr.income - curr.expense), 0);
        previousNetSavings = previousData.reduce((acc, curr) => acc + (curr.income - curr.expense), 0);
    }

    const trendPercentage = previousNetSavings !== 0
        ? ((recentNetSavings - previousNetSavings) / Math.abs(previousNetSavings)) * 100
        : 0;

    return {
        totalIncome,
        totalExpense,
        netSavings,
        trendPercentage: Math.round(trendPercentage * 10) / 10,
        trend: trendPercentage >= 0 ? 'up' : 'down',
    };
});

const averageMonthly = computed(() => {
    const months = props.data.length || 1;
    return {
        income: totals.value.totalIncome / months,
        expense: totals.value.totalExpense / months,
    };
});
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Pemasukan vs Pengeluaran</CardTitle>
            <CardDescription>Perbandingan bulanan selama 12 bulan terakhir</CardDescription>
        </CardHeader>
        <CardContent>
            <ChartContainer class="aspect-auto h-[250px] w-full" :config="chartConfig">
                <VisXYContainer :data="chartData">
                    <VisGroupedBar :x="(d: Data) => d.date" :y="[(d: Data) => d.income, (d: Data) => d.expense]"
                        :color="[chartConfig.income.color, chartConfig.expense.color]" :rounded-corners="4"
                        bar-padding="0.15" group-padding="0.1" />
                    <VisAxis type="x" :x="(d: Data) => d.date" :tick-line="false" :domain-line="false"
                        :grid-line="false" :num-ticks="12" :tick-format="(d: number) => {
                            const date = new Date(d)
                            return date.toLocaleDateString('id-ID', {
                                month: 'short',
                            })
                        }" :tick-values="chartData.map(d => d.date)" />
                    <VisAxis type="y" :num-ticks="4" :tick-line="false" :domain-line="false"
                        :tick-format="(d: number) => formatCurrency(d)" />
                    <ChartTooltip />
                    <ChartCrosshair :template="componentToString(chartConfig, ChartTooltipContent, {
                        indicator: 'dashed',
                        hideLabel: true,
                        labelFormatter: (d) => {
                            return new Date(d).toLocaleDateString('id-ID', {
                                month: 'long',
                                year: 'numeric',
                            })
                        },
                    })" color="#0000" />
                </VisXYContainer>
            </ChartContainer>
        </CardContent>
        <CardFooter class="flex-col items-start gap-2 text-sm">
            <div class="flex items-center gap-2 font-medium leading-none">
                <span v-if="totals.trend === 'up'" class="text-green-600">
                    Tabungan naik {{ Math.abs(totals.trendPercentage) }}% dari periode sebelumnya
                </span>
                <span v-else class="text-red-600">
                    Tabungan turun {{ Math.abs(totals.trendPercentage) }}% dari periode sebelumnya
                </span>
                <IconTrendingUp v-if="totals.trend === 'up'" class="h-4 w-4 text-green-600" />
                <IconTrendingDown v-else class="h-4 w-4 text-red-600" />
            </div>
            <div class="leading-none text-muted-foreground">
                Rata-rata pemasukan {{ formatFullCurrency(averageMonthly.income) }}/bulan,
                pengeluaran {{ formatFullCurrency(averageMonthly.expense) }}/bulan
            </div>
        </CardFooter>
    </Card>
</template>
