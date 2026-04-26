<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import type { ReportSummary } from '@/types/report'
import { TrendingDown, TrendingUp } from 'lucide-vue-next'

defineProps<{ data: ReportSummary }>()

const formatIDR = (val: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val)
</script>

<template>
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <!-- Income -->
        <Card>
            <CardHeader class="pb-2">
                <CardDescription>Total Income</CardDescription>
                <CardTitle class="text-2xl text-green-600">
                    {{ formatIDR(data.income.value) }}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                    <TrendingUp v-if="data.income.trend === 'up'" class="h-3 w-3 text-green-500" />
                    <TrendingDown v-else class="h-3 w-3 text-red-500" />
                    {{ data.income.change > 0 ? '+' : '' }}{{ data.income.change }}% vs prev period
                </div>
            </CardContent>
        </Card>

        <!-- Expense -->
        <Card>
            <CardHeader class="pb-2">
                <CardDescription>Total Expense</CardDescription>
                <CardTitle class="text-2xl text-red-600">
                    {{ formatIDR(data.expense.value) }}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                    <TrendingDown v-if="data.expense.trend === 'up'" class="h-3 w-3 text-green-500" />
                    <TrendingUp v-else class="h-3 w-3 text-red-500" />
                    {{ data.expense.change > 0 ? '+' : '' }}{{ data.expense.change }}% vs prev period
                </div>
            </CardContent>
        </Card>

        <!-- Net Savings -->
        <Card>
            <CardHeader class="pb-2">
                <CardDescription>Net Savings</CardDescription>
                <CardTitle
                    class="text-2xl"
                    :class="data.net.value >= 0 ? 'text-blue-600' : 'text-red-600'"
                >
                    {{ formatIDR(data.net.value) }}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                    <TrendingUp v-if="data.net.trend === 'up'" class="h-3 w-3 text-green-500" />
                    <TrendingDown v-else class="h-3 w-3 text-red-500" />
                    {{ data.net.change > 0 ? '+' : '' }}{{ data.net.change }}% vs prev period
                </div>
            </CardContent>
        </Card>

        <!-- Savings Rate -->
        <Card>
            <CardHeader class="pb-2">
                <CardDescription>Savings Rate</CardDescription>
                <CardTitle
                    class="text-2xl"
                    :class="data.savingsRate.value >= 20 ? 'text-green-600' : 'text-yellow-600'"
                >
                    {{ data.savingsRate.value }}%
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                    <TrendingUp v-if="data.savingsRate.trend === 'up'" class="h-3 w-3 text-green-500" />
                    <TrendingDown v-else class="h-3 w-3 text-red-500" />
                    {{ data.savingsRate.change > 0 ? '+' : '' }}{{ data.savingsRate.change }}pp vs prev period
                </div>
            </CardContent>
        </Card>
    </div>
</template>
