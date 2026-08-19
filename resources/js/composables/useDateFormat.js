import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from '@/bootstrap/dayjs.js'

export function useDateFormat() {
    const { locale } = useI18n()

    const dayjsLocale = computed(() => locale.value === 'ru' ? 'ru' : 'en')

    const parseDate = (value) => {
        if (!value) return null

        if (dayjs.isDayjs(value)) {
            return value.locale(dayjsLocale.value)
        }

        if (value instanceof Date || typeof value === 'number') {
            const parsed = dayjs(value)
            return parsed.isValid() ? parsed.locale(dayjsLocale.value) : null
        }

        const stringValue = String(value)
        const parsed = /^\d{4}-\d{2}-\d{2}$/.test(stringValue)
            ? dayjs(`${stringValue}T00:00:00`)
            : dayjs(stringValue)

        return parsed.isValid() ? parsed.locale(dayjsLocale.value) : null
    }

    const formatDate = (value, format = 'DD.MM.YYYY') => {
        const date = parseDate(value)
        return date ? date.format(format) : '—'
    }

    const formatShortDate = (value) => formatDate(value, 'DD MMM')
    const formatLongDate = (value) => formatDate(value, 'D MMMM YYYY')
    const formatPeriod = (from, to) => `${formatDate(from)} — ${formatDate(to)}`

    const monthNames = computed(() => Array.from({ length: 12 }, (_, month) => (
        dayjs()
            .locale(dayjsLocale.value)
            .month(month)
            .date(1)
            .format('MMM')
    )))

    const weekdayNames = computed(() => {
        const monday = dayjs('2026-01-05').locale(dayjsLocale.value)

        return Array.from({ length: 7 }, (_, offset) => (
            monday.add(offset, 'day').format('dd').slice(0, 1)
        ))
    })

    return {
        dayjsLocale,
        formatDate,
        formatShortDate,
        formatLongDate,
        formatPeriod,
        monthNames,
        weekdayNames,
    }
}
