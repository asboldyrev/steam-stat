<template>
    <VChart
        :key="themeKey"
        :option="option"
        :theme="isDark ? 'dark' : undefined"
        :autoresize="autoresize"
        class="w-full min-w-0"
        :style="{ height: normalizedHeight }"
    />
</template>

<script setup>
import { computed } from 'vue'
import VChart from 'vue-echarts'
import '@/charts/echarts.js'
import { useTheme } from '@/composables/useTheme.js'

const props = defineProps({
    option: {
        type: Object,
        required: true,
    },
    height: {
        type: [Number, String],
        default: 280,
    },
    autoresize: {
        type: Boolean,
        default: true,
    },
})

const { isDark } = useTheme()

const themeKey = computed(() => isDark.value ? 'dark' : 'light')
const normalizedHeight = computed(() => typeof props.height === 'number' ? `${props.height}px` : props.height)
</script>
