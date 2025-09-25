<script setup>
defineProps({
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
});

const sizeClasses = {
    small: "px-4 py-2 text-sm md:px-6 md:py-3 md:text-base",
    medium: "px-6 py-3 text-base md:px-8 md:py-4 md:text-lg",
    large: "px-8 py-4 text-lg md:px-12 md:py-6 md:text-xl",
};

const presetClasses = {
    primary: "bg-primary text-white hover:bg-pressed hover:text-white",
    secondary: "bg-pressed text-white hover:bg-hover hover:text-white",
    accent: "bg-accent text-black hover:bg-primary hover:text-white",
    neutral:
        "bg-white text-pressed hover:bg-primary hover:text-white border-4 border-pressed",
    success: "bg-accent text-black hover:bg-primary hover:text-white",
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
            $attrs.disabled
                ? 'opacity-50 cursor-not-allowed'
                : 'cursor-pointer',
        ]"
        :disabled="$attrs.disabled"
    >
        {{ text }}
    </button>
</template>
