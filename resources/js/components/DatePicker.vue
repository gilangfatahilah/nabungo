<script setup lang="ts">
import {
  DateFormatter,
  type DateValue,
  getLocalTimeZone,
  fromDate,
  toCalendarDate,
  CalendarDate,
} from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'
import { ref, watch, computed } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import Calendar from '@/components/Calendar.vue'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'

// Props dan emits
interface Props {
  modelValue?: Date
}
const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'update:modelValue', value: Date): void
}>()

// Konversi antara Date dan DateValue
const dateValueFromDate = (date: Date | undefined): DateValue | undefined => {
  if (!date) return undefined

  // Gunakan toCalendarDate untuk mempertahankan tanggal yang dipilih
  // tanpa memperhatikan timezone
  const dateValue = fromDate(date, getLocalTimeZone())
  return toCalendarDate(dateValue)
}

const dateFromDateValue = (dateValue: DateValue | undefined): Date | undefined => {
  if (!dateValue) return undefined

  // Buat Date dalam UTC tapi dengan tanggal yang benar
  const year = dateValue.year
  const month = dateValue.month - 1
  const day = dateValue.day

  // Menggunakan UTC constructor untuk menghindari timezone offset
  return new Date(Date.UTC(year, month, day, 0, 0, 0, 0)) 
}

// Internal state menggunakan DateValue untuk Calendar component
const internalDateValue = ref<DateValue | undefined>(
  dateValueFromDate(props.modelValue)
)

// Watch props.modelValue dan update internal state
watch(() => props.modelValue, (newDate) => {
  internalDateValue.value = dateValueFromDate(newDate)
}, { immediate: true })

// Handle calendar value change
const handleCalendarChange = (newDateValue: DateValue | undefined) => {
  internalDateValue.value = newDateValue
  const newDate = dateFromDateValue(newDateValue)
  if (newDate) {
    emit('update:modelValue', newDate)
  }
}

// Computed untuk display text
const displayText = computed(() => {
  if (!internalDateValue.value) return "Pick a date"

  const df = new DateFormatter('en-US', {
    dateStyle: 'long',
  })

  return df.format(internalDateValue.value.toDate(getLocalTimeZone()))
})

// State untuk popover
const isOpen = ref(false)

// Close popover when date is selected
watch(internalDateValue, (newValue) => {
  if (newValue) {
    isOpen.value = false
  }
})
</script>

<template>
  <Popover v-model:open="isOpen">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="
          cn(
            'w-full justify-start text-left font-normal',
            !internalDateValue && 'text-muted-foreground'
          )
        "
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        {{ displayText }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0">
      <Calendar
        :model-value="internalDateValue"
        @update:model-value="handleCalendarChange"
        initial-focus
      />
    </PopoverContent>
  </Popover>
</template>
