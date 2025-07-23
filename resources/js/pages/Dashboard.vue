<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Chart from './dashboard/partials/IncomeExpenseChart.vue';
import PeriodSelect from './dashboard/partials/PeriodSelect.vue';
import StatsCard from './dashboard/partials/StatsCard.vue';
import AllocationChart from './dashboard/partials/AllocationChart.vue';
import { formatIdr } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import MainBalanceCard from './dashboard/partials/MainBalanceCard.vue';
import MonthlyBudgetCard from './dashboard/partials/MonthlyBudgetCard.vue';
import FullCalendar from '@/components/FullCalendar.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="grid md:grid-rows-2 gap-4 md:grid-cols-4">
        <div
          class="relative row-span-1 col-span-3 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <MainBalanceCard />
        </div>

        <div
          class="relative row-span-2 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <MonthlyBudgetCard />
        </div>

        <div
          class="relative w-full h-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <StatsCard />
        </div>
        <div
          class="relative w-full h-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <StatsCard />
        </div>
        <div
          class="relative w-full h-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <StatsCard />
        </div>
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div
          class="relative min-h-[70vh] bg-card col-span-3 md:col-span-2 flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min p-4 space-y-4"
        >
          <div class="flex justify-between">
            <h1 class="text-lg font-semibold">Statistics</h1>

            <PeriodSelect />
          </div>

          <Chart />
        </div>

        <div
          class="relative min-h-[50vh] bg-card col-span-3 md:col-span-1 flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min p-4 space-y-4 items-center"
        >
          <div class="h-full w-full flex flex-col justify-between items-center">
            <h1 class="text-2xl font-semibold tracking-wide">All Expenses</h1>
            <AllocationChart class="h-[250px]" />
            <p class="text-muted-foreground text-sm">
              Expense percentage of each category.
            </p>
          </div>
        </div>
      </div>

      <!-- <FullCalendar /> -->
    </div>
  </AppLayout>
</template>
