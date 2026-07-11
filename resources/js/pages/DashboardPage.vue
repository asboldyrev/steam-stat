<template>
  <div class="space-y-8">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card transition-smooth hover:shadow-hover">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.stats.totalPlaytime') }}</p>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">1,247h</h3>
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
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">87</h3>
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
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">312h</h3>
            <p class="text-sm text-green-600 dark:text-green-400 mt-1">25% {{ t('dashboard.stats.ofTotalTime') }}</p>
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
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-blue-500"></div>
              <span class="text-gray-700 dark:text-gray-300">{{ t('dashboard.platformDistribution.windows') }}</span>
            </div>
            <div class="flex items-center gap-4">
              <span class="font-medium text-gray-700 dark:text-gray-300">512h (41%)</span>
              <div class="w-48 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full" style="width: 41%"></div>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-green-500"></div>
              <span class="text-gray-700 dark:text-gray-300">{{ t('dashboard.platformDistribution.steamDeck') }}</span>
            </div>
            <div class="flex items-center gap-4">
              <span class="font-medium text-gray-700 dark:text-gray-300">312h (25%)</span>
              <div class="w-48 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-green-500 rounded-full" style="width: 25%"></div>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-purple-500"></div>
              <span class="text-gray-700 dark:text-gray-300">{{ t('dashboard.platformDistribution.linux') }}</span>
            </div>
            <div class="flex items-center gap-4">
              <span class="font-medium text-gray-700 dark:text-gray-300">248h (20%)</span>
              <div class="w-48 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-purple-500 rounded-full" style="width: 20%"></div>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-orange-500"></div>
              <span class="text-gray-700 dark:text-gray-300">{{ t('dashboard.platformDistribution.macOS') }}</span>
            </div>
            <div class="flex items-center gap-4">
              <span class="font-medium text-gray-700 dark:text-gray-300">98h (8%)</span>
              <div class="w-48 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-orange-500 rounded-full" style="width: 8%"></div>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-gray-500"></div>
              <span class="text-gray-700 dark:text-gray-300">{{ t('dashboard.platformDistribution.offline') }}</span>
            </div>
            <div class="flex items-center gap-4">
              <span class="font-medium text-gray-700 dark:text-gray-300">77h (6%)</span>
              <div class="w-48 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gray-500 rounded-full" style="width: 6%"></div>
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
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center">
              <span class="text-white font-bold">CS2</span>
            </div>
            <div class="flex-1">
              <h4 class="font-medium text-gray-900 dark:text-white">Counter-Strike 2</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.recentActivity.playedOn') }} Windows • Today</p>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">4h</span>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-emerald-400 flex items-center justify-center">
              <span class="text-white font-bold">ELD</span>
            </div>
            <div class="flex-1">
              <h4 class="font-medium text-gray-900 dark:text-white">Elden Ring</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.recentActivity.playedOn') }} Steam Deck • Yesterday</p>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">6h</span>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-400 flex items-center justify-center">
              <span class="text-white font-bold">BG3</span>
            </div>
            <div class="flex-1">
              <h4 class="font-medium text-gray-900 dark:text-white">Baldur's Gate 3</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.recentActivity.playedOn') }} Linux • 2 days ago</p>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">3h</span>
          </div>
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
           <tr class="border-b border-steam/50 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-smooth">
             <td class="py-4 px-4">
               <div class="flex items-center gap-3">
                 <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-600 to-blue-400"></div>
                 <div>
                   <h4 class="font-medium text-gray-900 dark:text-white">Counter-Strike 2</h4>
                   <!-- Удалена подпись издателя -->
                 </div>
               </div>
             </td>
             <td class="py-4 px-4 font-medium text-gray-700 dark:text-gray-300">248h</td>
             <td class="py-4 px-4 text-gray-500 dark:text-gray-400">Today</td>
           </tr>
           <tr class="border-b border-steam/50 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-smooth">
             <td class="py-4 px-4">
               <div class="flex items-center gap-3">
                 <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-600 to-emerald-400"></div>
                 <div>
                   <h4 class="font-medium text-gray-900 dark:text-white">Elden Ring</h4>
                   <!-- Удалена подпись издателя -->
                 </div>
               </div>
             </td>
             <td class="py-4 px-4 font-medium text-gray-700 dark:text-gray-300">186h</td>
             <td class="py-4 px-4 text-gray-500 dark:text-gray-400">Yesterday</td>
           </tr>
           <tr class="border-b border-steam/50 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-smooth">
             <td class="py-4 px-4">
               <div class="flex items-center gap-3">
                 <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 to-pink-400"></div>
                 <div>
                   <h4 class="font-medium text-gray-900 dark:text-white">Baldur's Gate 3</h4>
                   <!-- Удалена подпись издателя -->
                 </div>
               </div>
             </td>
             <td class="py-4 px-4 font-medium text-gray-700 dark:text-gray-300">142h</td>
             <td class="py-4 px-4 text-gray-500 dark:text-gray-400">3 days ago</td>
           </tr>
         </tbody>
       </table>
     </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>