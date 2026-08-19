<script setup>
import { computed } from 'vue'
import { parseDate } from '@internationalized/date'
import { useI18n } from 'vue-i18n'
import {
    DateRangePickerCalendar,
    DateRangePickerCell,
    DateRangePickerCellTrigger,
    DateRangePickerContent,
    DateRangePickerField,
    DateRangePickerGrid,
    DateRangePickerGridBody,
    DateRangePickerGridHead,
    DateRangePickerGridRow,
    DateRangePickerHeadCell,
    DateRangePickerHeader,
    DateRangePickerHeading,
    DateRangePickerInput,
    DateRangePickerNext,
    DateRangePickerPrev,
    DateRangePickerRoot,
    DateRangePickerTrigger,
} from 'reka-ui'

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['update:modelValue'])
const { locale } = useI18n()

const rekaLocale = computed(() => locale.value === 'ru' ? 'ru-RU' : 'en-US')

const value = computed({
    get() {
        return {
            start: parseDate(props.modelValue.from),
            end: parseDate(props.modelValue.to),
        }
    },
    set(range) {
        if (!range?.start || !range?.end) return
        emit('update:modelValue', {
            from: range.start.toString(),
            to: range.end.toString(),
        })
    },
})
</script>

<template>
    <DateRangePickerRoot v-model="value" :locale="rekaLocale" :week-starts-on="1">
        <DateRangePickerField class="flex min-h-10 items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm outline-none transition focus-within:ring-2 focus-within:ring-blue-500/40 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <DateRangePickerInput
                v-for="item in ['day', 'month', 'year']"
                :key="`start-${item}`"
                type="start"
                :part="item"
                class="rounded px-0.5 outline-none focus:bg-blue-50 dark:focus:bg-blue-950/50"
            />
            <span class="px-1 text-gray-400">—</span>
            <DateRangePickerInput
                v-for="item in ['day', 'month', 'year']"
                :key="`end-${item}`"
                type="end"
                :part="item"
                class="rounded px-0.5 outline-none focus:bg-blue-50 dark:focus:bg-blue-950/50"
            />
            <DateRangePickerTrigger class="ml-2 inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700" aria-label="Open calendar">
                <span aria-hidden="true">▣</span>
            </DateRangePickerTrigger>
        </DateRangePickerField>

        <DateRangePickerContent class="z-50 mt-2 rounded-2xl border border-gray-200 bg-white p-4 shadow-xl outline-none dark:border-gray-700 dark:bg-gray-900">
            <DateRangePickerCalendar v-slot="{ grid, weekDays }" class="w-full">
                <DateRangePickerHeader class="mb-3 flex items-center justify-between">
                    <DateRangePickerPrev class="inline-flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">‹</DateRangePickerPrev>
                    <DateRangePickerHeading class="text-sm font-semibold text-gray-900 dark:text-white" />
                    <DateRangePickerNext class="inline-flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">›</DateRangePickerNext>
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
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm text-gray-700 outline-none transition hover:bg-blue-50 data-[outside-view]:text-gray-300 data-[selected]:bg-blue-600 data-[selected]:text-white data-[today]:ring-1 data-[today]:ring-blue-500 dark:text-gray-200 dark:hover:bg-blue-950/50 dark:data-[outside-view]:text-gray-600"
                                />
                            </DateRangePickerCell>
                        </DateRangePickerGridRow>
                    </DateRangePickerGridBody>
                </DateRangePickerGrid>
            </DateRangePickerCalendar>
        </DateRangePickerContent>
    </DateRangePickerRoot>
</template>
