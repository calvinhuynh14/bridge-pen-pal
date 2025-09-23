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
    small: "px-6 py-4 text-lg",
    medium: "px-12 py-6 text-lg",
    large: "px-24 py-8 text-2xl",
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
        ]"
    >
        {{ text }}
    </button>
</template>
