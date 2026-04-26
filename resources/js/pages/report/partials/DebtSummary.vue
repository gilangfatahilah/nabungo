<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import type { DebtSummary } from '@/types/report'

defineProps<{ data: DebtSummary }>()

const formatIDR = (val: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val)
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Debt Summary</CardTitle>
            <CardDescription>Outstanding debts and receivables</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg bg-red-50 dark:bg-red-950/30 p-3 text-center">
                    <p class="text-xs text-muted-foreground mb-1">I Owe (Remaining)</p>
                    <p class="font-semibold text-red-600">{{ formatIDR(data.debt.remaining) }}</p>
                </div>
                <div class="rounded-lg bg-green-50 dark:bg-green-950/30 p-3 text-center">
                    <p class="text-xs text-muted-foreground mb-1">Owed to Me</p>
                    <p class="font-semibold text-green-600">{{ formatIDR(data.receivable.remaining) }}</p>
                </div>
            </div>

            <div v-if="data.overdue.length > 0">
                <p class="mb-2 text-xs font-medium text-destructive">⚠ Overdue Items</p>
                <div class="space-y-2">
                    <div
                        v-for="item in data.overdue"
                        :key="item.id"
                        class="flex items-center justify-between rounded-md border border-destructive/30 px-3 py-2 text-xs"
                    >
                        <div>
                            <p class="font-medium">{{ item.title }}</p>
                            <p class="text-muted-foreground">{{ item.contact_name }} · Due {{ item.due_date }}</p>
                        </div>
                        <span
                            :class="item.type === 'debt' ? 'text-red-600' : 'text-green-600'"
                            class="font-semibold"
                        >
                            {{ formatIDR(item.remaining) }}
                        </span>
                    </div>
                </div>
            </div>

            <div v-else class="text-xs text-muted-foreground text-center py-2">
                No overdue items. 🎉
            </div>
        </CardContent>
    </Card>
</template>
