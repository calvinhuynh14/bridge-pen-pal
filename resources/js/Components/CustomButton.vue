<script setup>
const props = defineProps({
    text: {
        type: String,
        required: true,
    },
    preset: {
        type: String,
        default: "primary",
        validator: (value) =>
            [
                "primary",
                "secondary",
                "accent",
                "neutral",
                "success",
                "warning",
                "error",
            ].includes(value),
    },
    size: {
        type: String,
        default: "medium",
        validator: (value) => ["small", "medium", "large"].includes(value),
    },
    ariaLabel: {
        type: String,
        default: null,
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
});

const sizeClasses = {
    small: "px-4 py-2 text-lg md:px-6 md:py-3 md:text-lg",
    medium: "px-6 py-3 text-lg md:px-8 md:py-4 md:text-xl",
    large: "px-8 py-4 text-xl md:px-12 md:py-6 md:text-2xl",
};

const presetClasses = {
    primary: "bg-primary text-white hover:bg-pressed hover:text-white",
    secondary: "bg-pressed text-white hover:bg-hover hover:text-white",
    accent: "bg-accent text-black hover:bg-primary hover:text-white",
    neutral:
        "bg-white text-primary hover:bg-primary hover:text-white border-4 border-primary",
    success: "bg-green-600 text-white hover:bg-green-700 hover:text-white",
    warning: "bg-accent text-black hover:bg-primary hover:text-white",
    error: "bg-hover text-white hover:bg-pressed hover:text-white",
};
</script>

<template>
    <button
        :class="[
            'rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
            presetClasses[preset],
            sizeClasses[size],
            $attrs.disabled || isLoading
                ? 'opacity-50 cursor-not-allowed'
                : 'cursor-pointer',
        ]"
        :aria-label="ariaLabel || text"
        :aria-busy="isLoading"
        :aria-disabled="$attrs.disabled || isLoading"
        :disabled="$attrs.disabled || isLoading"
        v-bind="$attrs"
    >
        <span v-if="isLoading" aria-hidden="true">Loading...</span>
        <span :aria-hidden="isLoading">{{ text }}</span>
    </button>
</template>
