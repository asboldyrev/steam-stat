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
                        <Input
                            v-model="searchQuery"
                            type="search"
                            :placeholder="t('library.filterPlaceholder')"
                        />
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

        <PageState
            v-if="loading || error"
            :loading="loading"
            :error="error"
            @retry="fetchGames"
        />

        <PageState
            v-else-if="games.length === 0"
            :empty="true"
        />

        <section v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            <RouterLink
                v-for="game in games"
                :key="game.id"
                :to="`/game/${game.id}`"
                class="group overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="relative aspect-[16/8] overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <img
                        v-if="game.icon_url && !game.iconError"
                        :src="game.icon_url"
                        :alt="game.name"
                        class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                        @error="game.iconError = true"
                    />
                    <div v-else :class="game.gradient" class="flex h-full w-full items-center justify-center text-4xl font-black text-white">
                        {{ game.abbreviation }}
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/70 to-transparent" />
                    <div class="absolute bottom-3 left-4 right-4 flex items-end justify-between gap-3 text-white">
                        <p class="min-w-0 truncate text-base font-bold">{{ game.name }}</p>
                        <span class="shrink-0 rounded-full bg-black/35 px-2.5 py-1 text-xs font-semibold backdrop-blur">
                            {{ game.total_time }}{{ t('common.hours.short') }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="platform in activePlatforms(game)"
                                :key="platform"
                                class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            >
                                {{ platform }}
                            </span>
                        </div>
                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">{{ t('library.gameCard.viewDetails') }}</span>
                    </div>

                    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                        {{ t('library.gameCard.lastPlayed') }}: {{ formatLastPlayed(game.last_played) }}
                    </p>
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
import { useDateFormat } from '@/composables/useDateFormat.js'

const { t } = useI18n()
const { formatDate } = useDateFormat()
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
    const total = games.value.reduce((sum, game) => sum + Number(game.total_time || 0), 0)
    return t('library.totalHours', { hours: total })
})

const activePlatforms = (game) => {
    const result = []
    if (game.platforms?.windows) result.push('Windows')
    if (game.platforms?.deck) result.push('Steam Deck')
    if (game.platforms?.linux) result.push('Linux')
    if (game.platforms?.mac) result.push('macOS')
    return result
}

const formatLastPlayed = (timestamp) => {
    if (!timestamp) return '—'
    return formatDate(new Date(timestamp * 1000), {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}

const fetchGames = async () => {
    loading.value = true
    error.value = ''

    try {
        const params = {
            sort: sortBy.value,
            platform: platformFilter.value,
        }
        if (searchQuery.value.trim()) params.search = searchQuery.value.trim()

        const response = await getGames(params)
        games.value = (response.games || []).map((game) => ({ ...game, iconError: false }))
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