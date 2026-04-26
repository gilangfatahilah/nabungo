<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ChartContainer, ChartCrosshair, ChartTooltip, ChartTooltipContent, componentToString } from '@/components/ui/chart'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import type { ChartConfig } from '@/components/ui/chart'
import type { CashFlowGroup, CashFlowItem, Period } from '@/types/report'
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
    data: CashFlowItem[]
    group: CashFlowGroup
    period: Period
}>()

const formatCompact = (val: number) => {
    if (val >= 1_000_000_000) return `Rp ${(val / 1_000_000_000).toFixed(1)}M`
    if (val >= 1_000_000) return `Rp ${(val / 1_000_000).toFixed(1)}jt`
    if (val >= 1_000) return `Rp ${(val / 1_000).toFixed(0)}rb`
    return `Rp ${val.toFixed(0)}`
}

const chartConfig = {
    income: {
        label: 'Income',
        color: 'var(--chart-2)',
    },
    expense: {
        label: 'Expense',
        color: 'var(--chart-1)',
    },
} satisfies ChartConfig

const svgDefs = `
  <linearGradient id="fillIncome" x1="0" y1="0" x2="0" y2="1">
    <stop offset="5%" stop-color="var(--chart-2)" stop-opacity="0.8" />
    <stop offset="95%" stop-color="var(--chart-2)" stop-opacity="0.1" />
  </linearGradient>
  <linearGradient id="fillExpense" x1="0" y1="0" x2="0" y2="1">
    <stop offset="5%" stop-color="var(--chart-1)" stop-opacity="0.8" />
    <stop offset="95%" stop-color="var(--chart-1)" stop-opacity="0.1" />
  </linearGradient>
`

const chartData = computed(() =>
    props.data.map((d) => ({
        date: new Date(d.date),
        income: d.income,
        expense: d.expense,
    }))
)

type CfData = { date: Date; income: number; expense: number }

const groups: CashFlowGroup[] = ['daily', 'weekly', 'monthly']

const setGroup = (grp: CashFlowGroup) => {
    router.visit(route('report.index'), {
        method: 'get',
        data: {
            preset: props.period.preset,
            from: props.period.from,
            to: props.period.to,
            cash_flow_group: grp,
        },
        preserveState: true,
        preserveScroll: true,
    })
}
</script>

<template>
    <Card>
        <CardHeader class="flex-row items-start justify-between">
            <div>
                <CardTitle>Cash Flow</CardTitle>
                <CardDescription>Income vs Expense — {{ period.from }} to {{ period.to }}</CardDescription>
            </div>
            <Tabs :model-value="group" @update:model-value="(v) => setGroup(v as CashFlowGroup)">
                <TabsList>
                    <TabsTrigger v-for="grp in groups" :key="grp" :value="grp" class="capitalize">
                        {{ grp }}
                    </TabsTrigger>
                </TabsList>
            </Tabs>
        </CardHeader>
        <CardContent>
            <div
                v-if="data.length === 0"
                class="flex h-48 items-center justify-center text-sm text-muted-foreground"
            >
                No transaction data for this period.
            </div>
            <ChartContainer v-else class="aspect-auto h-[250px] w-full" :config="chartConfig" :cursor="false">
                <VisXYContainer :data="chartData" :svg-defs="svgDefs" :margin="{ left: -40 }">
                    <VisArea
                        :x="(d: CfData) => d.date"
                        :y="[(d: CfData) => d.income, (d: CfData) => d.expense]"
                        :color="(_: CfData, i: number) => ['url(#fillIncome)', 'url(#fillExpense)'][i]"
                        :opacity="0.6"
                    />
                    <VisLine
                        :x="(d: CfData) => d.date"
                        :y="[(d: CfData) => d.income, (d: CfData) => d.expense]"
                        :color="(_: CfData, i: number) => [chartConfig.income.color, chartConfig.expense.color][i]"
                        :line-width="1"
                    />
                    <VisAxis
                        type="x"
                        :x="(d: CfData) => d.date"
                        :tick-line="false"
                        :domain-line="false"
                        :grid-line="false"
                        :num-ticks="6"
                        :tick-format="(d: number) => new Date(d).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' })"
                    />
                    <VisAxis
                        type="y"
                        :num-ticks="4"
                        :tick-line="false"
                        :domain-line="false"
                        :tick-format="(d: number) => formatCompact(d)"
                    />
                    <ChartTooltip />
                    <ChartCrosshair :template="componentToString(chartConfig, ChartTooltipContent, {
                        indicator: 'dashed',
                        hideLabel: true,
                        labelFormatter: (d) => new Date(d).toLocaleDateString('id-ID', { month: 'long', day: 'numeric', year: 'numeric' }),
                    })" color="#0000" />
                </VisXYContainer>
            </ChartContainer>
            <div class="mt-2 flex items-center gap-4 text-xs text-muted-foreground">
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm" style="background:var(--chart-2)" />Income
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm" style="background:var(--chart-1)" />Expense
                </span>
            </div>
        </CardContent>
    </Card>
</template>
