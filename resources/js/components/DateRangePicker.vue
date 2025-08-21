<script setup lang="ts">
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-nocheck
import type { DateRange } from "reka-ui";
import { cn } from "@/lib/utils";

import { Button } from "@/components/ui/button";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { RangeCalendar } from "@/components/ui/range-calendar";
import { DateFormatter, getLocalTimeZone } from "@internationalized/date";
import { CalendarIcon } from "lucide-vue-next";
import { ref, watch } from "vue";

const df = new DateFormatter("en-US", {
  dateStyle: "medium",
});

// === ubah props agar menerima Date biasa ===
const props = defineProps<{
  modelValue: { start: Date | null; end: Date | null };
}>();

const emit = defineEmits<{
  "update:modelValue": [value: { start: Date | null; end: Date | null }];
  "popover:close": [value: boolean];
}>();

// internalValue tetap pakai CalendarDate (dari reka-ui)
const internalValue = ref<DateRange>({
  start: props.modelValue.start
    ? props.modelValue.start && props.modelValue.start.toCalendarDate?.()
    : null,
  end: props.modelValue.end
    ? props.modelValue.end && props.modelValue.end.toCalendarDate?.()
    : null,
});

const handleOpenChange = (val: boolean) => {
  if (!val) emit("popover:close", true);
};

// convert CalendarDate -> Date sebelum emit keluar
watch(internalValue, (newValue) => {
  const start = newValue.start ? newValue.start.toDate(getLocalTimeZone()) : null;
  const end = newValue.end ? newValue.end.toDate(getLocalTimeZone()) : null;

  if (
    start?.toString() !== props.modelValue.start?.toString() ||
    end?.toString() !== props.modelValue.end?.toString()
  ) {
    emit("update:modelValue", { start, end });
  }
});

// kalau props berubah (Date biasa), sync ke internalValue (CalendarDate)
watch(
  () => props.modelValue,
  (newVal) => {
    if (
      newVal.start?.toString() !==
        internalValue.value.start?.toDate(getLocalTimeZone())?.toString() ||
      newVal.end?.toString() !==
        internalValue.value.end?.toDate(getLocalTimeZone())?.toString()
    ) {
      internalValue.value = {
        start: newVal.start ? newVal.start.toCalendarDate?.() : null,
        end: newVal.end ? newVal.end.toCalendarDate?.() : null,
      };
    }
  }
);
</script>

<template>
  <Popover @update:open="handleOpenChange">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="
          cn(
            'w-full justify-start text-left font-normal h-8',
            !internalValue.start && 'text-muted-foreground'
          )
        "
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        <template v-if="internalValue.start">
          <template v-if="internalValue.end">
            {{ df.format(internalValue.start.toDate(getLocalTimeZone())) }} -
            {{ df.format(internalValue.end.toDate(getLocalTimeZone())) }}
          </template>
          <template v-else>
            {{ df.format(internalValue.start.toDate(getLocalTimeZone())) }}
          </template>
        </template>
        <template v-else>Pick a date</template>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0">
      <RangeCalendar
        v-model="internalValue"
        locale="id-ID"
        initial-focus
        :number-of-months="2"
        @update:start-value="(startDate) => (internalValue.start = startDate)"
      />
    </PopoverContent>
  </Popover>
</template>
