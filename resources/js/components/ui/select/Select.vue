<script setup>
import {
    SelectContent,
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
    SelectPortal,
    SelectRoot,
    SelectTrigger,
    SelectValue,
    SelectViewport,
} from 'reka-ui'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <SelectRoot :model-value="props.modelValue" @update:model-value="emit('update:modelValue', $event)">
        <SelectTrigger class="inline-flex h-10 w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm outline-none transition focus:ring-2 focus:ring-blue-500/40 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <SelectValue />
            <span class="ml-2 text-gray-400">⌄</span>
        </SelectTrigger>
        <SelectPortal>
            <SelectContent class="z-50 overflow-hidden rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-700 dark:bg-gray-900">
                <SelectViewport>
                    <SelectItem
                        v-for="option in options"
                        :key="option.value"
                        :value="option.value"
                        class="relative flex cursor-default select-none items-center rounded-lg py-2 pl-8 pr-3 text-sm text-gray-700 outline-none data-[highlighted]:bg-gray-100 data-[state=checked]:font-semibold dark:text-gray-200 dark:data-[highlighted]:bg-gray-800"
                    >
                        <SelectItemIndicator class="absolute left-2">✓</SelectItemIndicator>
                        <SelectItemText>{{ option.label }}</SelectItemText>
                    </SelectItem>
                </SelectViewport>
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>
