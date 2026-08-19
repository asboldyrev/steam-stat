<template>
    <div class="space-y-8">
        <PageState
            v-if="loading || error"
            :loading="loading"
            :error="error"
            @retry="load"
        />

        <template v-else>
            <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="grid gap-6 p-6 lg:grid-cols-[1.35fr_0.65fr] lg:p-8">
                    <div>
                        <div class="mb-5 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <span class="rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                {{ t('dashboard.overview.trackedActivity') }}
                            </span>
                            <span>{{ activityPeriodLabel }}</span>
                        </div>

                        <p class="text-sm font-medium uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                            {{ t('dashboard.overview.playtime7d') }}
                        </p>
                        <div class="mt-3 flex flex-wrap items-end gap-x-5 gap-y-2">
                            <span class="text-5xl font-black tracking-tight text-gray-950 dark:text-white sm:text-6xl">
                                {{ formatMinutes(activity?.summary?.total_minutes || 0) }}
                            </span>
                            <span :class="comparisonClass" class="mb-1 rounded-full px-3 py-1 text-sm font-semibold">
                                {{ comparisonText }}
                            </span>
                        </div>
                        <p class="mt-4 max-w-2xl text-gray-600 dark:text-gray-300">
                            {{ t('dashboard.overview.description', {
                                games: activity?.summary?.games_count || 0,
                                days: activity?.summary?.active_days || 0,
                            }) }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-2">
                        <div v-for="card in lifetimeCards" :key="card.label" class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/70">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ card.value }}</p>
                            <p v-if="card.hint" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ card.hint }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-950 dark:text-white">{{ t('dashboard.activity7d.title') }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.activity7d.subtitle') }}</p>
                        </div>
                        <RouterLink to="/activity" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            {{ t('dashboard.activity7d.openActivity') }}
                        </RouterLink>
                    </div>

                    <div v-if="activity?.daily?.length" class="flex h-56 items-end gap-2 sm:gap-3">
                        <div v-for="day in activity.daily" :key="day.date" class="flex min-w-0 flex-1 flex-col items-center gap-2">
                            <div class="flex h-44 w-full items-end rounded-xl bg-gray-50 px-1 dark:bg-gray-800/60">
                                <div
                                    class="w-full rounded-lg bg-gradient-to-t from-blue-700 to-cyan-400 transition-all"
                                    :style="{ height: `${dailyHeight(day.minutes)}%` }"
                                    :title="`${day.date}: ${formatMinutes(day.minutes)}`"
                                />
                            </div>
                            <span class="max-w-full truncate text-xs text-gray-500 dark:text-gray-400">{{ shortDate(day.date) }}</span>
                        </div>
                    </div>
                    <div v-else class="flex h-56 items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                        {{ t('common.noData') }}
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-lg font-bold text-gray-950 dark:text-white">{{ t('dashboard.platformDistribution.title') }}</h3>
                    <div v-if="platforms.length" class="mt-6 space-y-5">
                        <div v-for="platform in platforms" :key="platform.name">
                            <div class="mb-2 flex items-center justify-between gap-4 text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-200">{{ platform.name }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ platform.percentage }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full bg-blue-500" :style="{ width: `${platform.percentage}%` }" />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ platform.hours }}{{ t('common.hours.short') }}</p>
                        </div>
                    </div>
                    <p v-else class="mt-6 text-sm text-gray-500 dark:text-gray-400">{{ t('common.noData') }}</p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">{{ t('dashboard.recentActivity.title') }}</h3>
                        <RouterLink to="/activity" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            {{ t('dashboard.recentActivity.viewAll') }}
                        </RouterLink>
                    </div>

                    <div v-if="recent.length" class="space-y-3">
                        <div v-for="item in recent" :key="`${item.game_id}-${item.last_played}`" class="flex items-center gap-4 rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/60">
                            <img v-if="item.icon_url" :src="item.icon_url" :alt="item.game_name" class="h-12 w-12 rounded-xl object-cover" />
                            <div v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-200 font-bold text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                {{ item.abbreviation }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-gray-950 dark:text-white">{{ item.game_name }}</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatMinutes(item.duration_minutes) }} · {{ item.platform }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">{{ t('common.noData') }}</p>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-5 text-lg font-bold text-gray-950 dark:text-white">{{ t('dashboard.topGames.title') }}</h3>
                    <div v-if="topGames.length" class="space-y-3">
                        <RouterLink
                            v-for="game in topGames"
                            :key="game.game_id"
                            :to="`/game/${game.game_id}`"
                            class="flex items-center gap-4 rounded-2xl p-3 transition hover:bg-gray-50 dark:hover:bg-gray-800/60"
                        >
                            <span class="w-7 shrink-0 text-center text-lg font-black text-gray-300 dark:text-gray-600">{{ game.rank }}</span>
                            <img v-if="game.icon_url" :src="game.icon_url" :alt="game.name" class="h-11 w-11 rounded-xl object-cover" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-gray-950 dark:text-white">{{ game.name }}</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ game.total_hours }}{{ t('common.hours.short') }}</p>
                            </div>
                        </RouterLink>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">{{ t('common.noData') }}</p>
                </div>
            </section>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import PageState from '@/components/PageState.vue'
import { useApi } from '@/composables/useApi.js'

const { t } = useI18n()
const {
    getDashboardStats,
    getPlatformDistribution,
    getRecentActivity,
    getTopGames,
    getActivity,
} = useApi()

const stats = ref(null)
const platforms = ref([])
const recent = ref([])
const topGames = ref([])
const activity = ref(null)
const loading = ref(true)
const error = ref('')

const dateInput = (date) => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

const formatMinutes = (minutes) => {
    const hours = Number(minutes || 0) / 60
    return `${hours >= 10 ? Math.round(hours) : hours.toFixed(1)}${t('common.hours.short')}`
}

const shortDate = (date) => date.slice(5)

const lifetimeCards = computed(() => [
    {
        label: t('dashboard.stats.totalPlaytime'),
        value: `${stats.value?.total_playtime_hours || 0}${t('common.hours.short')}`,
        hint: t('dashboard.stats.acrossAllPlatforms'),
    },
    {
        label: t('dashboard.stats.gamesPlayed'),
        value: stats.value?.games_count || 0,
    },
    {
        label: t('dashboard.stats.steamDeck'),
        value: `${stats.value?.steam_deck_hours || 0}${t('common.hours.short')}`,
        hint: t('dashboard.stats.ofTotalTime', { percent: stats.value?.steam_deck_percentage || 0 }),
    },
    {
        label: t('dashboard.overview.activeDays'),
        value: activity.value?.summary?.active_days || 0,
        hint: t('dashboard.overview.last7Days'),
    },
])

const activityPeriodLabel = computed(() => {
    if (!activity.value?.period) return ''
    return `${activity.value.period.from} — ${activity.value.period.to}`
})

const comparisonPercent = computed(() => {
    const current = Number(activity.value?.summary?.total_minutes || 0)
    const previous = Number(activity.value?.previous?.total_minutes || 0)

    if (previous === 0) return current > 0 ? null : 0
    return Math.round(((current - previous) / previous) * 100)
})

const comparisonText = computed(() => {
    if (comparisonPercent.value === null) return t('dashboard.overview.noPrevious')
    const sign = comparisonPercent.value > 0 ? '+' : ''
    return t('dashboard.overview.vsPrevious', { value: `${sign}${comparisonPercent.value}%` })
})

const comparisonClass = computed(() => {
    if (comparisonPercent.value === null || comparisonPercent.value === 0) {
        return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'
    }

    return comparisonPercent.value > 0
        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'
        : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300'
})

const maxDaily = computed(() => Math.max(1, ...(activity.value?.daily || []).map((day) => Number(day.minutes || 0))))
const dailyHeight = (minutes) => Math.max(minutes > 0 ? 5 : 0, Math.round((Number(minutes || 0) / maxDaily.value) * 100))

const load = async () => {
    loading.value = true
    error.value = ''

    const to = new Date()
    const from = new Date(to)
    from.setDate(to.getDate() - 6)

    try {
        const [statsData, platformData, recentData, topData, activityData] = await Promise.all([
            getDashboardStats(),
            getPlatformDistribution(),
            getRecentActivity(),
            getTopGames(),
            getActivity({ from: dateInput(from), to: dateInput(to) }),
        ])

        stats.value = statsData
        platforms.value = platformData
        recent.value = recentData
        topGames.value = topData
        activity.value = activityData
    } catch (requestError) {
        error.value = requestError?.response?.data?.message || requestError.message || t('common.error')
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
