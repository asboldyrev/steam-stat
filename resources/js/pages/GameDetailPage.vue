<template>
  <div class="space-y-8">
    <!-- Game Header -->
    <div class="flex items-start justify-between">
      <div class="flex items-center gap-6">
        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center">
          <span class="text-white text-3xl font-bold">CS2</span>
        </div>
        <div>
          <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Counter-Strike 2</h1>
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
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">248h</h3>
        <!-- Удалена подпись sinceOct2023 -->
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('gameDetail.lastPlayed') }}</p>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ t('gameDetail.today') }}</h3>
        <!-- Удалена подпись hoursAgo -->
      </div>
    </div>

    <!-- Platform Breakdown & Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
      <!-- Platform Breakdown -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">{{ t('gameDetail.platformUsage') }}</h3>
        <div class="space-y-6">
          <div>
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ t('gameDetail.platformWindows') }}</span>
              </div>
              <span class="font-bold text-gray-700 dark:text-gray-300">180h (73%)</span>
            </div>
            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div class="h-full bg-blue-500 rounded-full" style="width: 73%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ t('gameDetail.platformSteamDeck') }}</span>
              </div>
              <span class="font-bold text-gray-700 dark:text-gray-300">48h (19%)</span>
            </div>
            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div class="h-full bg-green-500 rounded-full" style="width: 19%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ t('gameDetail.platformLinux') }}</span>
              </div>
              <span class="font-bold text-gray-700 dark:text-gray-300">20h (8%)</span>
            </div>
            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div class="h-full bg-purple-500 rounded-full" style="width: 8%"></div>
            </div>
          </div>
        </div>
        <!-- Удалена секция Platform Insights -->
      </div>

      <!-- Удалена секция Playtime History -->
    </div>

    <!-- Удалена секция Recent Sessions -->
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const playtimeHistory = ref([
  { day: 'Mon', hours: 3.5, platform: 'Windows', color: 'bg-blue-500', percentage: 70 },
  { day: 'Tue', hours: 2.0, platform: 'Steam Deck', color: 'bg-green-500', percentage: 40 },
  { day: 'Wed', hours: 4.2, platform: 'Windows', color: 'bg-blue-500', percentage: 84 },
  { day: 'Thu', hours: 1.5, platform: 'Linux', color: 'bg-purple-500', percentage: 30 },
  { day: 'Fri', hours: 5.0, platform: 'Windows', color: 'bg-blue-500', percentage: 100 },
  { day: 'Sat', hours: 6.3, platform: 'Steam Deck', color: 'bg-green-500', percentage: 126 },
  { day: 'Sun', hours: 6.0, platform: 'Windows', color: 'bg-blue-500', percentage: 120 },
])

const recentSessions = ref([
  { id: 1, date: 'Today', duration: '4h 12m', platform: 'Windows', platformClass: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300', timeOfDay: 'Afternoon', notes: 'Competitive matches' },
  { id: 2, date: 'Yesterday', duration: '2h 45m', platform: 'Steam Deck', platformClass: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300', timeOfDay: 'Evening', notes: 'Casual play' },
  { id: 3, date: 'Mar 10', duration: '3h 30m', platform: 'Windows', platformClass: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300', timeOfDay: 'Night', notes: 'Tournament practice' },
  { id: 4, date: 'Mar 9', duration: '1h 15m', platform: 'Linux', platformClass: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300', timeOfDay: 'Morning', notes: 'Quick matches' },
  { id: 5, date: 'Mar 8', duration: '5h 20m', platform: 'Windows', platformClass: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300', timeOfDay: 'Afternoon', notes: 'Streaming session' },
])
</script>