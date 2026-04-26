<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ChartContainer, ChartCrosshair, ChartTooltip, ChartTooltipContent, componentToString } from '@/components/ui/chart'
import type { ChartConfig } from '@/components/ui/chart'
import type { AccountTrend } from '@/types/report'
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue'
import { computed } from 'vue'

const props = defineProps<{ data: AccountTrend[] }>()

const formatIDR = (val: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val)

const formatCompact = (val: number) => {
    if (val >= 1_000_000_000) return `Rp ${(val / 1_000_000_000).toFixed(1)}M`
    if (val >= 1_000_000) return `Rp ${(val / 1_000_000).toFixed(1)}jt`
    if (val >= 1_000) return `Rp ${(val / 1_000).toFixed(0)}rb`
    return `Rp ${val.toFixed(0)}`
}

const trendColors = [
    'var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)',
    'var(--chart-4)', 'var(--chart-5)',
]

type TrendPoint = { date: Date; balance: number }

const accountCharts = computed(() =>
    props.data.map((account, idx) => {
        const color = trendColors[idx % trendColors.length]
        const gradientId = `fillBalance${idx}`
        const svgDefs = `
            <linearGradient id="${gradientId}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stop-color="${color}" stop-opacity="0.8" />
                <stop offset="95%" stop-color="${color}" stop-opacity="0.1" />
            </linearGradient>
        `
        const chartConfig: ChartConfig = {
            balance: { label: 'Balance', color },
        }
        const chartData = account.trend.map(t => ({
            date: new Date(t.date),
            balance: t.balance,
        }))
        return { account, color, gradientId, svgDefs, chartConfig, chartData }
    })
)
</script>

<template>
    <Card v-if="data.length > 0">
        <CardHeader>
            <CardTitle>Account Balance Trend</CardTitle>
            <CardDescription>Monthly balance progression per account</CardDescription>
        </CardHeader>
        <CardContent class="px-2 pt-4 sm:px-6 sm:pt-6 pb-4">
            <div
                v-for="({ account, color, gradientId, svgDefs, chartConfig, chartData }) in accountCharts"
                :key="account.account_id"
                class="mb-6 last:mb-0"
            >
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="font-medium">{{ account.account_name }}</span>
                    <span class="text-muted-foreground">{{ formatIDR(account.current) }}</span>
                </div>
                <ChartContainer :config="chartConfig" class="aspect-auto h-[150px] w-full" :cursor="false">
                    <VisXYContainer :data="chartData" :svg-defs="svgDefs" :margin="{ left: -40 }">
                        <VisArea
                            :x="(d: TrendPoint) => d.date"
                            :y="(d: TrendPoint) => d.balance"
                            :color="`url(#${gradientId})`"
                            :opacity="0.6"
                        />
                        <VisLine
                            :x="(d: TrendPoint) => d.date"
                            :y="(d: TrendPoint) => d.balance"
                            :color="color"
                            :line-width="1"
                        />
                        <VisAxis
                            type="x"
                            :x="(d: TrendPoint) => d.date"
                            :tick-line="false"
                            :domain-line="false"
                            :grid-line="false"
                            :num-ticks="6"
                            :tick-format="(d: number) => new Date(d).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' })"
                        />
                        <VisAxis
                            type="y"
                            :num-ticks="3"
                            :tick-line="false"
                            :domain-line="false"
                            :tick-format="(d: number) => formatCompact(d)"
                        />
                        <ChartTooltip />
                        <ChartCrosshair
                            :template="componentToString(chartConfig, ChartTooltipContent, {
                                indicator: 'dashed',
                                hideLabel: true,
                                labelFormatter: (d) => new Date(d).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }),
                            })"
                            color="#0000"
                        />
                    </VisXYContainer>
                </ChartContainer>
            </div>
        </CardContent>
    </Card>
</template>
