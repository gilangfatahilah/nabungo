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

const props = defineProps<{
  modelValue: DateRange;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: DateRange];
  "popover:close": [value: boolean];
}>();

const internalValue = ref<DateRange>({
  start: props.modelValue.start,
  end: props.modelValue.end,
});

const handleOpenChange = (val: boolean) => {
  if (!val) emit("popover:close", true);
};

watch(internalValue, (newValue) => {
  if (
    newValue.start?.toString() !== props.modelValue.start?.toString() ||
    newValue.end?.toString() !== props.modelValue.end?.toString()
  ) {
    emit("update:modelValue", newValue);
  }
});

watch(
  () => props.modelValue,
  (newVal) => {
    if (
      newVal.start?.toString() !== internalValue.value.start?.toString() ||
      newVal.end?.toString() !== internalValue.value.end?.toString()
    ) {
      internalValue.value = { ...newVal };
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
            'w-[280px] justify-start text-left font-normal h-8',
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
