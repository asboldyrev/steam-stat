<template>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card transition-smooth hover:shadow-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.stats.steamDeck') }}</p>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ loading ? '...' : stats.steam_deck_hours }} {{ t('common.hours.short') }}</h3>
                <p class="text-sm text-green-600 dark:text-green-400 mt-1">{{ t('dashboard.stats.ofTotalTime', { percent: stats.steam_deck_percentage }) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <Gamepad2 class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
        </div>
    </div>
</template>

<script setup>
    import { Gamepad2 } from '@lucide/vue';
    import { useI18n } from 'vue-i18n'

    const props = defineProps({
        loading: Boolean,
        stats: Object
    })

    const { t } = useI18n()
</script>
