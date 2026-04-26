<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import type { BudgetActual } from '@/types/report'

defineProps<{ data: BudgetActual[] }>()

const formatIDR = (val: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val)

const badgeVariant = (status: 'ok' | 'warning' | 'over') => {
    if (status === 'over') return 'destructive'
    if (status === 'warning') return 'secondary'
    return 'outline'
}
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Budget vs Actual</CardTitle>
            <CardDescription>Spending against your set budgets</CardDescription>
        </CardHeader>
        <CardContent>
            <div
                v-if="data.length === 0"
                class="flex h-40 items-center justify-center text-sm text-muted-foreground"
            >
                No budgets found for this period.
            </div>
            <div v-else class="space-y-4">
                <div
                    v-for="b in data"
                    :key="b.category_id"
                    class="space-y-1.5"
                >
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium">{{ b.category_name }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground">
                                {{ formatIDR(b.actual) }} / {{ formatIDR(b.budgeted) }}
                            </span>
                            <Badge :variant="badgeVariant(b.status)" class="text-[10px] h-4 px-1.5">
                                {{ b.usage }}%
                            </Badge>
                        </div>
                    </div>
                    <Progress
                        :model-value="Math.min(b.usage, 100)"
                        :class="{
                            '[&>div]:bg-green-500': b.status === 'ok',
                            '[&>div]:bg-yellow-500': b.status === 'warning',
                            '[&>div]:bg-red-500': b.status === 'over',
                        }"
                    />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
