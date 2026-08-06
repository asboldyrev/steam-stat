<template>
    <div class="space-y-8">
        <!-- Library Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('library.title') }}</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-2">{{ games.length }} {{ t('common.gameCount', games.length) }} • {{ t('library.totalHours', { hours: 5 }) }}</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <input type="text" :placeholder="t('library.filterPlaceholder')" v-model.lazy="searchQuery" class="pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-steam rounded-xl w-64 focus:outline-none focus:ring-2 focus:ring-steam-blue placeholder-gray-400 dark:placeholder-gray-500" />
                    <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <select v-model="sortBy" class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-steam rounded-xl focus:outline-none focus:ring-2 focus:ring-steam-blue">
                    <option value="playtime">{{ t('library.sortBy.playtime') }}</option>
                    <option value="name">{{ t('library.sortBy.name') }}</option>
                    <option value="last_played">{{ t('library.sortBy.lastPlayed') }}</option>
                </select>
            </div>
        </div>

        <!-- Platform Filters -->
        <div class="flex flex-wrap gap-3">
            <button @click="platformFilter = 'all'" :class="platformFilter === 'all' ? 'bg-steam-blue text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'" class="px-4 py-2 border border-steam rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-smooth">
                {{ t('library.platformFilters.all') }}
            </button>
            <button @click="platformFilter = 'windows'" :class="platformFilter === 'windows' ? 'bg-blue-600 text-white' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'" class="px-4 py-2 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-200 dark:hover:bg-blue-800/50 transition-smooth">
                {{ t('library.platformFilters.windows') }}
            </button>
            <button @click="platformFilter = 'deck'" :class="platformFilter === 'deck' ? 'bg-green-600 text-white' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'" class="px-4 py-2 border border-green-200 dark:border-green-800 rounded-xl hover:bg-green-200 dark:hover:bg-green-800/50 transition-smooth">
                {{ t('library.platformFilters.steamDeck') }}
            </button>
            <button @click="platformFilter = 'linux'" :class="platformFilter === 'linux' ? 'bg-purple-600 text-white' : 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300'" class="px-4 py-2 border border-purple-200 dark:border-purple-800 rounded-xl hover:bg-purple-200 dark:hover:bg-purple-800/50 transition-smooth">
                {{ t('library.platformFilters.linux') }}
            </button>
        </div>

        <!-- Games Grid -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-steam-blue"></div>
            <p class="mt-4 text-gray-500 dark:text-gray-400">{{ t('common.loading') }}</p>
        </div>

        <div v-else-if="error" class="text-center py-12">
            <div class="text-red-500 dark:text-red-400 mb-4">
                <svg class="w-12 h-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-gray-700 dark:text-gray-300">{{ error }}</p>
            <button @click="fetchGames" class="mt-4 px-6 py-2 bg-steam-blue text-white rounded-xl hover:bg-steam-blue-dark transition-smooth">
                {{ t('common.retry') }}
            </button>
        </div>

        <div v-else-if="games.length === 0" class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">{{ t('common.noData') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <router-link v-for="game in games" :key="game.id" :to="`/game/${game.id}`" class="bg-white dark:bg-gray-800 rounded-2xl border border-steam shadow-card hover:shadow-hover transition-smooth overflow-hidden group cursor-pointer block">
                <GameCard :game="game" />
            </router-link>
        </div>
    </div>
</template>

<script setup>
    import { ref, onMounted, watch } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useApi } from '@/composables/useApi'
    import GameCard from '@/components/Library/GameCard.vue'

    const { t } = useI18n()
    const { getGames, } = useApi()

    const games = ref([])
    const loading = ref(false)
    const error = ref(null)

    // Параметры фильтрации
    const searchQuery = ref('')
    const sortBy = ref('playtime')
    const platformFilter = ref('all')

    const fetchGames = async () => {
        loading.value = true
        error.value = null
        try {
            const params = {}
            if (searchQuery.value) params.search = searchQuery.value
            if (sortBy.value) params.sort = sortBy.value
            if (platformFilter.value && platformFilter.value !== 'all') params.platform = platformFilter.value

            await getGames(params).then(response => {
                const gamesData = response.games || []
                // Добавляем поле iconError для отслеживания ошибок загрузки изображений
                gamesData.forEach(game => {
                    game.iconError = false
                })
                games.value = gamesData
            })
        } catch (err) {
            console.error('Failed to fetch games:', err)
            error.value = err.message || 'Unknown error'
        } finally {
            loading.value = false
        }
    }

    onMounted(() => {
        fetchGames()
    })

    // Реактивные обновления при изменении фильтров (можно добавить debounce)
    watch([searchQuery, sortBy, platformFilter], () => {
        fetchGames()
    })
</script>
