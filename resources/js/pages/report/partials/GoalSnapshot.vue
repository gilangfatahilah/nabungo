<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import type { GoalSnapshot } from '@/types/report'

defineProps<{ data: GoalSnapshot[] }>()

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
            <CardTitle>Goal Progress</CardTitle>
            <CardDescription>Current savings goal status</CardDescription>
        </CardHeader>
        <CardContent>
            <div
                v-if="data.length === 0"
                class="flex h-32 items-center justify-center text-sm text-muted-foreground"
            >
                No active goals.
            </div>
            <div v-else class="space-y-4">
                <div
                    v-for="g in data"
                    :key="g.id"
                    class="space-y-1.5"
                >
                    <div class="flex items-start justify-between text-xs">
                        <div>
                            <p class="font-medium text-sm">{{ g.title }}</p>
                            <p class="text-muted-foreground">
                                {{ formatIDR(g.saved_amount) }} / {{ formatIDR(g.target_amount) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <Badge
                                :variant="g.status === 'achieved' ? 'default' : 'secondary'"
                                class="text-[10px]"
                            >
                                {{ g.progress }}%
                            </Badge>
                            <p v-if="g.months_to_finish" class="mt-0.5 text-muted-foreground">
                                ~{{ g.months_to_finish }}mo to go
                            </p>
                            <p v-if="g.days_left !== null" class="text-muted-foreground">
                                {{ g.days_left > 0 ? Math.floor(g.days_left) + 'd left' : 'Overdue' }}
                            </p>
                        </div>
                    </div>
                    <Progress
                        :model-value="Math.min(g.progress, 100)"
                        :class="g.status === 'achieved' ? '[&>div]:bg-green-500' : ''"
                    />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
