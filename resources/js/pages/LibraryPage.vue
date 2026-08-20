<template>
    <div class="space-y-8">
        <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                        {{ t('library.overview.eyebrow') }}
                    </p>
                    <div class="mt-2 flex flex-wrap items-baseline gap-x-4 gap-y-2">
                        <h3 class="text-3xl font-black tracking-tight text-gray-950 dark:text-white">{{ games.length }}</h3>
                        <span class="text-gray-500 dark:text-gray-400">{{ t('common.gameCount', games.length) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ totalHoursLabel }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[620px]">
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ t('library.controls.search') }}</span>
                        <Input v-model="searchQuery" type="search" :placeholder="t('library.filterPlaceholder')" />
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ t('library.controls.sort') }}</span>
                        <Select v-model="sortBy" :options="sortOptions" />
                    </label>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                <Button
                    v-for="filter in platformFilters"
                    :key="filter.value"
                    type="button"
                    :variant="platformFilter === filter.value ? 'default' : 'outline'"
                    class="h-9 rounded-full px-4"
                    @click="platformFilter = filter.value"
                >
                    {{ filter.label }}
                </Button>
            </div>
        </section>

        <PageState v-if="loading || error" :loading="loading" :error="error" @retry="fetchGames" />
        <PageState v-else-if="games.length === 0" :empty="true" />

        <section v-else class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            <RouterLink
                v-for="game in games"
                :key="game.id"
                :to="`/game/${game.id}`"
                class="group overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900 dark:hover:border-blue-900"
            >
                <div class="relative aspect-[920/430] overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <img
                        v-if="headerUrl(game) && !game.headerError"
                        :src="headerUrl(game)"
                        :alt="game.name"
                        class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.015]"
                        loading="lazy"
                        @error="game.headerError = true"
                    />

                    <div v-else :class="game.gradient" class="flex h-full w-full items-center justify-center">
                        <img
                            v-if="iconUrl(game) && !game.iconError"
                            :src="iconUrl(game)"
                            :alt="game.name"
                            class="h-16 w-16 rounded-2xl object-contain shadow-lg ring-1 ring-white/20"
                            @error="game.iconError = true"
                        />
                        <span v-else class="text-4xl font-black text-white/95">{{ game.abbreviation }}</span>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="truncate text-base font-bold text-gray-950 dark:text-white">{{ game.name }}</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ t('library.gameCard.lastPlayed') }}: {{ formatLastPlayed(game.last_played_at) }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                            {{ formatPlaytime(game.total_minutes) }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <div class="flex min-w-0 flex-wrap gap-1.5">
                            <span
                                v-for="platform in activePlatforms(game)"
                                :key="platform"
                                class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            >
                                {{ platform }}
                            </span>
                        </div>

                        <span class="shrink-0 text-xs font-semibold text-blue-600 transition group-hover:text-blue-700 dark:text-blue-400 dark:group-hover:text-blue-300">
                            {{ t('library.gameCard.viewDetails') }}
                        </span>
                    </div>
                </div>
            </RouterLink>
        </section>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import PageState from '@/components/PageState.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Select from '@/components/ui/select/Select.vue'
import { useApi } from '@/composables/useApi.js'
import { useArtwork } from '@/composables/useArtwork.js'
import { useDateFormat } from '@/composables/useDateFormat.js'

const { t } = useI18n()
const { formatDate } = useDateFormat()
const { assetUrl } = useArtwork()
const { getGames } = useApi()

const games = ref([])
const loading = ref(true)
const error = ref('')
const searchQuery = ref('')
const sortBy = ref('playtime')
const platformFilter = ref('all')
let searchTimer = null

const platformFilters = computed(() => [
    { value: 'all', label: t('library.platformFilters.all') },
    { value: 'windows', label: t('library.platformFilters.windows') },
    { value: 'deck', label: t('library.platformFilters.steamDeck') },
    { value: 'linux', label: t('library.platformFilters.linux') },
])

const sortOptions = computed(() => [
    { value: 'playtime', label: t('library.sortBy.playtime') },
    { value: 'name', label: t('library.sortBy.name') },
    { value: 'last_played', label: t('library.sortBy.lastPlayed') },
])

const totalHoursLabel = computed(() => {
    const totalMinutes = games.value.reduce((sum, game) => sum + Number(game.total_minutes || 0), 0)
    return t('library.totalHours', { hours: Math.round(totalMinutes / 60) })
})

const headerUrl = (game) => assetUrl(game.artwork, 'header')
const iconUrl = (game) => assetUrl(game.artwork, 'icon')

const formatPlaytime = (minutes) => {
    const hours = Number(minutes || 0) / 60
    const value = hours >= 10 ? Math.round(hours) : hours.toFixed(1)
    return `${value}${t('common.hours.short')}`
}

const activePlatforms = (game) => {
    const result = []
    if (game.platforms?.windows) result.push('Windows')
    if (game.platforms?.deck) result.push('Steam Deck')
    if (game.platforms?.linux) result.push('Linux')
    if (game.platforms?.mac) result.push('macOS')
    return result
}

const formatLastPlayed = (value) => value ? formatDate(value, 'D MMM YYYY') : '—'

const fetchGames = async () => {
    loading.value = true
    error.value = ''

    try {
        const params = { sort: sortBy.value, platform: platformFilter.value }
        if (searchQuery.value.trim()) params.search = searchQuery.value.trim()

        const response = await getGames(params)
        games.value = (response.games || []).map((game, index) => ({
            ...game,
            gradient: [
                'bg-gradient-to-br from-blue-600 to-cyan-500',
                'bg-gradient-to-br from-green-600 to-emerald-500',
                'bg-gradient-to-br from-purple-600 to-pink-500',
                'bg-gradient-to-br from-yellow-600 to-orange-500',
                'bg-gradient-to-br from-red-600 to-rose-500',
                'bg-gradient-to-br from-indigo-600 to-violet-500',
            ][index % 6],
            headerError: false,
            iconError: false,
        }))
    } catch (requestError) {
        error.value = requestError?.response?.data?.message || requestError.message || t('common.error')
    } finally {
        loading.value = false
    }
}

watch([sortBy, platformFilter], fetchGames)
watch(searchQuery, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(fetchGames, 300)
})

onMounted(fetchGames)
</script>
