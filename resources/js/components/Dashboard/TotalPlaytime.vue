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

    const props = defineProps({
        loading: Boolean,
        stats: Object
    })

    const { t } = useI18n()

    const formatedTotalPlaytime = computed(() => {
        let playtime = props.stats.total_playtime_hours
        let result = []

        if (playtime > 8760) {
            const years = Math.floor(playtime / 8760)

            if (years) {
                result.push(years + ' ' + t('common.years', years))
                playtime = playtime % 8760
            }
        }

        if (playtime > 720) {
            const months = Math.floor(playtime / 720)

            if (months) {
                result.push(months + ' ' + t('common.months', months))
                playtime = playtime % 720
            }
        }

        if (playtime > 24) {
            const days = Math.floor(playtime / 24)

            if (days) {
                result.push(days + ' ' + t('common.days', days))
                playtime = playtime % 24
            }
        }

        if (playtime) {
            result.push(playtime + ' ' + t('common.hours.full', playtime))
        }

        return result.join(', ')
    })
</script>
