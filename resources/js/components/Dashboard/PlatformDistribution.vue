<template>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('dashboard.platformDistribution.title') }}</h3>
        </div>
        <div class="space-y-4">
            <div v-if="loading" class="text-center py-4 text-gray-500 dark:text-gray-400">
                {{ t('common.loading') }}
            </div>
            <div v-else-if="platforms.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
                {{ t('common.noData') }}
            </div>
            <div v-else v-for="platform in platforms" :key="platform.name" class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full" :class="platform.color"></div>
                    <span class="text-gray-700 dark:text-gray-300">{{ platform.name }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $formatNumber(platform.hours) }} {{ t('common.hours.full', platform.hours) }} ({{ platform.percentage }}%)</span>
                    <div class="w-48 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" :class="platform.color" :style="{ width: platform.percentage + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { useI18n } from 'vue-i18n'

    const props = defineProps({
        loading: Boolean,
        platforms: Object
    })

    const { t } = useI18n()
</script>
