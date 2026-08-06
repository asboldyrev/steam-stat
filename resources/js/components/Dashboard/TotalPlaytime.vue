<template>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card transition-smooth hover:shadow-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.stats.totalPlaytime') }}</p>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ loading ? '...' : $formatNumber(stats.total_playtime_hours) }} {{ t('common.hours.full', stats.total_playtime_hours) }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ loading ? '...' : formatedTotalPlaytime }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <Clock class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
        </div>
    </div>
</template>

<script setup>
    import { Clock } from '@lucide/vue'
    import { computed } from 'vue'
    import { useI18n } from 'vue-i18n'
    import dayjs from '@/bootstrap/dayjs.js'

    const props = defineProps({
        loading: Boolean,
        stats: Object
    })

    const { t } = useI18n()

    const formatedTotalPlaytime = computed(() => {
        const duration = dayjs.duration(props.stats.total_playtime_hours, 'hours')

        const parts = [
            {
                value: duration.months(),
                forms: ['месяц', 'месяца', 'месяцев'],
            },
            {
                value: duration.days(),
                forms: ['день', 'дня', 'дней'],
            },
            {
                value: duration.hours(),
                forms: ['час', 'часа', 'часов'],
            },
        ]

        return parts
            .filter(({ value }) => value > 0)
            .map(({ value, forms }) => `${value} ${pluralize(value, forms)}`)
            .join(', ')
    })

    function pluralize(number, forms) {
        const abs = Math.abs(number) % 100
        const lastDigit = abs % 10

        if (abs >= 11 && abs <= 19) {
            return forms[2]
        }

        if (lastDigit === 1) {
            return forms[0]
        }

        if (lastDigit >= 2 && lastDigit <= 4) {
            return forms[1]
        }

        return forms[2]
    }
</script>
