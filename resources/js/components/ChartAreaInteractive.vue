<script setup lang="ts">
import type { ChartConfig } from '@/components/ui/chart'

import { VisArea, VisAxis, VisLine, VisXYContainer } from "@unovis/vue"
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import {
    ChartContainer,
    ChartCrosshair,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
    componentToString,
} from '@/components/ui/chart'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { computed, ref } from 'vue'

interface NetworthItem {
    date: string;
    networth: number;
}

interface Props {
    data: NetworthItem[];
}

const props = defineProps<Props>();

const chartData = computed(() => {
    return props.data.map(item => ({
        date: new Date(item.date),
        networth: item.networth,
    }));
});

type Data = { date: Date; networth: number };

const chartConfig = {
    networth: {
        label: "Net Worth",
        color: "var(--primary)",
    },
} satisfies ChartConfig

const svgDefs = `
  <linearGradient id="fillNetworth" x1="0" y1="0" x2="0" y2="1">
    <stop
      offset="5%"
      stop-color="var(--color-networth)"
      stop-opacity="0.8"
    />
    <stop
      offset="95%"
      stop-color="var(--color-networth)"
      stop-opacity="0.1"
    />
  </linearGradient>
`

const timeRange = ref("12m")

const filterRange = computed(() => {
    const data = chartData.value;
    if (!data.length) return [];

    const referenceDate = new Date(data[data.length - 1].date);
    let monthsToShow = 12;

    if (timeRange.value === "6m") {
        monthsToShow = 6;
    } else if (timeRange.value === "3m") {
        monthsToShow = 3;
    }

    const startDate = new Date(referenceDate);
    startDate.setMonth(startDate.getMonth() - monthsToShow + 1);

    return data.filter((item) => {
        return item.date >= startDate;
    });
});

const yDomain = computed(() => {
    const values = filterRange.value.map(d => d.networth);
    if (!values.length) return [0, 1000000];

    const min = Math.min(...values);
    const max = Math.max(...values);
    const padding = (max - min) * 0.1 || max * 0.1;

    return [Math.max(0, min - padding), max + padding];
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

const timeRangeLabel = computed(() => {
    switch (timeRange.value) {
        case "3m": return "3 bulan terakhir";
        case "6m": return "6 bulan terakhir";
        default: return "12 bulan terakhir";
    }
});
</script>

<template>
    <Card class="pt-0">
        <CardHeader class="flex items-center gap-2 space-y-0 border-b py-5 sm:flex-row">
            <div class="grid flex-1 gap-1">
                <CardTitle>Pergerakan Net Worth</CardTitle>
                <CardDescription>
                    {{ timeRangeLabel }}
                </CardDescription>
            </div>
            <Select v-model="timeRange">
                <SelectTrigger class="hidden w-[160px] rounded-lg sm:ml-auto sm:flex" aria-label="Select a value">
                    <SelectValue placeholder="12 bulan terakhir" />
                </SelectTrigger>
                <SelectContent class="rounded-xl">
                    <SelectItem value="12m" class="rounded-lg">
                        12 bulan terakhir
                    </SelectItem>
                    <SelectItem value="6m" class="rounded-lg">
                        6 bulan terakhir
                    </SelectItem>
                    <SelectItem value="3m" class="rounded-lg">
                        3 bulan terakhir
                    </SelectItem>
                </SelectContent>
            </Select>
        </CardHeader>
        <CardContent class="px-2 pt-4 sm:px-6 sm:pt-6 pb-4">
            <ChartContainer :config="chartConfig" class="aspect-auto h-[250px] w-full" :cursor="false">
                <VisXYContainer :data="filterRange" :svg-defs="svgDefs" :margin="{ left: 10 }" :y-domain="yDomain">
                    <VisArea :x="(d: Data) => d.date" :y="(d: Data) => d.networth" color="url(#fillNetworth)"
                        :opacity="0.6" />
                    <VisLine :x="(d: Data) => d.date" :y="(d: Data) => d.networth" :color="chartConfig.networth.color"
                        :line-width="2" />
                    <VisAxis type="x" :x="(d: Data) => d.date" :tick-line="false" :domain-line="false"
                        :grid-line="false" :num-ticks="6" :tick-format="(d: number) => {
                            const date = new Date(d)
                            return date.toLocaleDateString('id-ID', {
                                month: 'short',
                                year: '2-digit',
                            })
                        }" />
                    <VisAxis type="y" :num-ticks="4" :tick-line="false" :domain-line="false"
                        :tick-format="(d: number) => formatCurrency(d)" />
                    <ChartTooltip />
                    <ChartCrosshair :template="componentToString(chartConfig, ChartTooltipContent, {
                        labelFormatter: (d) => {
                            return new Date(d).toLocaleDateString('id-ID', {
                                month: 'long',
                                year: 'numeric',
                            })
                        },
                    })" :color="chartConfig.networth.color" />
                </VisXYContainer>

                <ChartLegendContent />
            </ChartContainer>
        </CardContent>
    </Card>
</template>
