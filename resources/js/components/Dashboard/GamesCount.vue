<template>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card transition-smooth hover:shadow-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.stats.gamesPlayed') }}</p>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ loading ? '...' : stats.games_count }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ t('dashboard.stats.acrossAllPlatforms') }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                <GalleryVerticalEnd class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
        </div>
    </div>
</template>

<script setup>
    import { GalleryVerticalEnd } from '@lucide/vue';
    import { useI18n } from 'vue-i18n'

    const props = defineProps({
        loading: Boolean,
        stats: Object
    })

    const { t } = useI18n()
</script>
