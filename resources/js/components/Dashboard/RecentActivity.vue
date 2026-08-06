<template>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('dashboard.recentActivity.title') }}</h3>
        </div>
        <div class="space-y-4">
            <div v-if="loading" class="text-center py-4 text-gray-500 dark:text-gray-400">
                {{ t('common.loading') }}
            </div>
            <div v-else-if="activities.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
                {{ t('common.noData') }}
            </div>
            <div v-else v-for="activity in activities" :key="activity.game_id" class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-smooth">
                <div class="w-10 h-10 rounded-lg overflow-hidden" :class="'bg-gradient-to-br ' + activity.gradient">
                    <img v-if="activity.icon_url" :src="activity.icon_url" :alt="activity.game_name" class="w-full h-full object-cover" @error="activity.iconError = true" v-show="!activity.iconError" />
                    <div class="w-full h-full flex items-center justify-center text-white font-bold" :class="{ 'hidden': activity.icon_url && !activity.iconError }">
                        {{ activity.abbreviation }}
                    </div>
                </div>
                <div class="flex-1">
                    <h4 class="font-medium text-gray-900 dark:text-white">{{ activity.game_name }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.recentActivity.playedOn', { time: dayjs.duration({ minutes: activity.duration_minutes }).humanize(), platform: activity.platform }) }}</p>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ dayjs.unix(activity.last_played).fromNow() }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
    import dayjs from '@/bootstrap/dayjs.js'
    import { useI18n } from 'vue-i18n'

    const props = defineProps({
        loading: Boolean,
        activities: Object
    })

    const { t } = useI18n()
</script>
