<script setup>
import { computed, useAttrs } from "vue";

const props = defineProps({
    value: String,
    for: {
        type: String,
        default: null,
    },
    color: {
        type: String,
        default: "white", // "white" or "black"
        validator: (value) => ["white", "black"].includes(value),
    },
    size: {
        type: String,
        default: "base", // "sm", "base", "lg"
        validator: (value) => ["sm", "base", "lg"].includes(value),
    },
});

const attrs = useAttrs();
const labelFor = computed(() => props.for || attrs.for || null);

const colorClass = computed(() => {
    return props.color === "white" ? "text-white" : "text-black";
});

const sizeClass = computed(() => {
    const sizes = {
        sm: "text-sm",
        base: "text-base",
        lg: "text-lg",
    };
    return sizes[props.size] || sizes.base;
});
</script>

<template>
    <label 
        :for="labelFor"
        :class="[
            'block font-medium',
            sizeClass,
            colorClass,
            attrs.class
        ]"
    >
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
