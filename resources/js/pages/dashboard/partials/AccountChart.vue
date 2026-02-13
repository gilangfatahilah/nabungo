<script setup lang="ts">
import type {
    ChartConfig,
} from "@/components/ui/chart"

import { Donut } from "@unovis/ts"
import { VisDonut, VisSingleContainer } from "@unovis/vue"
import { IconTrendingUp } from "@tabler/icons-vue"
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
    ChartTooltip,
} from "@/components/ui/chart"
import AccountChartTooltip from "./AccountChartTooltip.vue"
import { isClient } from "@vueuse/core"
import { h, render } from "vue"

interface AccountItem {
    id: number;
    name: string;
    type: string;
    balance: number;
    percentage: number;
}

interface Props {
    data: AccountItem[];
}

const props = defineProps<Props>();

// Chart colors for different accounts
const chartColors = [
    "var(--chart-1)",
    "var(--chart-2)",
    "var(--chart-3)",
    "var(--chart-4)",
    "var(--chart-5)",
];

const chartData = computed(() => {
    return props.data.map((account, index) => ({
        account: account.name,
        balance: account.balance,
        percentage: account.percentage,
        type: account.type,
        fill: chartColors[index % chartColors.length],
    }));
});

type Data = {
    account: string;
    balance: number;
    percentage: number;
    type: string;
    fill: string;
};

const chartConfig = computed(() => {
    const config: ChartConfig = {
        balance: {
            label: "Saldo",
            color: undefined,
        },
    };

    props.data.forEach((account, index) => {
        config[`account-${index}`] = {
            label: account.name,
            color: chartColors[index % chartColors.length],
        };
    });

    return config;
});

const totalBalance = computed(() => {
    return props.data.reduce((acc, curr) => acc + curr.balance, 0);
});

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

const topAccount = computed(() => {
    if (!props.data.length) return null;
    return props.data.reduce((max, account) =>
        account.balance > max.balance ? account : max
        , props.data[0]);
});

const getColor = (index: number) => chartColors[index % chartColors.length];

// Custom tooltip renderer for donut segments
const tooltipTemplate = () => {
    if (!isClient) return undefined;

    const cache = new Map<string, string>();

    return (rawData: any) => {
        const data = 'data' in rawData ? rawData.data : rawData;
        const index = rawData.index ?? 0;

        const cacheKey = `${data.account}-${data.balance}`;
        const cached = cache.get(cacheKey);
        if (cached) return cached;

        const color = getColor(index);
        const vnode = h(AccountChartTooltip, {
            payload: data,
            color: color,
        });

        const div = document.createElement('div');
        render(vnode, div);
        const html = div.innerHTML;
        cache.set(cacheKey, html);
        return html;
    };
};
</script>

<template>
    <Card class="flex flex-col">
        <CardHeader class="items-center pb-0">
            <CardTitle>Distribusi Saldo</CardTitle>
            <CardDescription>Per Akun</CardDescription>
        </CardHeader>
        <CardContent class="flex-1 pb-0">
            <ChartContainer v-if="chartData.length > 0" :config="chartConfig"
                class="mx-auto aspect-square max-h-[250px]" :style="{
                    '--vis-donut-central-label-font-size': 'var(--text-xl)',
                    '--vis-donut-central-label-font-weight': 'var(--font-weight-bold)',
                    '--vis-donut-central-label-text-color': 'var(--foreground)',
                    '--vis-donut-central-sub-label-text-color': 'var(--muted-foreground)',
                }">
                <VisSingleContainer :data="chartData" :margin="{ top: 30, bottom: 30 }">
                    <VisDonut :value="(d: Data) => d.balance" :color="(d: Data, i: number) => getColor(i)"
                        :arc-width="30" :central-label-offset-y="10" :central-label="formatCurrency(totalBalance)"
                        central-sub-label="Total" />
                    <ChartTooltip :triggers="{
                        [Donut.selectors.segment]: tooltipTemplate(),
                    }" />
                </VisSingleContainer>
            </ChartContainer>
            <div v-else class="flex items-center justify-center h-[250px] text-muted-foreground">
                Belum ada data akun
            </div>
        </CardContent>
        <CardFooter class="flex-col gap-2 text-sm">
            <div v-if="topAccount" class="flex items-center gap-2 font-medium leading-none">
                {{ topAccount.name }} ({{ topAccount.percentage }}%)
                <IconTrendingUp class="h-4 w-4 text-green-600" />
            </div>
            <div class="leading-none text-muted-foreground">
                {{ data.length }} akun aktif
            </div>
        </CardFooter>
    </Card>
</template>
