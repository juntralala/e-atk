<script setup>
import { randomMinMax } from '@/lib/utils';

defineProps({
    size: {
        type: [Number, String],
        default: () => randomMinMax(10, 40)
    },
    duration: {
        type: String,
        default: () => `${randomMinMax(12, 20)}s`,
    },
    delay: {
        type: String,
        default: () => `${randomMinMax(1, 12)}s`,
    },
    left: {
        type: String,
        default: () => `${randomMinMax(1, 100)}%`,
    },
    alpha: {
        type: Number,
        default: 0.2,
    }
});
</script>

<template>
    <div class="rounded-2xl" :style="{
        position: 'absolute',
        opacity: 0,
        left: left,
        animation: `bubble-up ${duration} infinite`,
        animationDelay: delay,
        width: `calc(${size} * 0.25rem)`,
        height: `calc(${size} * 0.25rem)`,
        backgroundColor: `rgba(255, 255, 255, ${alpha})`
    }">
    </div>
</template>