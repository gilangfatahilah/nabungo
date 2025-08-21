<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";
import { Button } from "@/components/ui/button";

type ButtonVariant =
  | "default"
  | "outline"
  | "ghost"
  | "link"
  | "destructive"
  | "secondary"
  | "highlight"
  | null
  | undefined;

interface Month {
  number: number;
  name: string;
  yearOffset: number;
}

export interface QuickSelector {
  label: string;
  startMonth: Date;
  endMonth: Date;
  variant?: ButtonVariant;
  onClick?: (s: QuickSelector) => void;
}

interface DateRange {
  start: Date;
  end: Date;
}

const props = withDefaults(
  defineProps<{
    modelValue?: DateRange;
    onStartMonthSelect?: (date: Date) => void;
    callbacks?: {
      yearLabel?: (year: number) => string;
      monthLabel?: (month: Month) => string;
    };
    variant?: {
      calendar?: {
        main?: ButtonVariant;
        selected?: ButtonVariant;
        inRange?: ButtonVariant;
      };
      chevrons?: ButtonVariant;
    };
    minDate?: Date;
    maxDate?: Date;
    quickSelectors?: QuickSelector[];
    showQuickSelectors?: boolean;
  }>(),
  {
    showQuickSelectors: true,
  }
);

const emit = defineEmits<{
  "update:modelValue": [value: DateRange];
  onMonthRangeSelect: [value: DateRange];
}>();

// Generate months layout - optimized to avoid recreation on every render
const MONTHS: Month[][] = (() => {
  const months = [];
  for (let row = 0; row < 3; row++) {
    const monthRow: Month[] = [];
    for (let col = 0; col < 8; col++) {
      const monthNum = (row * 4 + (col % 4)) % 12;
      const yearOffset = col >= 4 ? 1 : 0;
      monthRow.push({
        number: monthNum,
        name: new Date(0, monthNum).toLocaleString("default", { month: "short" }),
        yearOffset,
      });
    }
    months.push(monthRow);
  }
  return months;
})();

// Initialize state
const today = new Date();
const defaultStart =
  props.modelValue?.start || new Date(today.getFullYear(), today.getMonth());
const defaultEnd =
  props.modelValue?.end || new Date(today.getFullYear() + 1, today.getMonth());

const startYear = ref(defaultStart.getFullYear());
const startMonth = ref(defaultStart.getMonth());
const endYear = ref(defaultEnd.getFullYear());
const endMonth = ref(defaultEnd.getMonth());
const menuYear = ref(startYear.value);
const rangePending = ref(false);

// Watch for external modelValue changes
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      startYear.value = newValue.start.getFullYear();
      startMonth.value = newValue.start.getMonth();
      endYear.value = newValue.end.getFullYear();
      endMonth.value = newValue.end.getMonth();
      menuYear.value = newValue.start.getFullYear();
      rangePending.value = false;
    }
  },
  { deep: true }
);

// Computed properties for better performance
const startDate = computed(() => new Date(startYear.value, startMonth.value));
const endDate = computed(() => new Date(endYear.value, endMonth.value));

const isMonthDisabled = (month: Month): boolean => {
  const monthDate = new Date(menuYear.value + month.yearOffset, month.number);

  if (
    props.minDate &&
    monthDate < new Date(props.minDate.getFullYear(), props.minDate.getMonth())
  ) {
    return true;
  }

  if (
    props.maxDate &&
    monthDate > new Date(props.maxDate.getFullYear(), props.maxDate.getMonth())
  ) {
    return true;
  }

  return false;
};

const isInRange = (month: Month): boolean => {
  if (rangePending.value) return false;

  const monthDate = new Date(menuYear.value + month.yearOffset, month.number);
  const start = startDate.value;
  const end = endDate.value;

  return monthDate > start && monthDate < end;
};

const isSelected = (month: Month): boolean => {
  const year = menuYear.value + month.yearOffset;
  return (
    (month.number === startMonth.value && year === startYear.value) ||
    (month.number === endMonth.value && year === endYear.value && !rangePending.value)
  );
};

const getMonthVariant = (month: Month): ButtonVariant => {
  if (isSelected(month)) {
    return props.variant?.calendar?.selected ?? "default";
  }
  if (isInRange(month)) {
    return props.variant?.calendar?.inRange ?? "highlight";
  }
  return props.variant?.calendar?.main ?? "ghost";
};

function handleSelect(month: Month) {
  if (isMonthDisabled(month)) return;

  const selectedYear = menuYear.value + month.yearOffset;
  const selectedDate = new Date(selectedYear, month.number);

  if (rangePending.value) {
    const isBeforeStart =
      selectedYear < startYear.value ||
      (selectedYear === startYear.value && month.number < startMonth.value);

    if (isBeforeStart) {
      // If selected date is before start, make it the new start
      startYear.value = selectedYear;
      startMonth.value = month.number;
      endYear.value = selectedYear;
      endMonth.value = month.number;
      props.onStartMonthSelect?.(selectedDate);
    } else {
      // Complete the range selection
      endYear.value = selectedYear;
      endMonth.value = month.number;
      rangePending.value = false;

      const newRange: DateRange = {
        start: new Date(Date.UTC(startYear.value, startMonth.value)),
        end: new Date(Date.UTC(selectedYear, month.number)),
      };

      emit("update:modelValue", newRange);
      emit("onMonthRangeSelect", newRange);
    }
  } else {
    // Start new range selection
    startYear.value = selectedYear;
    startMonth.value = month.number;
    endYear.value = selectedYear;
    endMonth.value = month.number;
    rangePending.value = true;
    props.onStartMonthSelect?.(selectedDate);
  }
}

function handleQuickSelect(selector: QuickSelector) {
  startYear.value = selector.startMonth.getFullYear();
  startMonth.value = selector.startMonth.getMonth();
  endYear.value = selector.endMonth.getFullYear();
  endMonth.value = selector.endMonth.getMonth();
  rangePending.value = false;

  const newRange: DateRange = {
    start: selector.startMonth,
    end: selector.endMonth,
  };

  emit("update:modelValue", newRange);
  emit("onMonthRangeSelect", newRange);
  selector.onClick?.(selector);
}

function navigateYear(direction: number) {
  menuYear.value += direction;
}
</script>

<template>
  <div class="min-w-[400px] p-3">
    <div class="flex flex-col sm:flex-row space-y-4 sm:space-x-4 sm:space-y-0">
      <div class="w-full">
        <!-- Year navigation header -->
        <div class="flex justify-evenly pt-1 relative items-center">
          <div class="text-sm font-medium">
            {{ props.callbacks?.yearLabel?.(menuYear) ?? menuYear }}
          </div>

          <div class="space-x-1 flex items-center">
            <Button
              :variant="props.variant?.chevrons ?? 'outline'"
              class="absolute left-1 h-7 w-7 p-0"
              @click="navigateYear(-1)"
              aria-label="Previous year"
            >
              <ChevronLeft class="opacity-50 h-4 w-4" />
            </Button>

            <Button
              :variant="props.variant?.chevrons ?? 'outline'"
              class="absolute right-1 h-7 w-7 p-0"
              @click="navigateYear(1)"
              aria-label="Next year"
            >
              <ChevronRight class="opacity-50 h-4 w-4" />
            </Button>
          </div>

          <div class="text-sm font-medium">
            {{ props.callbacks?.yearLabel?.(menuYear + 1) ?? menuYear + 1 }}
          </div>
        </div>

        <!-- Month grid -->
        <table class="w-full border-collapse">
          <tbody>
            <tr
              v-for="(row, rowIndex) in MONTHS"
              :key="`row-${rowIndex}`"
              class="flex w-full mt-2"
            >
              <td
                v-for="month in row"
                :key="`${month.number}-${month.yearOffset}`"
                class="w-1/4"
              >
                <Button
                  class="w-full"
                  :variant="getMonthVariant(month)"
                  :disabled="isMonthDisabled(month)"
                  @click="handleSelect(month)"
                  :aria-label="`Select ${month.name} ${menuYear + month.yearOffset}`"
                >
                  {{ props.callbacks?.monthLabel?.(month) ?? month.name }}
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Quick selectors -->
      <div
        v-if="props.showQuickSelectors && props.quickSelectors?.length"
        class="flex flex-col gap-1 justify-center"
      >
        <Button
          v-for="selector in props.quickSelectors"
          :key="selector.label"
          :variant="selector.variant ?? 'outline'"
          @click="handleQuickSelect(selector)"
        >
          {{ selector.label }}
        </Button>
      </div>
    </div>
  </div>
</template>
