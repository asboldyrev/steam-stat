<template>
    <div class="space-y-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <TotalPlaytime :loading="loading.stats" :stats="stats" />
            <GamesCount :loading="loading.stats" :stats="stats" />
            <SteamDeckHours :loading="loading.stats" :stats="stats" />
        </div>

        <!-- Charts & Platform Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <PlatformDistribution :loading="loading.platform" :platforms="platformDistribution.platforms" />
            <RecentActivity :loading="loading.activity" :activities="recentActivity.activities" />
        </div>

        <TopGames :loading="loading.topGames" :games="topGames.games" />
    </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useApi } from '@/composables/useApi'
    import TotalPlaytime from '@/components/Dashboard/TotalPlaytime.vue'
    import GamesCount from '@/components/Dashboard/GamesCount.vue'
    import SteamDeckHours from '@/components/Dashboard/SteamDeckHours.vue'
    import PlatformDistribution from '@/components/Dashboard/PlatformDistribution.vue'
    import RecentActivity from '@/components/Dashboard/RecentActivity.vue'
    import TopGames from '@/components/Dashboard/TopGames.vue'

    const { t } = useI18n()
    const { getDashboardStats, getPlatformDistribution, getRecentActivity, getTopGames } = useApi()

    const stats = ref({
        total_playtime_hours: 0,
        games_count: 0,
        steam_deck_hours: 0,
        steam_deck_percentage: 0
    })
    const platformDistribution = ref({ platforms: [] })
    const recentActivity = ref({ activities: [] })
    const topGames = ref({ games: [] })
    const loading = ref({
        stats: false,
        platform: false,
        activity: false,
        topGames: false
    })
    const error = ref(null)

    const fetchDashboardData = async () => {
        try {
            loading.value.stats = true
            loading.value.platform = true
            loading.value.activity = true
            loading.value.topGames = true

            const [statsData, platformData, activityData, topGamesData] = await Promise.all([
                getDashboardStats(),
                getPlatformDistribution(),
                getRecentActivity(),
                getTopGames()
            ])

            stats.value = statsData
            platformDistribution.value = platformData
            // Добавляем поле iconError для отслеживания ошибок загрузки изображений
            const activities = activityData.activities || []
            activities.forEach(activity => {
                activity.iconError = false
            })
            recentActivity.value = { activities }

            // Добавляем поле iconError для top games
            const topGamesList = topGamesData.games || []
            topGamesList.forEach(game => {
                game.iconError = false
            })
            topGames.value = { games: topGamesList }
        } catch (err) {
            console.error('Failed to fetch dashboard data:', err)
            error.value = err.message || 'Unknown error'
        } finally {
            loading.value.stats = false
            loading.value.platform = false
            loading.value.activity = false
            loading.value.topGames = false
        }
    }

    onMounted(() => {
        fetchDashboardData()
    })
</script>
