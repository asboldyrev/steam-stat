<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-steam shadow-card transition-smooth hover:shadow-hover">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ label }}</p>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ value }}</h3>
        <p class="text-sm mt-1" :class="trendClass">
          {{ trend }}
        </p>
      </div>
      <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="iconBgClass">
        <slot name="icon">
          <svg class="w-6 h-6" :class="iconColorClass" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: String,
  value: String,
  trend: String,
  trendType: {
    type: String,
    default: 'neutral', // 'positive', 'negative', 'neutral'
  },
  iconBg: {
    type: String,
    default: 'bg-blue-100 dark:bg-blue-900/30',
  },
  iconColor: {
    type: String,
    default: 'text-blue-600 dark:text-blue-400',
  },
})

const trendClass = computed(() => {
  switch (props.trendType) {
    case 'positive': return 'text-green-600 dark:text-green-400'
    case 'negative': return 'text-red-600 dark:text-red-400'
    default: return 'text-gray-500 dark:text-gray-400'
  }
})

const iconBgClass = computed(() => props.iconBg)
const iconColorClass = computed(() => props.iconColor)
</script>