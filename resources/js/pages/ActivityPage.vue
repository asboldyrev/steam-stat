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
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('activity.daily.title') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('activity.daily.subtitle') }}</p>
                    </div>
                    <BaseChart v-if="hasDailyActivity" :option="dailyChartOption" :height="280" />
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
                    <BaseChart v-if="overview.platforms.length" :option="platformChartOption" :height="280" />
                    <PageState v-else :message="t('activity.noActivity')" />
                </article>
            </div>

            <article v-if="insights" class="rounded-2xl border border-gray-200/80 bg-white/80 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
                <div class="mb-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('activity.heatmap.title') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('activity.heatmap.subtitle') }}</p>
                </div>
                <BaseChart v-if="insights.heatmap.length" :option="heatmapChartOption" :height="230" />
                <PageState v-else :message="t('activity.noActivity')" />
            </article>
        </template>

        <PageState v-else :message="t('activity.noActivity')" @retry="load" />
    </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseChart from '@/components/charts/BaseChart.vue'
import PageState from '@/components/PageState.vue'
import { useApi } from '@/composables/useApi.js'
import { useTheme } from '@/composables/useTheme.js'

const { t } = useI18n()
const { isDark } = useTheme()
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

const axisColor = computed(() => isDark.value ? '#9ca3af' : '#6b7280')
const splitColor = computed(() => isDark.value ? '#374151' : '#e5e7eb')
const maxGameMinutes = computed(() => Math.max(1, ...(overview.value?.games || []).map((game) => game.minutes)))
const hasDailyActivity = computed(() => (overview.value?.daily || []).some((day) => Number(day.minutes) > 0))

const dailyChartOption = computed(() => ({
    backgroundColor: 'transparent',
    animationDuration: 350,
    grid: { left: 12, right: 12, top: 20, bottom: 24, containLabel: true },
    tooltip: {
        trigger: 'axis',
        formatter: (items) => {
            const item = items?.[0]
            return item ? `${item.axisValue}<br/>${formatMinutes(item.value)}` : ''
        },
    },
    xAxis: {
        type: 'category',
        data: (overview.value?.daily || []).map((day) => day.date.slice(5)),
        axisLine: { lineStyle: { color: splitColor.value } },
        axisTick: { show: false },
        axisLabel: { color: axisColor.value, hideOverlap: true },
    },
    yAxis: {
        type: 'value',
        minInterval: 1,
        axisLabel: { color: axisColor.value, formatter: (value) => formatMinutes(value) },
        splitLine: { lineStyle: { color: splitColor.value } },
    },
    series: [{
        type: 'bar',
        data: (overview.value?.daily || []).map((day) => day.minutes),
        barMaxWidth: 34,
        itemStyle: { color: '#3b82f6', borderRadius: [7, 7, 2, 2] },
        emphasis: { itemStyle: { color: '#06b6d4' } },
    }],
}))

const platformChartOption = computed(() => ({
    backgroundColor: 'transparent',
    tooltip: {
        trigger: 'item',
        formatter: ({ name, value, percent }) => `${name}<br/>${formatMinutes(value)} · ${percent}%`,
    },
    legend: {
        bottom: 0,
        textStyle: { color: axisColor.value },
    },
    series: [{
        type: 'pie',
        radius: ['48%', '72%'],
        center: ['50%', '43%'],
        avoidLabelOverlap: true,
        padAngle: 2,
        itemStyle: { borderRadius: 6 },
        label: { show: false },
        data: (overview.value?.platforms || []).map((platform) => ({
            name: platform.name,
            value: platform.minutes,
        })),
    }],
}))

const heatmapChartOption = computed(() => {
    const data = (insights.value?.heatmap || []).map((day) => [day.date, day.minutes])
    const maxMinutes = Math.max(1, ...data.map((item) => Number(item[1]) || 0))
    const firstDate = data[0]?.[0] || filters.from
    const lastDate = data[data.length - 1]?.[0] || filters.to

    return {
        backgroundColor: 'transparent',
        tooltip: {
            formatter: ({ value }) => `${value?.[0] || ''}<br/>${formatMinutes(value?.[1] || 0)}`,
        },
        visualMap: {
            min: 0,
            max: maxMinutes,
            calculable: false,
            orient: 'horizontal',
            left: 'center',
            bottom: 0,
            textStyle: { color: axisColor.value },
            inRange: {
                color: isDark.value
                    ? ['#1f2937', '#1d4ed8', '#22d3ee']
                    : ['#eff6ff', '#60a5fa', '#0891b2'],
            },
        },
        calendar: {
            top: 18,
            left: 38,
            right: 24,
            bottom: 48,
            range: [firstDate, lastDate],
            cellSize: ['auto', 18],
            itemStyle: {
                color: isDark.value ? '#111827' : '#f3f4f6',
                borderWidth: 3,
                borderColor: isDark.value ? '#111827' : '#ffffff',
            },
            splitLine: { show: false },
            dayLabel: { color: axisColor.value, firstDay: 1, nameMap: 'en' },
            monthLabel: { color: axisColor.value },
            yearLabel: { show: false },
        },
        series: [{
            type: 'heatmap',
            coordinateSystem: 'calendar',
            data,
        }],
    }
})

const bestDay = computed(() => {
    const value = insights.value?.records?.best_day
    return value ? `${value.date} · ${formatMinutes(value.minutes)}` : '—'
})

const bestGame = computed(() => {
    const value = insights.value?.records?.best_game
    return value ? `${value.name} · ${formatMinutes(value.minutes)}` : '—'
})

onMounted(load)
</script>
