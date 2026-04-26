<script setup lang="ts">
import PeriodSelector from '@/components/common/PeriodSelector.vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem } from '@/types'
import type { ReportPageProps } from '@/types/report'
import { Download } from 'lucide-vue-next'
import AccountTrends from './partials/AccountTrends.vue'
import BudgetVsActual from './partials/BudgetVsActual.vue'
import CashFlowChart from './partials/CashFlowChart.vue'
import CategoryBreakdown from './partials/CategoryBreakdown.vue'
import DebtSummary from './partials/DebtSummary.vue'
import GoalSnapshot from './partials/GoalSnapshot.vue'
import SummaryCards from './partials/SummaryCards.vue'
import TopTransactions from './partials/TopTransactions.vue'

const props = defineProps<ReportPageProps & { errors: Record<string, string> }>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: '/report' },
]

const buildExportUrl = (format: 'csv' | 'pdf') => {
    const params = new URLSearchParams({
        format,
        preset: props.period.preset,
        from: props.period.from,
        to: props.period.to,
    })
    return `/report/export?${params.toString()}`
}
</script>

<template>
    <AppLayout :errors="errors" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 py-4 px-4 md:gap-6 md:py-6 md:px-6">

            <!-- Header bar -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Financial Report</h1>
                    <p class="text-sm text-muted-foreground mt-0.5">
                        {{ period.from }} → {{ period.to }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <PeriodSelector
                        :preset="period.preset"
                        :from="period.from"
                        :to="period.to"
                    />
                    <a :href="buildExportUrl('csv')" target="_blank">
                        <Button variant="outline" size="sm" class="gap-1.5">
                            <Download class="h-4 w-4" />
                            CSV
                        </Button>
                    </a>
                    <a :href="buildExportUrl('pdf')" target="_blank">
                        <Button variant="outline" size="sm" class="gap-1.5">
                            <Download class="h-4 w-4" />
                            PDF
                        </Button>
                    </a>
                </div>
            </div>

            <!-- ① Summary Cards -->
            <SummaryCards :data="summary" />

            <!-- ② Cash Flow Chart -->
            <CashFlowChart :data="cashFlow" :group="cashFlowGroup" :period="period" />

            <!-- ③ Category & Budget vs Actual -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <CategoryBreakdown :data="categoryBreakdown" />
                <BudgetVsActual :data="budgetVsActual" />
            </div>

            <!-- ④ Top Transactions -->
            <TopTransactions :data="topTransactions" />

            <!-- ⑤ Account Balance Trend -->
            <AccountTrends :data="accountTrends" />

            <!-- ⑥ Goals & Debt -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <GoalSnapshot :data="goalSnapshot" />
                <DebtSummary :data="debtSummary" />
            </div>

        </div>
    </AppLayout>
</template>
