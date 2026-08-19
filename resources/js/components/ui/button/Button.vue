<script setup>
import { Primitive } from 'reka-ui'

const props = defineProps({
    as: {
        type: [String, Object],
        default: 'button',
    },
    asChild: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'default',
    },
})
</script>

<template>
    <Primitive
        :as="props.as"
        :as-child="props.asChild"
        data-slot="button"
        :class="[
            'inline-flex h-10 items-center justify-center rounded-xl px-4 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 disabled:pointer-events-none disabled:opacity-50',
            props.variant === 'default'
                ? 'bg-steam-gradient text-white hover:opacity-90'
                : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700',
        ]"
    >
        <slot />
    </Primitive>
</template>
