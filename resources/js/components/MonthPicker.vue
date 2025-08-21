<script setup lang="ts">
import { ref, computed } from "vue";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
// import { cn } from '@/lib/utils';

type Month = {
  number: number;
  name: string;
};

const MONTHS: Month[][] = [
  [
    { number: 0, name: "Jan" },
    { number: 1, name: "Feb" },
    { number: 2, name: "Mar" },
    { number: 3, name: "Apr" },
  ],
  [
    { number: 4, name: "May" },
    { number: 5, name: "Jun" },
    { number: 6, name: "Jul" },
    { number: 7, name: "Aug" },
  ],
  [
    { number: 8, name: "Sep" },
    { number: 9, name: "Oct" },
    { number: 10, name: "Nov" },
    { number: 11, name: "Dec" },
  ],
];

type ButtonVariant =
  | "default"
  | "outline"
  | "ghost"
  | "link"
  | "destructive"
  | "secondary";

interface Props {
  modelValue?: Date;
  minDate?: Date;
  maxDate?: Date;
  disabledDates?: Date[];
  callbacks?: {
    yearLabel?: (year: number) => string;
    monthLabel?: (month: Month) => string;
  };
  variant?: {
    calendar?: {
      main?: ButtonVariant;
      selected?: ButtonVariant;
    };
    chevrons?: ButtonVariant;
  };
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: "update:modelValue", value: Date): void;
}>();

const year = ref(props.modelValue?.getFullYear() ?? new Date().getFullYear());
const month = ref(props.modelValue?.getMonth() ?? new Date().getMonth());
const menuYear = ref(year.value);

const disabledDatesMapped = computed(
  () =>
    props.disabledDates?.map((d) => ({ year: d.getFullYear(), month: d.getMonth() })) ??
    []
);

function selectMonth(m: number) {
  year.value = menuYear.value;
  month.value = m;
  emit("update:modelValue", new Date(Date.UTC(menuYear.value, m, 1)));
}
</script>

<template>
  <div class="min-w-[200px] w-[280px] p-3">
    <div class="flex justify-center pt-1 relative items-center">
      <div class="text-sm font-medium">
        {{ props.callbacks?.yearLabel ? props.callbacks.yearLabel(menuYear) : menuYear }}
      </div>
      <div class="space-x-1 flex items-center">
        <Button
          :variant="props.variant?.chevrons ?? 'outline'"
          class="inline-flex items-center justify-center h-7 w-7 p-0 absolute left-1"
          @click="menuYear--"
        >
          <ChevronLeft class="opacity-50 h-4 w-4" />
        </Button>
        <Button
          :variant="props.variant?.chevrons ?? 'outline'"
          class="inline-flex items-center justify-center h-7 w-7 p-0 absolute right-1"
          @click="menuYear++"
        >
          <ChevronRight class="opacity-50 h-4 w-4" />
        </Button>
      </div>
    </div>
    <table class="w-full border-collapse">
      <tbody>
        <tr v-for="(monthRow, a) in MONTHS" :key="'row-' + a" class="flex w-full mt-2">
          <td
            v-for="m in monthRow"
            :key="m.number"
            class="h-10 w-1/4 text-center text-sm p-0 relative"
          >
            <Button
              :variant="
                month === m.number && menuYear === year
                  ? props.variant?.calendar?.selected ?? 'default'
                  : props.variant?.calendar?.main ?? 'ghost'
              "
              class="h-full w-full p-0 font-normal"
              :disabled="
                (props.maxDate
                  ? menuYear > props.maxDate.getFullYear() ||
                    (menuYear === props.maxDate.getFullYear() &&
                      m.number > props.maxDate.getMonth())
                  : false) ||
                (props.minDate
                  ? menuYear < props.minDate.getFullYear() ||
                    (menuYear === props.minDate.getFullYear() &&
                      m.number < props.minDate.getMonth())
                  : false) ||
                disabledDatesMapped.some(
                  (d) => d.year === menuYear && d.month === m.number
                )
              "
              @click="selectMonth(m.number)"
            >
              {{ props.callbacks?.monthLabel ? props.callbacks.monthLabel(m) : m.name }}
            </Button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
