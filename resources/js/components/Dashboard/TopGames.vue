<template>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('dashboard.topGames.title') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-steam">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">{{ t('dashboard.topGames.columns.game') }}</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">{{ t('dashboard.topGames.columns.totalTime') }}</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">{{ t('dashboard.topGames.columns.lastPlayed') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading" class="border-b border-steam/50">
                        <td colspan="3" class="py-8 text-center text-gray-500 dark:text-gray-400">
                            {{ t('common.loading') }}
                        </td>
                    </tr>
                    <tr v-else-if="games.length === 0" class="border-b border-steam/50">
                        <td colspan="3" class="py-8 text-center text-gray-500 dark:text-gray-400">
                            {{ t('common.noData') }}
                        </td>
                    </tr>
                    <tr v-else v-for="game in games" :key="game.game_id" class="border-b border-steam/50 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-smooth">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg overflow-hidden" :class="'bg-gradient-to-br ' + game.gradient">
                                    <img v-if="game.icon_url" :src="game.icon_url" :alt="game.game_name" class="w-full h-full object-cover" @error="game.iconError = true" v-show="!game.iconError" />
                                    <div class="w-full h-full" :class="{ 'hidden': game.icon_url && !game.iconError }"></div>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ game.game_name }}</h4>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 font-medium text-gray-700 dark:text-gray-300">{{ $formatNumber(game.total_time) }} {{ t('common.hours.short') }}</td>
                        <td class="py-4 px-4 text-gray-500 dark:text-gray-400">{{ game.last_played }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
    import { useI18n } from 'vue-i18n'

    const props = defineProps({
        loading: Boolean,
        games: Object
    })

    const { t } = useI18n()
</script>
