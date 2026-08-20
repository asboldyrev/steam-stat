<template>
    <section class="space-y-6">
        <PageState v-if="loading || error" :loading="loading" :error="error" @retry="load" />

        <template v-else-if="game">
            <article class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="relative min-h-56 overflow-hidden bg-gray-100 dark:bg-gray-800 sm:min-h-72">
                    <img
                        v-if="game.cover_url && !coverError"
                        :src="game.cover_url"
                        :alt="game.name"
                        class="absolute inset-0 h-full w-full object-cover"
                        @error="coverError = true"
                    />
                    <div v-else class="absolute inset-0 bg-gradient-to-br from-blue-700 via-slate-800 to-cyan-700" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/10" />

                    <div class="relative flex min-h-56 flex-col justify-end p-5 text-white sm:min-h-72 sm:p-7">
                        <div class="flex items-end gap-4">
                            <img
                                v-if="game.icon_url && !iconError"
                                :src="game.icon_url"
                                :alt="game.name"
                                class="h-16 w-16 rounded-2xl object-contain shadow-xl ring-1 ring-white/20 sm:h-20 sm:w-20"
                                @error="iconError = true"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Steam App {{ game.app_id }}</p>
                                <h1 class="mt-1 text-2xl font-black tracking-tight sm:text-4xl">{{ game.name }}</h1>
                                <p class="mt-2 text-sm text-white/70">{{ t('gameDetail.lastPlayed') }}: {{ lastPlayedLabel }}</p>
                            </div>
                            <a
                                :href="game.store_url"
                                target="_blank"
                                rel="noreferrer"
                                class="hidden rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold backdrop-blur transition hover:bg-white/20 sm:inline-flex"
                            >
                                {{ t('gameDetail.openSteam') }}
                            </a>
                        </div>
                    </div>
                </div>
            </article>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article v-for="card in summaryCards" :key="card.label" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ card.value }}</p>
                    <p v-if="card.hint" class="mt-1 text-xs text-gray-400">{{ card.hint }}</p>
                </article>
            </div>

            <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="space-y-1.5">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('gameDetail.period') }}</span>
                        <DateRangePicker v-model="dateRange" />
                    </div>
                    <Button type="button" @click="load">{{ t('gameDetail.apply') }}</Button>
                </div>
            </article>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4">
                        <h2 class="font-semibold text-gray-950 dark:text-white">{{ t('gameDetail.playtimeHistory') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ periodLabel }}</p>
                    </div>
                    <BaseChart v-if="hasActivity" :option="activityChartOption" :height="300" />
                    <PageState v-else :message="t('gameDetail.noActivity')" />
                </article>

                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="font-semibold text-gray-950 dark:text-white">{{ t('gameDetail.platformUsage') }}</h2>
                    <BaseChart v-if="game.platforms.length" :option="platformChartOption" :height="300" />
                    <PageState v-else :message="t('gameDetail.noActivity')" />
                </article>
            </div>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <h2 class="font-semibold text-gray-950 dark:text-white">{{ t('gameDetail.lifetimePlatforms') }}</h2>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div v-for="platform in lifetimePlatforms" :key="platform.name" class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/70">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ platform.name }}</p>
                                <p class="mt-1 text-lg font-bold text-gray-950 dark:text-white">{{ formatMinutes(platform.minutes) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="game.lifetime.disconnected_minutes > 0" class="w-full rounded-xl border border-dashed border-gray-300 p-4 dark:border-gray-700 lg:max-w-xs">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ t('gameDetail.diagnostics') }}</p>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            {{ t('gameDetail.disconnected') }}: {{ formatMinutes(game.lifetime.disconnected_minutes) }}
                        </p>
                    </div>
                </div>
            </article>
        </template>
    </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import BaseChart from '@/components/charts/BaseChart.vue'
import PageState from '@/components/PageState.vue'
import Button from '@/components/ui/button/Button.vue'
import DateRangePicker from '@/components/ui/date-range-picker/DateRangePicker.vue'
import { useApi } from '@/composables/useApi.js'
import { useDateFormat } from '@/composables/useDateFormat.js'
import { useTheme } from '@/composables/useTheme.js'

const { t } = useI18n()
const route = useRoute()
const { getGameDetail } = useApi()
const { formatLongDate, formatPeriod, formatShortDate } = useDateFormat()
const { isDark } = useTheme()

const today = new Date()
const thirtyDaysAgo = new Date(today)
thirtyDaysAgo.setDate(today.getDate() - 29)

const toDateInput = (date) => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

const dateRange = ref({ from: toDateInput(thirtyDaysAgo), to: toDateInput(today) })
const game = ref(null)
const loading = ref(true)
const error = ref('')
const coverError = ref(false)
const iconError = ref(false)

const formatMinutes = (minutes) => {
    const value = Number(minutes || 0)
    const hours = value / 60
    return `${hours >= 10 ? Math.round(hours) : hours.toFixed(1)}${t('common.hours.short')}`
}

const lastPlayedLabel = computed(() => game.value?.last_played_at ? formatLongDate(game.value.last_played_at) : '—')
const periodLabel = computed(() => game.value ? formatPeriod(game.value.period.from, game.value.period.to) : '')
const hasActivity = computed(() => (game.value?.daily || []).some((day) => Number(day.minutes) > 0))

const summaryCards = computed(() => game.value ? [
    { label: t('gameDetail.totalPlaytime'), value: formatMinutes(game.value.lifetime.total_minutes) },
    { label: t('gameDetail.periodPlaytime'), value: formatMinutes(game.value.summary.total_minutes), hint: periodLabel.value },
    { label: t('gameDetail.activeDays'), value: game.value.summary.active_days, hint: `${game.value.period.days} ${t('common.days', game.value.period.days)}` },
    { label: t('gameDetail.average'), value: formatMinutes(game.value.summary.average_minutes_per_active_day) },
] : [])

const lifetimePlatforms = computed(() => {
    if (!game.value) return []
    return [
        { name: 'Windows', minutes: game.value.lifetime.windows_minutes },
        { name: 'Steam Deck', minutes: game.value.lifetime.deck_minutes },
        { name: 'Linux', minutes: game.value.lifetime.linux_minutes },
        { name: 'macOS', minutes: game.value.lifetime.mac_minutes },
        { name: 'Unclassified', minutes: game.value.lifetime.unclassified_minutes },
    ].filter((platform) => platform.minutes > 0)
})

const axisColor = computed(() => isDark.value ? '#9ca3af' : '#6b7280')
const splitColor = computed(() => isDark.value ? '#374151' : '#e5e7eb')

const activityChartOption = computed(() => ({
    backgroundColor: 'transparent',
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
        data: (game.value?.daily || []).map((day) => formatShortDate(day.date)),
        axisLine: { lineStyle: { color: splitColor.value } },
        axisTick: { show: false },
        axisLabel: { color: axisColor.value, hideOverlap: true },
    },
    yAxis: {
        type: 'value',
        axisLabel: { color: axisColor.value, formatter: (value) => formatMinutes(value) },
        splitLine: { lineStyle: { color: splitColor.value } },
    },
    series: [{
        type: 'bar',
        data: (game.value?.daily || []).map((day) => day.minutes),
        barMaxWidth: 34,
        itemStyle: { color: '#3b82f6', borderRadius: [7, 7, 2, 2] },
    }],
}))

const platformChartOption = computed(() => ({
    backgroundColor: 'transparent',
    tooltip: { trigger: 'item', formatter: ({ name, value, percent }) => `${name}<br/>${formatMinutes(value)} · ${percent}%` },
    legend: { bottom: 0, textStyle: { color: axisColor.value } },
    series: [{
        type: 'pie',
        radius: ['48%', '72%'],
        center: ['50%', '43%'],
        padAngle: 2,
        itemStyle: { borderRadius: 6 },
        label: { show: false },
        data: (game.value?.platforms || []).map((platform) => ({ name: platform.name, value: platform.minutes })),
    }],
}))

const load = async () => {
    const gameId = route.params.id
    if (!gameId) return

    loading.value = true
    error.value = ''

    try {
        game.value = await getGameDetail(gameId, { from: dateRange.value.from, to: dateRange.value.to })
        coverError.value = false
        iconError.value = false
    } catch (requestError) {
        error.value = requestError?.response?.data?.message || requestError.message || t('common.error')
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
