<template>
    <VChart
        :key="themeKey"
        :option="option"
        :theme="isDark ? 'dark' : undefined"
        :autoresize="autoresize"
        :class="chartClass"
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
const chartClass = computed(() => 'w-full')
</script>

<style scoped>
.echarts {
    min-width: 0;
}
</style>

<style scoped>
:deep(.echarts) {
    height: v-bind("typeof props.height === 'number' ? `${props.height}px` : props.height");
}
</style>
