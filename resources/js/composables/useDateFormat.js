import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const localeMap = {
    ru: 'ru-RU',
    en: 'en-US',
}

export function useDateFormat() {
    const { locale } = useI18n()

    const intlLocale = computed(() => localeMap[locale.value] || 'en-US')

    const parseDateOnly = (value) => {
        if (!value) return null
        const [year, month, day] = String(value).slice(0, 10).split('-').map(Number)
        if (!year || !month || !day) return null
        return new Date(year, month - 1, day)
    }

    const formatDate = (value, options = {}) => {
        const date = value instanceof Date ? value : parseDateOnly(value)
        if (!date) return '—'

        return new Intl.DateTimeFormat(intlLocale.value, {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            ...options,
        }).format(date)
    }

    const formatShortDate = (value) => formatDate(value, {
        day: '2-digit',
        month: 'short',
        year: undefined,
    })

    const formatPeriod = (from, to) => `${formatDate(from)} — ${formatDate(to)}`

    const monthNames = computed(() => Array.from({ length: 12 }, (_, month) => (
        new Intl.DateTimeFormat(intlLocale.value, { month: 'short' }).format(new Date(2026, month, 1))
    )))

    const weekdayNames = computed(() => Array.from({ length: 7 }, (_, offset) => (
        new Intl.DateTimeFormat(intlLocale.value, { weekday: 'narrow' }).format(new Date(2026, 0, 5 + offset))
    )))

    return {
        intlLocale,
        formatDate,
        formatShortDate,
        formatPeriod,
        monthNames,
        weekdayNames,
    }
}
