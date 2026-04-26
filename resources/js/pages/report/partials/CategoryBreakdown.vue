<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { ChartContainer, ChartTooltip } from '@/components/ui/chart'
import type { ChartConfig } from '@/components/ui/chart'
import type { CategoryBreakdown } from '@/types/report'
import { Donut } from '@unovis/ts'
import { VisDonut, VisSingleContainer } from '@unovis/vue'
import { isClient } from '@vueuse/core'
import { computed, h, render } from 'vue'

const props = defineProps<{ data: CategoryBreakdown[] }>()

const chartColors = [
    'var(--chart-1)',
    'var(--chart-2)',
    'var(--chart-3)',
    'var(--chart-4)',
    'var(--chart-5)',
]

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

type CatData = { name: string; total: number; percentage: number; fill: string }

const chartData = computed<CatData[]>(() =>
    props.data.map((c, i) => ({
        name: c.category_name,
        total: c.total,
        percentage: c.percentage,
        fill: chartColors[i % chartColors.length],
    }))
)

const totalExpense = computed(() => props.data.reduce((s, c) => s + c.total, 0))

const chartConfig = computed(() => {
    const config: ChartConfig = { total: { label: 'Total', color: undefined } }
    props.data.forEach((c, i) => {
        config[`cat-${i}`] = { label: c.category_name, color: chartColors[i % chartColors.length] }
    })
    return config
})

const getColor = (i: number) => chartColors[i % chartColors.length]

const topCategory = computed(() => {
    if (!props.data.length) return null
    return props.data.reduce((max, c) => (c.total > max.total ? c : max), props.data[0])
})

const tooltipTemplate = () => {
    if (!isClient) return undefined
    const cache = new Map<string, string>()
    return (rawData: any) => {
        const d: CatData = 'data' in rawData ? rawData.data : rawData
        const index: number = rawData.index ?? 0
        const key = `${d.name}-${d.total}`
        if (cache.has(key)) return cache.get(key)!
        const color = getColor(index)
        const div = document.createElement('div')
        render(
            h('div', { style: 'padding:6px 10px;font-size:12px;line-height:1.6' }, [
                h('div', { style: `display:flex;align-items:center;gap:6px;font-weight:600` }, [
                    h('span', { style: `width:10px;height:10px;border-radius:50%;background:${color};display:inline-block` }),
                    d.name,
                ]),
                h('div', {}, `${formatIDR(d.total)} (${d.percentage}%)`),
            ]),
            div
        )
        const html = div.innerHTML
        cache.set(key, html)
        return html
    }
}
</script>

<template>
    <Card class="flex flex-col">
        <CardHeader class="items-center pb-0">
            <CardTitle>Spending by Category</CardTitle>
            <CardDescription>Expense breakdown for the selected period</CardDescription>
        </CardHeader>
        <CardContent class="flex-1 pb-0">
            <div v-if="data.length === 0" class="flex h-[250px] items-center justify-center text-sm text-muted-foreground">
                No expense data.
            </div>
            <template v-else>
                <ChartContainer
                    :config="chartConfig"
                    class="mx-auto aspect-square max-h-[250px]"
                    :style="{
                        '--vis-donut-central-label-font-size': 'var(--text-xl)',
                        '--vis-donut-central-label-font-weight': 'var(--font-weight-bold)',
                        '--vis-donut-central-label-text-color': 'var(--foreground)',
                        '--vis-donut-central-sub-label-text-color': 'var(--muted-foreground)',
                    }"
                >
                    <VisSingleContainer :data="chartData" :margin="{ top: 30, bottom: 30 }">
                        <VisDonut
                            :value="(d: CatData) => d.total"
                            :color="(_: CatData, i: number) => getColor(i)"
                            :arc-width="30"
                            :central-label-offset-y="10"
                            :central-label="formatCompact(totalExpense)"
                            central-sub-label="Total"
                        />
                        <ChartTooltip :triggers="{ [Donut.selectors.segment]: tooltipTemplate() }" />
                    </VisSingleContainer>
                </ChartContainer>
                <div class="mt-3 space-y-1.5">
                    <div
                        v-for="(cat, i) in data"
                        :key="cat.category_id"
                        class="flex items-center justify-between text-xs"
                    >
                        <div class="flex items-center gap-1.5">
                            <span
                                class="inline-block h-2 w-2 rounded-full flex-shrink-0"
                                :style="{ background: getColor(i) }"
                            />
                            <span class="text-muted-foreground">{{ cat.category_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ formatIDR(cat.total) }}</span>
                            <span class="w-8 text-right text-muted-foreground">{{ cat.percentage }}%</span>
                        </div>
                    </div>
                </div>
            </template>
        </CardContent>
        <CardFooter class="flex-col gap-1 text-sm">
            <div v-if="topCategory" class="flex items-center gap-2 font-medium leading-none">
                {{ topCategory.category_name }} is the top category ({{ topCategory.percentage }}%)
            </div>
            <div class="leading-none text-muted-foreground">
                {{ data.length }} expense categories
            </div>
        </CardFooter>
    </Card>
</template>
