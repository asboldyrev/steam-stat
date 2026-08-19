<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-950 dark:to-gray-900">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-black/40 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 w-72 border-r border-steam bg-white/90 backdrop-blur-xl transition-transform duration-200 dark:bg-gray-900/90 lg:w-64 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-full flex-col p-5 lg:p-6">
                <div class="mb-8 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-steam-gradient">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ t('layout.appName') }}</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('layout.appTagline') }}</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 lg:hidden"
                        @click="sidebarOpen = false"
                    >
                        <span class="sr-only">Close menu</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-2">
                    <router-link
                        v-for="item in navigation"
                        :key="item.to"
                        :to="item.to"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-smooth hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        active-class="bg-steam-gradient text-white hover:bg-steam-gradient/90"
                        :exact="item.exact"
                        @click="sidebarOpen = false"
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        <span>{{ t(item.labelKey) }}</span>
                    </router-link>
                </nav>

                <div class="mt-auto rounded-2xl bg-gray-50 p-4 dark:bg-gray-800">
                    <div v-if="loading" class="space-y-3">
                        <div class="h-4 w-32 animate-pulse rounded bg-gray-300 dark:bg-gray-700"></div>
                        <div class="h-9 w-full animate-pulse rounded-xl bg-gray-300 dark:bg-gray-700"></div>
                    </div>
                    <div v-else>
                        <p v-if="syncError" class="text-sm text-red-500 dark:text-red-400">{{ syncError }}</p>
                        <p v-else class="text-sm text-gray-600 dark:text-gray-300">{{ t('layout.lastSync', { time: lastSync || '...' }) }}</p>
                        <button
                            type="button"
                            class="mt-3 w-full rounded-xl bg-steam-gradient py-2 text-sm font-medium text-white transition-smooth hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="loading"
                            @click="handleSync"
                        >
                            {{ t('layout.syncButton') }}
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <main class="min-w-0 lg:ml-64">
            <div class="mx-auto w-full max-w-[1600px] px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
                <header class="mb-6 sm:mb-8">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3">
                            <button
                                type="button"
                                class="mt-0.5 rounded-xl border border-steam bg-white p-2.5 text-gray-700 dark:bg-gray-800 dark:text-gray-300 lg:hidden"
                                @click="sidebarOpen = true"
                            >
                                <span class="sr-only">Open menu</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <div class="min-w-0">
                                <h2 class="truncate text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">{{ currentPageTitle }}</h2>
                                <p class="mt-1 hidden text-sm text-gray-500 dark:text-gray-400 sm:block">{{ t('layout.pageSubtitle') }}</p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                            <button
                                type="button"
                                class="rounded-xl border border-steam bg-white p-2.5 transition-smooth hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                :title="isDark ? 'Switch to light theme' : 'Switch to dark theme'"
                                @click="toggleTheme"
                            >
                                <svg v-if="isDark" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <svg v-else class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="rounded-xl border border-steam bg-white px-3 py-2.5 font-medium text-gray-700 transition-smooth hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 sm:px-4"
                                :title="locale === 'en' ? 'Switch to Russian' : 'Switch to English'"
                                @click="toggleLocale"
                            >
                                {{ locale === 'en' ? 'RU' : 'EN' }}
                            </button>
                        </div>
                    </div>
                </header>

                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed, h, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useTheme } from '@/composables/useTheme.js'
import { useLocale } from '@/composables/useLocale.js'
import { useApi } from '@/composables/useApi.js'

const { t } = useI18n()
const route = useRoute()
const { isDark, toggleTheme } = useTheme()
const { locale, toggleLocale } = useLocale()
const { getLastSync, triggerSync } = useApi()

const lastSync = ref('')
const loading = ref(false)
const syncError = ref(null)
const sidebarOpen = ref(false)

const icon = (path) => ({
    render() {
        return h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': 2,
                d: path,
            }),
        ])
    },
})

const navigation = [
    {
        to: '/',
        exact: true,
        labelKey: 'layout.menu.dashboard',
        icon: icon('M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'),
    },
    {
        to: '/activity',
        labelKey: 'layout.menu.activity',
        icon: icon('M3 3v18h18M7 15l4-4 3 3 5-6'),
    },
    {
        to: '/library',
        labelKey: 'layout.menu.gameLibrary',
        icon: icon('M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'),
    },
]

const fetchLastSync = async () => {
    loading.value = true
    syncError.value = null

    try {
        const data = await getLastSync()
        lastSync.value = data.last_sync
    } catch (error) {
        syncError.value = error?.response?.data?.message || error.message || t('common.error')
    } finally {
        loading.value = false
    }
}

const handleSync = async () => {
    loading.value = true
    syncError.value = null

    try {
        await triggerSync()
        await fetchLastSync()
    } catch (error) {
        syncError.value = error?.response?.data?.message || error.message || t('common.error')
    } finally {
        loading.value = false
    }
}

const currentPageTitle = computed(() => {
    const key = route.meta.titleKey || 'layout.pageTitle.default'
    return t(key)
})

onMounted(fetchLastSync)
</script>
