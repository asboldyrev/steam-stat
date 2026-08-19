<template>
    <section class="space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-gray-200/80 bg-white/80 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/70 sm:flex-row sm:items-end sm:justify-between">
            <div class="grid grid-cols-2 gap-3 sm:flex">
                <label class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                    <span>{{ t('activity.filters.from') }}</span>
                    <input v-model="filters.from" type="date" class="block w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </label>
                <label class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                    <span>{{ t('activity.filters.to') }}</span>
                    <input v-model="filters.to" type="date" class="block w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </label>
            </div>

            <button type="button" class="rounded-xl bg-steam-gradient px-4 py-2.5 text-sm font-medium text-white hover:opacity-90" @click="load">
                {{ t('activity.filters.apply') }}
            </button>
        </div>

        <PageState v-if="loading || error" :loading="loading" :error="error" @retry="load" />

        <template v-else-if="overview">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article v-for="card in summaryCards" :key="card.label" class="rounded-2xl border border-gray-200/80 bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ card.value }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ card.hint }}</p>
                </article>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                <article class="rounded-2xl border border-gray-200/80 bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('activity.daily.title') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('activity.daily.subtitle') }}</p>
                        </div>
                    </div>

                    <div v-if="maxDailyMinutes > 0" class="flex h-56 items-end gap-1 overflow-x-auto pb-6">
                        <div v-for="day in overview.daily" :key="day.date" class="group relative flex h-full min-w-3 flex-1 items-end">
                            <div
                                class="w-full rounded-t-md bg-steam-blue/75 transition-opacity group-hover:opacity-80"
                                :style="{ height: `${Math.max(3, (day.minutes / maxDailyMinutes) * 100)}%` }"
                            ></div>
                            <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-gray-950 px-2 py-1 text-xs text-white group-hover:block">
                                {{ day.date }} · {{ formatMinutes(day.minutes) }}
                            </div>
                        </div>
                    </div>
                    <PageState v-else :message="t('activity.noActivity')" />
                </article>

                <article class="rounded-2xl border border-gray-200/80 bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('activity.records.title') }}</h3>
                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('activity.records.bestDay') }}</span>
                            <span class="text-right text-sm font-medium text-gray-900 dark:text-white">{{ bestDay }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('activity.records.bestGame') }}</span>
                            <span class="text-right text-sm font-medium text-gray-900 dark:text-white">{{ bestGame }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('activity.records.longestStreak') }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ insights?.records?.longest_streak_days ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('activity.records.currentStreak') }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ insights?.records?.current_streak_days ?? 0 }}</span>
                        </div>
                    </div>
                </article>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-2xl border border-gray-200/80 bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('activity.games.title') }}</h3>
                    <div v-if="overview.games.length" class="mt-4 space-y-3">
                        <div v-for="game in overview.games.slice(0, 8)" :key="game.game_id" class="flex items-center gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="truncate font-medium text-gray-900 dark:text-white">{{ game.name }}</span>
                                    <span class="shrink-0 text-gray-500 dark:text-gray-400">{{ formatMinutes(game.minutes) }}</span>
                                </div>
                                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div class="h-full rounded-full bg-steam-blue" :style="{ width: `${(game.minutes / maxGameMinutes) * 100}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <PageState v-else :message="t('activity.noActivity')" />
                </article>

                <article class="rounded-2xl border border-gray-200/80 bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('activity.platforms.title') }}</h3>
                    <div v-if="overview.platforms.length" class="mt-4 space-y-4">
                        <div v-for="platform in overview.platforms" :key="platform.name">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">{{ platform.name }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ platform.percentage }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full bg-steam-blue" :style="{ width: `${platform.percentage}%` }"></div>
                            </div>
                        </div>
                    </div>
                    <PageState v-else :message="t('activity.noActivity')" />
                </article>
            </div>

            <article v-if="insights" class="rounded-2xl border border-gray-200/80 bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
                <div class="mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('activity.heatmap.title') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('activity.heatmap.subtitle') }}</p>
                </div>
                <div class="grid grid-cols-14 gap-1 sm:grid-cols-21 md:grid-cols-30 lg:grid-cols-45 xl:grid-cols-60">
                    <div
                        v-for="day in insights.heatmap"
                        :key="day.date"
                        class="aspect-square rounded-[3px] bg-steam-blue"
                        :class="heatmapOpacity(day.minutes)"
                        :title="`${day.date}: ${formatMinutes(day.minutes)}`"
                    ></div>
                </div>
            </article>
        </template>

        <PageState v-else :message="t('activity.noActivity')" @retry="load" />
    </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import PageState from '@/components/PageState.vue'
import { useApi } from '@/composables/useApi.js'

const { t } = useI18n()
const { getActivity, getActivityInsights } = useApi()

const today = new Date()
const thirtyDaysAgo = new Date(today)
thirtyDaysAgo.setDate(today.getDate() - 29)

const toDateInput = (date) => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

const filters = reactive({
    from: toDateInput(thirtyDaysAgo),
    to: toDateInput(today),
})

const overview = ref(null)
const insights = ref(null)
const loading = ref(false)
const error = ref('')

const formatMinutes = (minutes) => {
    if (!minutes) return `0${t('common.hours.short')}`
    const hours = minutes / 60
    return `${hours >= 10 ? Math.round(hours) : hours.toFixed(1)}${t('common.hours.short')}`
}

const load = async () => {
    loading.value = true
    error.value = ''

    try {
        const params = { from: filters.from, to: filters.to }
        const [overviewData, insightsData] = await Promise.all([
            getActivity(params),
            getActivityInsights(params),
        ])
        overview.value = overviewData
        insights.value = insightsData
    } catch (requestError) {
        error.value = requestError?.response?.data?.message || requestError.message || t('common.error')
    } finally {
        loading.value = false
    }
}

const summaryCards = computed(() => {
    if (!overview.value) return []

    return [
        {
            label: t('activity.summary.playtime'),
            value: formatMinutes(overview.value.summary.total_minutes),
            hint: t('activity.summary.previous', { value: formatMinutes(overview.value.previous.total_minutes) }),
        },
        {
            label: t('activity.summary.activeDays'),
            value: overview.value.summary.active_days,
            hint: t('activity.summary.periodDays', { value: overview.value.period.days }),
        },
        {
            label: t('activity.summary.games'),
            value: overview.value.summary.games_count,
            hint: t('activity.summary.previousGames', { value: overview.value.previous.games_count }),
        },
        {
            label: t('activity.summary.average'),
            value: formatMinutes(overview.value.summary.average_minutes_per_active_day),
            hint: t('activity.summary.perActiveDay'),
        },
    ]
})

const maxDailyMinutes = computed(() => Math.max(0, ...(overview.value?.daily || []).map((day) => day.minutes)))
const maxGameMinutes = computed(() => Math.max(1, ...(overview.value?.games || []).map((game) => game.minutes)))
const maxHeatmapMinutes = computed(() => Math.max(1, ...(insights.value?.heatmap || []).map((day) => day.minutes)))

const bestDay = computed(() => {
    const value = insights.value?.records?.best_day
    return value ? `${value.date} · ${formatMinutes(value.minutes)}` : '—'
})

const bestGame = computed(() => {
    const value = insights.value?.records?.best_game
    return value ? `${value.name} · ${formatMinutes(value.minutes)}` : '—'
})

const heatmapOpacity = (minutes) => {
    if (minutes <= 0) return 'opacity-10'
    const ratio = minutes / maxHeatmapMinutes.value
    if (ratio < 0.25) return 'opacity-30'
    if (ratio < 0.5) return 'opacity-50'
    if (ratio < 0.75) return 'opacity-70'
    return 'opacity-100'
}

onMounted(load)
</script>
