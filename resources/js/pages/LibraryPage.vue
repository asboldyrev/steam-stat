<template>
  <div class="space-y-8">
    <!-- Library Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('library.title') }}</h2>
        <p class="text-gray-500 dark:text-gray-400 mt-2">{{ t('library.subtitle') }}</p>
      </div>
      <div class="flex items-center gap-4">
        <div class="relative">
          <input
            type="text"
            :placeholder="t('library.filterPlaceholder')"
            class="pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-steam rounded-xl w-64 focus:outline-none focus:ring-2 focus:ring-steam-blue placeholder-gray-400 dark:placeholder-gray-500"
          />
          <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <select class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-steam rounded-xl focus:outline-none focus:ring-2 focus:ring-steam-blue">
          <option>{{ t('library.sortBy.playtime') }}</option>
          <option>{{ t('library.sortBy.name') }}</option>
          <option>{{ t('library.sortBy.lastPlayed') }}</option>
        </select>
        <!-- Удалена кнопка "Добавить игру" -->
      </div>
    </div>

    <!-- Platform Filters -->
    <div class="flex flex-wrap gap-3">
      <button class="px-4 py-2 bg-white dark:bg-gray-800 border border-steam rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-smooth text-gray-700 dark:text-gray-300">
        {{ t('library.platformFilters.all') }}
      </button>
      <button class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-200 dark:hover:bg-blue-800/50 transition-smooth">
        {{ t('library.platformFilters.windows') }}
      </button>
      <button class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800 rounded-xl hover:bg-green-200 dark:hover:bg-green-800/50 transition-smooth">
        {{ t('library.platformFilters.steamDeck') }}
      </button>
      <button class="px-4 py-2 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 rounded-xl hover:bg-purple-200 dark:hover:bg-purple-800/50 transition-smooth">
        {{ t('library.platformFilters.linux') }}
      </button>
      <!-- Удален фильтр macOS -->
    </div>

    <!-- Games Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div
        v-for="game in games"
        :key="game.id"
        class="bg-white dark:bg-gray-800 rounded-2xl border border-steam shadow-card hover:shadow-hover transition-smooth overflow-hidden group cursor-pointer"
      >
        <div class="p-5">
          <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-xl" :class="game.gradient">
              <div class="w-full h-full flex items-center justify-center text-white font-bold">
                {{ game.abbr }}
              </div>
            </div>
            <!-- Удалены индикаторы платформ (3 цветные точки) -->
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-2">{{ game.name }}</h3>
          <!-- Удалена подпись издателя -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('library.gameCard.totalTime') }}</span>
              <span class="font-medium text-gray-700 dark:text-gray-300">{{ game.totalTime }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('library.gameCard.lastPlayed') }}</span>
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ game.lastPlayed }}</span>
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
        <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-steam">
          <router-link
            :to="`/game/${game.id}`"
            class="w-full py-2.5 text-center bg-white dark:bg-gray-800 border border-steam rounded-xl text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 transition-smooth block text-gray-700 dark:text-gray-300"
          >
            {{ t('library.gameCard.viewDetails') }}
          </router-link>
        </div>
      </div>
    </div>

    <!-- Пагинация удалена -->
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const games = ref([
  {
    id: 1,
    name: 'Counter-Strike 2',
    abbr: 'CS2',
    publisher: 'Valve',
    gradient: 'bg-gradient-to-br from-blue-600 to-cyan-500',
    totalTime: '248h',
    lastPlayed: 'Today',
    platforms: { windows: true, deck: true, linux: true, mac: false }
  },
  {
    id: 2,
    name: 'Elden Ring',
    abbr: 'ELD',
    publisher: 'FromSoftware',
    gradient: 'bg-gradient-to-br from-green-600 to-emerald-500',
    totalTime: '186h',
    lastPlayed: 'Yesterday',
    platforms: { windows: true, deck: true, linux: false, mac: false }
  },
  {
    id: 3,
    name: 'Baldur\'s Gate 3',
    abbr: 'BG3',
    publisher: 'Larian Studios',
    gradient: 'bg-gradient-to-br from-purple-600 to-pink-500',
    totalTime: '142h',
    lastPlayed: '3 days ago',
    platforms: { windows: true, deck: true, linux: true, mac: false }
  },
  {
    id: 4,
    name: 'Cyberpunk 2077',
    abbr: 'CP',
    publisher: 'CD Projekt Red',
    gradient: 'bg-gradient-to-br from-yellow-600 to-orange-500',
    totalTime: '98h',
    lastPlayed: '1 week ago',
    platforms: { windows: true, deck: false, linux: false, mac: false }
  },
  {
    id: 5,
    name: 'Dota 2',
    abbr: 'D2',
    publisher: 'Valve',
    gradient: 'bg-gradient-to-br from-red-600 to-rose-500',
    totalTime: '512h',
    lastPlayed: 'Today',
    platforms: { windows: true, deck: true, linux: true, mac: true }
  },
  {
    id: 6,
    name: 'Hades',
    abbr: 'HAD',
    publisher: 'Supergiant Games',
    gradient: 'bg-gradient-to-br from-indigo-600 to-violet-500',
    totalTime: '64h',
    lastPlayed: '2 weeks ago',
    platforms: { windows: true, deck: true, linux: true, mac: true }
  },
  {
    id: 7,
    name: 'Stardew Valley',
    abbr: 'SDV',
    publisher: 'ConcernedApe',
    gradient: 'bg-gradient-to-br from-teal-600 to-emerald-500',
    totalTime: '78h',
    lastPlayed: '1 month ago',
    platforms: { windows: true, deck: true, linux: false, mac: true }
  },
  {
    id: 8,
    name: 'Portal 2',
    abbr: 'P2',
    publisher: 'Valve',
    gradient: 'bg-gradient-to-br from-gray-600 to-slate-500',
    totalTime: '42h',
    lastPlayed: '3 months ago',
    platforms: { windows: true, deck: false, linux: true, mac: true }
  },
])
</script>