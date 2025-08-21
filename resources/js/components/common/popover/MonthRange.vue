<script setup lang="ts">
import { inject, ref } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Button } from "@/components/ui/button";
import { Calendar as CalendarIcon } from "lucide-vue-next";
import MonthRangePicker, { QuickSelector } from "@/components/MonthRangePicker.vue";
import { QueryParams } from "../data-table/DataTableToolbar.vue";
import { router } from "@inertiajs/vue3";

interface DateRange {
  start: Date;
  end: Date;
}

const quickSelectors: QuickSelector[] = [
  {
    label: "Last 3 Months",
    startMonth: new Date(new Date().getFullYear(), new Date().getMonth() - 2),
    endMonth: new Date(),
  },
  {
    label: "Last 6 Months",
    startMonth: new Date(new Date().getFullYear(), new Date().getMonth() - 5),
    endMonth: new Date(),
  },
  {
    label: "Current Year",
    startMonth: new Date(new Date().getFullYear(), 0),
    endMonth: new Date(new Date().getFullYear(), 11),
  },
  {
    label: "Last Year",
    startMonth: new Date(new Date().getFullYear() - 1, 0),
    endMonth: new Date(new Date().getFullYear() - 1, 11),
  },
];

const url = inject("url") as { index: string };
const query = inject("query") as QueryParams;

const firstDayThisMonth = new Date(
  Date.UTC(new Date().getUTCFullYear(), new Date().getUTCMonth(), 1)
);
const dateRange = ref<DateRange>({
  start: query.start_month ? new Date(query.start_month) : firstDayThisMonth,
  end: query.end_month ? new Date(query.end_month) : firstDayThisMonth,
});

const formatMonthYear = (d: Date) => {
  return d.toLocaleDateString("en-US", { month: "short", year: "numeric" });
};

const handleRangeSelect = (range: DateRange) => {
  router.get(
    route(url.index, {
      page: query.page ?? 1,
      per_page: query.per_page ?? 10,
      start_month: range.start,
      end_month: range.end,
    })
  );
};
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        size="sm"
        class="w-[280px] justify-start text-left font-normal"
        :class="{ 'text-muted-foreground': !dateRange }"
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        <span v-if="dateRange">
          {{ formatMonthYear(dateRange.start) }} - {{ formatMonthYear(dateRange.end) }}
        </span>
        <span v-else> Pick a month range </span>
      </Button>
    </PopoverTrigger>

    <PopoverContent class="w-auto p-0">
      <MonthRangePicker
        v-model="dateRange"
        :quick-selectors="quickSelectors"
        @on-month-range-select="handleRangeSelect"
      />
    </PopoverContent>
  </Popover>
</template>
