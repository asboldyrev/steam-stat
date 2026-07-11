<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-950 dark:to-gray-900">
    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 h-full w-64 border-r border-steam bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl z-40">
      <div class="p-6">
        <div class="flex items-center gap-3 mb-8">
          <div class="w-10 h-10 rounded-xl bg-steam-gradient flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ t('layout.appName') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('layout.appTagline') }}</p>
          </div>
        </div>

        <nav class="space-y-2">
          <router-link
            to="/"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-smooth hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400"
            active-class="bg-steam-gradient text-white hover:bg-steam-gradient/90"
            exact
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>{{ t('layout.menu.dashboard') }}</span>
          </router-link>
          <router-link
            to="/library"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-smooth hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400"
            active-class="bg-steam-gradient text-white hover:bg-steam-gradient/90"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
            </svg>
            <span>{{ t('layout.menu.gameLibrary') }}</span>
          </router-link>
        </nav>

        <div class="mt-12 p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl">
          <div v-if="loading" class="flex items-center justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-300 animate-pulse bg-gray-300 dark:bg-gray-700 rounded h-4 w-32"></p>
            <button disabled class="mt-2 w-full py-2 bg-gray-300 dark:bg-gray-700 text-gray-500 rounded-xl text-sm font-medium cursor-not-allowed">
              {{ t('layout.syncButton') }}
            </button>
          </div>
          <div v-else>
            <p v-if="syncError" class="text-sm text-red-500 dark:text-red-400">{{ syncError }}</p>
            <p v-else class="text-sm text-gray-600 dark:text-gray-300">{{ t('layout.lastSync', { time: lastSync || '...' }) }}</p>
            <button @click="handleSync" class="mt-2 w-full py-2 bg-steam-gradient text-white rounded-xl text-sm font-medium hover:opacity-90 transition-smooth disabled:opacity-50 disabled:cursor-not-allowed" :disabled="loading">
              {{ t('layout.syncButton') }}
            </button>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <main class="ml-64 p-8">
      <header class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ currentPageTitle }}</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">{{ t('layout.pageSubtitle') }}</p>
          </div>
          <div class="flex items-center gap-4">
            <!-- Поисковая строка удалена -->
            <!-- Кнопка переключения темы -->
            <button
              @click="toggleTheme"
              class="p-2.5 bg-white dark:bg-gray-800 border border-steam rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-smooth"
              :title="isDark ? 'Switch to light theme' : 'Switch to dark theme'"
            >
              <svg v-if="isDark" class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>
            <!-- Кнопка переключения языка -->
            <button
              @click="toggleLocale"
              class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-steam rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-smooth font-medium text-gray-700 dark:text-gray-300"
              :title="locale === 'en' ? 'Switch to Russian' : 'Switch to English'"
            >
              {{ locale === 'en' ? 'RU' : 'EN' }}
            </button>
          </div>
        </div>
      </header>

      <div class="max-w-7xl">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useTheme } from '../../composables/useTheme.js'
import { useLocale } from '../../composables/useLocale.js'
import { useApi } from '../../composables/useApi.js'

const { t } = useI18n()
const route = useRoute()
const { isDark, toggleTheme } = useTheme()
const { locale, toggleLocale } = useLocale()
const { getLastSync, triggerSync } = useApi()

const lastSync = ref('')
const loading = ref(false)
const syncError = ref(null)

const fetchLastSync = async () => {
    loading.value = true
    syncError.value = null
    try {
        const data = await getLastSync()
        lastSync.value = data.last_sync
    } catch (err) {
        console.error('Failed to fetch last sync:', err)
        syncError.value = err.message || 'Unknown error'
    } finally {
        loading.value = false
    }
}

const handleSync = async () => {
    loading.value = true
    syncError.value = null
    try {
        await triggerSync()
        // После успешного запуска синхронизации обновляем время
        await fetchLastSync()
    } catch (err) {
        console.error('Failed to trigger sync:', err)
        syncError.value = err.message || 'Unknown error'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchLastSync()
})

const currentPageTitle = computed(() => {
  const path = route.path
  if (path === '/') return t('layout.pageTitle.dashboard')
  if (path === '/library') return t('layout.pageTitle.gameLibrary')
  if (path.startsWith('/game/')) return t('layout.pageTitle.gameDetails')
  return t('layout.pageTitle.default')
})
</script>