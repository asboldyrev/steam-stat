<template>
    <div class="rounded-2xl border border-gray-200/80 bg-white/80 p-8 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
        <div v-if="loading" class="space-y-3">
            <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-gray-200 border-t-steam-blue dark:border-gray-700 dark:border-t-steam-blue"></div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('common.loading') }}</p>
        </div>

        <div v-else-if="error" class="space-y-4">
            <div class="text-base font-semibold text-gray-900 dark:text-white">{{ t('common.error') }}</div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ error }}</p>
            <button
                type="button"
                class="rounded-xl bg-steam-gradient px-4 py-2 text-sm font-medium text-white transition-opacity hover:opacity-90"
                @click="$emit('retry')"
            >
                {{ t('common.retry') }}
            </button>
        </div>

        <div v-else class="space-y-2">
            <div class="text-base font-semibold text-gray-900 dark:text-white">{{ t('common.noData') }}</div>
            <p v-if="message" class="text-sm text-gray-500 dark:text-gray-400">{{ message }}</p>
        </div>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
    loading: { type: Boolean, default: false },
    error: { type: String, default: '' },
    message: { type: String, default: '' },
})

defineEmits(['retry'])

const { t } = useI18n()
</script>
