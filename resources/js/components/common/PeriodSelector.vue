<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { RangeCalendar } from '@/components/ui/range-calendar'
import type { PeriodPreset } from '@/types/report'
import { CalendarDate, type DateValue, getLocalTimeZone, today } from '@internationalized/date'
import { router } from '@inertiajs/vue3'
import { CalendarDays, ChevronDown } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'

interface Props {
    preset: PeriodPreset
    from: string
    to: string
    routeName?: string
    extraParams?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
    routeName: 'report.index',
    extraParams: () => ({}),
})

type PresetOption = { label: string; value: PeriodPreset }

const presets: PresetOption[] = [
    { label: 'This Month', value: 'this_month' },
    { label: 'Last Month', value: 'last_month' },
    { label: 'Last 3 Months', value: 'last_3_months' },
    { label: 'Last 6 Months', value: 'last_6_months' },
    { label: 'This Year', value: 'this_year' },
    { label: 'Last Year', value: 'last_year' },
    { label: 'Custom Range', value: 'custom' },
]

const popoverOpen = ref(false)
const calendarOpen = ref(false)

// Convert ISO string to CalendarDate
const toCalendarDate = (dateStr: string): CalendarDate => {
    const d = new Date(dateStr)
    return new CalendarDate(d.getFullYear(), d.getMonth() + 1, d.getDate())
}

const customRange = ref<{ start: DateValue | undefined; end: DateValue | undefined }>({
    start: toCalendarDate(props.from),
    end: toCalendarDate(props.to),
})

const activePreset = computed(() =>
    presets.find((p) => p.value === props.preset)?.label ?? 'Custom Range'
)

const formatDisplay = computed(() => {
    if (props.preset !== 'custom') return activePreset.value
    return `${props.from} → ${props.to}`
})

const navigate = (preset: PeriodPreset, fromDate?: string, toDate?: string) => {
    const params: Record<string, string> = {
        ...props.extraParams,
        preset,
    }
    if (preset === 'custom' && fromDate && toDate) {
        params.from = fromDate
        params.to = toDate
    }
    router.visit(route(props.routeName), {
        method: 'get',
        data: params,
        preserveState: true,
        preserveScroll: true,
    })
    popoverOpen.value = false
    calendarOpen.value = false
}

const selectPreset = (value: PeriodPreset) => {
    if (value === 'custom') {
        calendarOpen.value = true
        return
    }
    navigate(value)
}

const applyCustomRange = () => {
    if (!customRange.value.start || !customRange.value.end) return
    const from = customRange.value.start.toString()
    const to = customRange.value.end.toString()
    navigate('custom', from, to)
}

watch(
    () => props.from,
    (v) => { customRange.value.start = toCalendarDate(v) }
)
watch(
    () => props.to,
    (v) => { customRange.value.end = toCalendarDate(v) }
)
</script>

<template>
    <Popover v-model:open="popoverOpen">
        <PopoverTrigger as-child>
            <Button variant="outline" class="flex items-center gap-2 min-w-[180px]">
                <CalendarDays class="h-4 w-4 text-muted-foreground" />
                <span class="flex-1 text-left text-sm">{{ formatDisplay }}</span>
                <ChevronDown class="h-3.5 w-3.5 text-muted-foreground" />
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-auto p-0" align="start">
            <!-- Preset list -->
            <div v-if="!calendarOpen" class="flex flex-col p-1">
                <button
                    v-for="p in presets"
                    :key="p.value"
                    class="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-accent transition-colors text-left"
                    :class="{ 'bg-accent font-medium': preset === p.value }"
                    @click="selectPreset(p.value)"
                >
                    {{ p.label }}
                </button>
            </div>

            <!-- Custom range calendar -->
            <div v-else class="p-3">
                <button
                    class="mb-2 flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground transition-colors"
                    @click="calendarOpen = false"
                >
                    ← Back
                </button>
                <RangeCalendar
                    v-model="customRange"
                    :max-value="today(getLocalTimeZone())"
                    initial-focus
                />
                <div class="mt-3 flex justify-end gap-2">
                    <Button variant="ghost" size="sm" @click="calendarOpen = false">Cancel</Button>
                    <Button
                        size="sm"
                        :disabled="!customRange.start || !customRange.end"
                        @click="applyCustomRange"
                    >
                        Apply
                    </Button>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
