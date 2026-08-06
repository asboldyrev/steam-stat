<template>
    <div class="p-5">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-xl overflow-hidden" :class="game.gradient">
                <img v-if="game.icon_url" :src="game.icon_url" :alt="game.name" class="w-full h-full object-cover" @error="game.iconError = true" v-show="!game.iconError" />
                <div class="w-full h-full flex items-center justify-center text-white font-bold" :class="{ 'hidden': game.icon_url && !game.iconError }">
                    {{ game.abbreviation }}
                </div>
            </div>
        </div>
        <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-2">{{ game.name }}</h3>
        <!-- Удалена подпись издателя -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('library.gameCard.totalTime') }}</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ game.total_time }} {{ t('common.hours.full', game.total_time) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('library.gameCard.lastPlayed') }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ dayjs.unix(game.last_played).fromNow() }}</span>
            </div>
            <div class="pt-3 border-t border-steam">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ t('library.gameCard.platforms') }}</span>
                    <div class="flex items-center gap-2">
                        <span v-if="game.platforms.windows" class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-xs">{{ t('library.gameCard.platformWindows') }}</span>
                        <span v-if="game.platforms.deck" class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg text-xs">{{ t('library.gameCard.platformDeck') }}</span>
                        <span v-if="game.platforms.linux" class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg text-xs">{{ t('library.gameCard.platformLinux') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import dayjs from '@/bootstrap/dayjs.js'
    import { useI18n } from 'vue-i18n'

    const { t } = useI18n()

    defineProps({
        game: {
            type: Object,
            required: true
        }
    })
</script>
