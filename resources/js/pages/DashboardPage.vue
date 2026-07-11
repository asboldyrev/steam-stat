<template>
  <div class="space-y-8">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card transition-smooth hover:shadow-hover">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.stats.totalPlaytime') }}</p>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ loading.stats ? '...' : stats.total_playtime_hours }}h</h3>
            <!-- Удален тренд +12% -->
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card transition-smooth hover:shadow-hover">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.stats.gamesPlayed') }}</p>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ loading.stats ? '...' : stats.games_count }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ t('dashboard.stats.acrossAllPlatforms') }}</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card transition-smooth hover:shadow-hover">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.stats.steamDeck') }}</p>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ loading.stats ? '...' : stats.steam_deck_hours }}h</h3>
            <p class="text-sm text-green-600 dark:text-green-400 mt-1">{{ stats.steam_deck_percentage }}% {{ t('dashboard.stats.ofTotalTime') }}</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts & Platform Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Platform Distribution -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('dashboard.platformDistribution.title') }}</h3>
          <!-- Удален select timeRange -->
        </div>
        <div class="space-y-4">
          <div v-if="loading.platform" class="text-center py-4 text-gray-500 dark:text-gray-400">
            Loading platform distribution...
          </div>
          <div v-else-if="platformDistribution.platforms.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
            No platform data available.
          </div>
          <div v-else v-for="platform in platformDistribution.platforms" :key="platform.name" class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full" :class="platform.color"></div>
              <span class="text-gray-700 dark:text-gray-300">{{ platform.name }}</span>
            </div>
            <div class="flex items-center gap-4">
              <span class="font-medium text-gray-700 dark:text-gray-300">{{ platform.hours }}h ({{ platform.percentage }}%)</span>
              <div class="w-48 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full" :class="platform.color" :style="{ width: platform.percentage + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('dashboard.recentActivity.title') }}</h3>
          <button class="px-4 py-2 text-sm text-steam-accent hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-smooth text-gray-700 dark:text-gray-300">
            {{ t('dashboard.recentActivity.viewAll') }}
          </button>
        </div>
        <div class="space-y-4">
          <div v-if="loading.activity" class="text-center py-4 text-gray-500 dark:text-gray-400">
            Loading recent activity...
          </div>
          <div v-else-if="recentActivity.activities.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
            No recent activity.
          </div>
          <router-link
            v-else v-for="activity in recentActivity.activities"
            :key="activity.game_id"
            :to="`/game/${activity.game_id}`"
            class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-smooth cursor-pointer"
          >
            <div class="w-10 h-10 rounded-lg overflow-hidden" :class="'bg-gradient-to-br ' + activity.gradient">
              <img
                v-if="activity.icon_url"
                :src="activity.icon_url"
                :alt="activity.game_name"
                class="w-full h-full object-cover"
                @error="activity.iconError = true"
                v-show="!activity.iconError"
              />
              <div
                class="w-full h-full flex items-center justify-center text-white font-bold"
                :class="{ 'hidden': activity.icon_url && !activity.iconError }"
              >
                {{ activity.abbreviation }}
              </div>
            </div>
            <div class="flex-1">
              <h4 class="font-medium text-gray-900 dark:text-white">{{ activity.game_name }}</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.recentActivity.playedOn') }} {{ activity.platform }} • {{ activity.time_ago }}</p>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ activity.duration_hours }}h</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Top Games -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('dashboard.topGames.title') }}</h3>
        <!-- Удалены кнопки thisMonth/allTime -->
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
           <tr v-if="loading.topGames" class="border-b border-steam/50">
             <td colspan="3" class="py-8 text-center text-gray-500 dark:text-gray-400">
               Loading top games...
             </td>
           </tr>
           <tr v-else-if="topGames.games.length === 0" class="border-b border-steam/50">
             <td colspan="3" class="py-8 text-center text-gray-500 dark:text-gray-400">
               No top games data.
             </td>
           </tr>
           <tr v-else v-for="game in topGames.games" :key="game.game_id" class="border-b border-steam/50 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-smooth">
             <td class="py-4 px-4">
               <div class="flex items-center gap-3">
                 <div class="w-10 h-10 rounded-lg overflow-hidden" :class="'bg-gradient-to-br ' + game.gradient">
                   <img
                     v-if="game.icon_url"
                     :src="game.icon_url"
                     :alt="game.game_name"
                     class="w-full h-full object-cover"
                     @error="game.iconError = true"
                     v-show="!game.iconError"
                   />
                   <div
                     class="w-full h-full"
                     :class="{ 'hidden': game.icon_url && !game.iconError }"
                   ></div>
                 </div>
                 <div>
                   <h4 class="font-medium text-gray-900 dark:text-white">{{ game.game_name }}</h4>
                   <!-- Удалена подпись издателя -->
                 </div>
               </div>
             </td>
             <td class="py-4 px-4 font-medium text-gray-700 dark:text-gray-300">{{ game.total_time }}</td>
             <td class="py-4 px-4 text-gray-500 dark:text-gray-400">{{ game.last_played }}</td>
           </tr>
         </tbody>
       </table>
     </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useApi } from '@/composables/useApi'

const { t } = useI18n()
const { getDashboardStats, getPlatformDistribution, getRecentActivity, getTopGames } = useApi()

const stats = ref({
    total_playtime_hours: 0,
    games_count: 0,
    steam_deck_hours: 0,
    steam_deck_percentage: 0
})
const platformDistribution = ref({ platforms: [] })
const recentActivity = ref({ activities: [] })
const topGames = ref({ games: [] })
const loading = ref({
    stats: false,
    platform: false,
    activity: false,
    topGames: false
})
const error = ref(null)

const fetchDashboardData = async () => {
    try {
        loading.value.stats = true
        loading.value.platform = true
        loading.value.activity = true
        loading.value.topGames = true

        const [statsData, platformData, activityData, topGamesData] = await Promise.all([
            getDashboardStats(),
            getPlatformDistribution(),
            getRecentActivity(),
            getTopGames()
        ])

        stats.value = statsData
        platformDistribution.value = platformData
        // Добавляем поле iconError для отслеживания ошибок загрузки изображений
        const activities = activityData.activities || []
        activities.forEach(activity => {
          activity.iconError = false
        })
        recentActivity.value = { activities }
        
        // Добавляем поле iconError для top games
        const topGamesList = topGamesData.games || []
        topGamesList.forEach(game => {
          game.iconError = false
        })
        topGames.value = { games: topGamesList }
    } catch (err) {
        console.error('Failed to fetch dashboard data:', err)
        error.value = err.message || 'Unknown error'
    } finally {
        loading.value.stats = false
        loading.value.platform = false
        loading.value.activity = false
        loading.value.topGames = false
    }
}

onMounted(() => {
    fetchDashboardData()
})
</script>