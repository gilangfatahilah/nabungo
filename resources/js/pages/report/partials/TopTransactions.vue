<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import type { TopTransactions } from '@/types/report'

defineProps<{ data: TopTransactions }>()

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
            <CardTitle>Top Transactions</CardTitle>
            <CardDescription>Largest income and expense transactions in this period</CardDescription>
        </CardHeader>
        <CardContent>
            <Tabs default-value="expense">
                <TabsList class="mb-4">
                    <TabsTrigger value="income">Top Income</TabsTrigger>
                    <TabsTrigger value="expense">Top Expense</TabsTrigger>
                </TabsList>

                <TabsContent value="income">
                    <div
                        v-if="data.income.length === 0"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        No income transactions.
                    </div>
                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-xs text-muted-foreground">
                                <th class="pb-2 text-left font-medium">Date</th>
                                <th class="pb-2 text-left font-medium">Description</th>
                                <th class="pb-2 text-left font-medium">Category</th>
                                <th class="pb-2 text-left font-medium">Account</th>
                                <th class="pb-2 text-right font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="tx in data.income"
                                :key="tx.id"
                                class="border-b last:border-0"
                            >
                                <td class="py-2 text-muted-foreground text-xs">{{ tx.transaction_date }}</td>
                                <td class="py-2 max-w-[160px] truncate">{{ tx.description ?? '—' }}</td>
                                <td class="py-2 text-muted-foreground">{{ tx.category ?? '—' }}</td>
                                <td class="py-2 text-muted-foreground">{{ tx.account ?? '—' }}</td>
                                <td class="py-2 text-right font-medium text-green-600">{{ formatIDR(tx.amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </TabsContent>

                <TabsContent value="expense">
                    <div
                        v-if="data.expense.length === 0"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        No expense transactions.
                    </div>
                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-xs text-muted-foreground">
                                <th class="pb-2 text-left font-medium">Date</th>
                                <th class="pb-2 text-left font-medium">Description</th>
                                <th class="pb-2 text-left font-medium">Category</th>
                                <th class="pb-2 text-left font-medium">Account</th>
                                <th class="pb-2 text-right font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="tx in data.expense"
                                :key="tx.id"
                                class="border-b last:border-0"
                            >
                                <td class="py-2 text-muted-foreground text-xs">{{ tx.transaction_date }}</td>
                                <td class="py-2 max-w-[160px] truncate">{{ tx.description ?? '—' }}</td>
                                <td class="py-2 text-muted-foreground">{{ tx.category ?? '—' }}</td>
                                <td class="py-2 text-muted-foreground">{{ tx.account ?? '—' }}</td>
                                <td class="py-2 text-right font-medium text-red-600">{{ formatIDR(tx.amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </TabsContent>
            </Tabs>
        </CardContent>
    </Card>
</template>
