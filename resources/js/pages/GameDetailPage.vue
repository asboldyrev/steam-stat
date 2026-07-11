<template>
  <div class="space-y-8">
    <!-- Game Header -->
    <div class="flex items-start justify-between">
      <div class="flex items-center gap-6">
        <div v-if="loading.game" class="w-24 h-24 rounded-2xl bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
        <div v-else class="w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center">
          <span class="text-white text-3xl font-bold">{{ game.abbreviation }}</span>
        </div>
        <div>
          <h1 v-if="loading.game" class="text-4xl font-bold text-gray-900 dark:text-white animate-pulse bg-gray-200 dark:bg-gray-700 rounded h-10 w-64"></h1>
          <h1 v-else class="text-4xl font-bold text-gray-900 dark:text-white">{{ game.name }}</h1>
          <!-- Удалена строка с издателем, датой релиза и AppID -->
          <!-- Удалены теги жанров -->
        </div>
      </div>
      <!-- Удалены кнопки exportData и addNote -->
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('gameDetail.totalPlaytime') }}</p>
        <h3 v-if="loading.game" class="text-3xl font-bold text-gray-900 dark:text-white mt-2 animate-pulse bg-gray-200 dark:bg-gray-700 rounded h-10 w-24"></h3>
        <h3 v-else class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ game.total_playtime_hours }}h</h3>
        <!-- Удалена подпись sinceOct2023 -->
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('gameDetail.lastPlayed') }}</p>
        <h3 v-if="loading.game" class="text-3xl font-bold text-gray-900 dark:text-white mt-2 animate-pulse bg-gray-200 dark:bg-gray-700 rounded h-10 w-24"></h3>
        <h3 v-else class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ game.last_played }}</h3>
        <!-- Удалена подпись hoursAgo -->
      </div>
    </div>

    <!-- Platform Breakdown & Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
      <!-- Platform Breakdown -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">{{ t('gameDetail.platformUsage') }}</h3>
        <div v-if="loading.platform" class="space-y-6">
          <div v-for="n in 3" :key="n" class="animate-pulse">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                <span class="font-medium text-gray-300 dark:text-gray-600 bg-gray-300 dark:bg-gray-600 rounded h-4 w-24"></span>
              </div>
              <span class="font-bold text-gray-300 dark:text-gray-600 bg-gray-300 dark:bg-gray-600 rounded h-4 w-20"></span>
            </div>
            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div class="h-full bg-gray-300 dark:bg-gray-600 rounded-full" style="width: 30%"></div>
            </div>
          </div>
        </div>
        <div v-else class="space-y-6">
          <div v-for="platform in platformBreakdown.platforms" :key="platform.name">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full" :class="platform.color"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ platform.name }}</span>
              </div>
              <span class="font-bold text-gray-700 dark:text-gray-300">{{ platform.hours }}h ({{ platform.percentage }}%)</span>
            </div>
            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div class="h-full rounded-full" :class="platform.color" :style="{ width: platform.percentage + '%' }"></div>
            </div>
          </div>
        </div>
        <!-- Удалена секция Platform Insights -->
      </div>

      <!-- Playtime History -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">{{ t('gameDetail.playtimeHistory') }}</h3>
        <div v-if="loading.history" class="space-y-4">
          <div v-for="n in 7" :key="n" class="animate-pulse flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-10 h-6 bg-gray-300 dark:bg-gray-600 rounded"></div>
              <div class="w-16 h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
            </div>
            <div class="w-24 h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
          </div>
        </div>
        <div v-else-if="playtimeHistory.history.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
          {{ t('gameDetail.noPlaytimeHistory') }}
        </div>
        <div v-else class="space-y-4">
          <div v-for="item in playtimeHistory.history" :key="item.day" class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-10 text-center font-medium text-gray-700 dark:text-gray-300">{{ item.day }}</div>
              <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full" :class="item.color"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ item.platform }}</span>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <span class="font-medium text-gray-700 dark:text-gray-300">{{ item.hours }}h</span>
              <div class="w-32 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full" :class="item.color" :style="{ width: item.percentage + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Sessions -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">{{ t('gameDetail.recentSessions') }}</h3>
      <div v-if="loading.sessions" class="space-y-4">
        <div v-for="n in 5" :key="n" class="animate-pulse p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
          <div class="flex justify-between">
            <div class="w-32 h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
            <div class="w-16 h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
          </div>
        </div>
      </div>
      <div v-else-if="recentSessions.sessions.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
        {{ t('gameDetail.noRecentSessions') }}
      </div>
      <div v-else class="space-y-4">
        <div v-for="session in recentSessions.sessions" :key="session.id" class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="font-medium text-gray-900 dark:text-white">{{ session.date }}</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ session.timeOfDay }} • {{ session.notes }}</p>
            </div>
            <div class="flex items-center gap-4">
              <span class="font-medium text-gray-700 dark:text-gray-300">{{ session.duration }}</span>
              <span class="px-3 py-1 rounded-lg text-sm font-medium" :class="session.platformClass">{{ session.platform }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useApi } from '@/composables/useApi'

const { t } = useI18n()
const route = useRoute()
const { getGame, getGamePlatformBreakdown, getGamePlaytimeHistory, getGameRecentSessions } = useApi()

const game = ref({})
const platformBreakdown = ref({ platforms: [] })
const playtimeHistory = ref({ history: [] })
const recentSessions = ref({ sessions: [] })
const loading = ref({
    game: false,
    platform: false,
    history: false,
    sessions: false
})
const error = ref(null)

const fetchGameData = async () => {
    const gameId = route.params.id
    if (!gameId) return

    try {
        loading.value.game = true
        loading.value.platform = true
        loading.value.history = true
        loading.value.sessions = true

        const [gameData, platformData, historyData, sessionsData] = await Promise.all([
            getGame(gameId),
            getGamePlatformBreakdown(gameId),
            getGamePlaytimeHistory(gameId),
            getGameRecentSessions(gameId)
        ])

        game.value = gameData
        platformBreakdown.value = platformData
        playtimeHistory.value = historyData
        recentSessions.value = sessionsData
    } catch (err) {
        console.error('Failed to fetch game data:', err)
        error.value = err.message || 'Unknown error'
    } finally {
        loading.value.game = false
        loading.value.platform = false
        loading.value.history = false
        loading.value.sessions = false
    }
}

onMounted(() => {
    fetchGameData()
})
</script>