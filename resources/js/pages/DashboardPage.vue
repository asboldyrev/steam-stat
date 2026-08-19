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
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-950 dark:text-white">{{ t('dashboard.activity7d.title') }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.activity7d.subtitle') }}</p>
                        </div>
                        <RouterLink to="/activity" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            {{ t('dashboard.activity7d.openActivity') }}
                        </RouterLink>
                    </div>

                    <BaseChart v-if="hasActivity" :option="activityChartOption" :height="280" />
                    <div v-else class="flex h-56 items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                        {{ t('common.noData') }}
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-lg font-bold text-gray-950 dark:text-white">{{ t('dashboard.platformDistribution.title') }}</h3>
                    <BaseChart v-if="platforms.length" :option="platformChartOption" :height="280" />
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
import BaseChart from '@/components/charts/BaseChart.vue'
import PageState from '@/components/PageState.vue'
import { useApi } from '@/composables/useApi.js'
import { useDateFormat } from '@/composables/useDateFormat.js'
import { useTheme } from '@/composables/useTheme.js'

const { t } = useI18n()
const { isDark } = useTheme()
const { formatPeriod, formatShortDate } = useDateFormat()
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
    return formatPeriod(activity.value.period.from, activity.value.period.to)
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

const axisColor = computed(() => isDark.value ? '#9ca3af' : '#6b7280')
const splitColor = computed(() => isDark.value ? '#374151' : '#e5e7eb')
const hasActivity = computed(() => (activity.value?.daily || []).some((day) => Number(day.minutes) > 0))

const activityChartOption = computed(() => ({
    backgroundColor: 'transparent',
    animationDuration: 350,
    grid: { left: 12, right: 12, top: 20, bottom: 20, containLabel: true },
    tooltip: {
        trigger: 'axis',
        formatter: (items) => {
            const item = items?.[0]
            return item ? `${item.axisValue}<br/>${formatMinutes(item.value)}` : ''
        },
    },
    xAxis: {
        type: 'category',
        data: (activity.value?.daily || []).map((day) => formatShortDate(day.date)),
        axisLine: { lineStyle: { color: splitColor.value } },
        axisTick: { show: false },
        axisLabel: { color: axisColor.value },
    },
    yAxis: {
        type: 'value',
        minInterval: 1,
        axisLabel: { color: axisColor.value, formatter: (value) => formatMinutes(value) },
        splitLine: { lineStyle: { color: splitColor.value } },
    },
    series: [{
        type: 'bar',
        data: (activity.value?.daily || []).map((day) => day.minutes),
        barMaxWidth: 42,
        itemStyle: { color: '#3b82f6', borderRadius: [8, 8, 2, 2] },
        emphasis: { itemStyle: { color: '#06b6d4' } },
    }],
}))

const platformChartOption = computed(() => ({
    backgroundColor: 'transparent',
    tooltip: {
        trigger: 'item',
        formatter: ({ name, value, percent }) => `${name}<br/>${value}${t('common.hours.short')} · ${percent}%`,
    },
    legend: {
        bottom: 0,
        textStyle: { color: axisColor.value },
    },
    series: [{
        type: 'pie',
        radius: ['48%', '72%'],
        center: ['50%', '43%'],
        padAngle: 2,
        itemStyle: { borderRadius: 6 },
        label: { show: false },
        data: platforms.value.map((platform) => ({
            name: platform.name,
            value: platform.hours,
        })),
    }],
}))

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
