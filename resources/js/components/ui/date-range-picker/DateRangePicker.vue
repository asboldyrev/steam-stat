<script setup>
import { ref, watch } from 'vue'
import { parseDate } from '@internationalized/date'
import { CalendarDays, ChevronLeft, ChevronRight } from '@lucide/vue'
import { useI18n } from 'vue-i18n'
import {
    DateRangePickerCalendar,
    DateRangePickerCell,
    DateRangePickerCellTrigger,
    DateRangePickerContent,
    DateRangePickerGrid,
    DateRangePickerGridBody,
    DateRangePickerGridHead,
    DateRangePickerGridRow,
    DateRangePickerHeadCell,
    DateRangePickerHeader,
    DateRangePickerHeading,
    DateRangePickerNext,
    DateRangePickerPrev,
    DateRangePickerRoot,
    DateRangePickerTrigger,
} from 'reka-ui'
import { useDateFormat } from '@/composables/useDateFormat.js'

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['update:modelValue'])
const { locale } = useI18n()
const { formatPeriod } = useDateFormat()

const toRekaRange = (range) => ({
    start: range?.from ? parseDate(range.from) : undefined,
    end: range?.to ? parseDate(range.to) : undefined,
})

const value = ref(toRekaRange(props.modelValue))

watch(
    () => props.modelValue,
    (range) => {
        const next = toRekaRange(range)
        if (
            next.start?.toString() !== value.value?.start?.toString()
            || next.end?.toString() !== value.value?.end?.toString()
        ) {
            value.value = next
        }
    },
    { deep: true },
)

const updateValue = (range) => {
    value.value = range

    if (!range?.start || !range?.end) return

    emit('update:modelValue', {
        from: range.start.toString(),
        to: range.end.toString(),
    })
}
</script>

<template>
    <DateRangePickerRoot
        :model-value="value"
        :locale="locale === 'ru' ? 'ru-RU' : 'en-US'"
        :week-starts-on="1"
        :close-on-select="true"
        @update:model-value="updateValue"
    >
        <DateRangePickerTrigger
            class="inline-flex min-h-10 min-w-[260px] items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-3.5 text-sm font-medium text-gray-800 shadow-sm outline-none transition hover:bg-gray-50 focus-visible:ring-2 focus-visible:ring-blue-500/40 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700"
        >
            <span>{{ formatPeriod(modelValue.from, modelValue.to) }}</span>
            <CalendarDays class="h-4 w-4 shrink-0 text-gray-400" />
        </DateRangePickerTrigger>

        <DateRangePickerContent
            side="bottom"
            :side-offset="8"
            align="start"
            class="z-50 rounded-2xl border border-gray-200 bg-white p-4 shadow-xl outline-none dark:border-gray-700 dark:bg-gray-900"
        >
            <DateRangePickerCalendar v-slot="{ grid, weekDays }" class="w-[292px]">
                <DateRangePickerHeader class="mb-3 flex items-center justify-between">
                    <DateRangePickerPrev class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                        <ChevronLeft class="h-4 w-4" />
                    </DateRangePickerPrev>
                    <DateRangePickerHeading class="text-sm font-semibold text-gray-900 dark:text-white" />
                    <DateRangePickerNext class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                        <ChevronRight class="h-4 w-4" />
                    </DateRangePickerNext>
                </DateRangePickerHeader>

                <DateRangePickerGrid v-for="month in grid" :key="month.value.toString()" class="w-full border-collapse">
                    <DateRangePickerGridHead>
                        <DateRangePickerGridRow class="grid grid-cols-7">
                            <DateRangePickerHeadCell v-for="day in weekDays" :key="day" class="py-1 text-center text-xs font-medium text-gray-400">
                                {{ day }}
                            </DateRangePickerHeadCell>
                        </DateRangePickerGridRow>
                    </DateRangePickerGridHead>
                    <DateRangePickerGridBody>
                        <DateRangePickerGridRow v-for="(weekDates, index) in month.rows" :key="`week-${index}`" class="grid grid-cols-7">
                            <DateRangePickerCell v-for="date in weekDates" :key="date.toString()" :date="date" class="relative p-0.5 text-center">
                                <DateRangePickerCellTrigger
                                    :day="date"
                                    :month="month.value"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm text-gray-700 outline-none transition hover:bg-blue-50 data-[outside-view]:text-gray-300 data-[selected]:bg-blue-600 data-[selected]:text-white data-[today]:ring-1 data-[today]:ring-blue-500 dark:text-gray-200 dark:hover:bg-blue-950/50 dark:data-[outside-view]:text-gray-600"
                                />
                            </DateRangePickerCell>
                        </DateRangePickerGridRow>
                    </DateRangePickerGridBody>
                </DateRangePickerGrid>
            </DateRangePickerCalendar>
        </DateRangePickerContent>
    </DateRangePickerRoot>
</template>
