<script setup>
import { computed } from "vue";

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: "md", // xs, sm, md, lg, xl, 2xl, or custom classes
        validator: (value) =>
            ["xs", "sm", "md", "lg", "xl", "2xl", "custom"].includes(value),
    },
    customSize: {
        type: String,
        default: null, // For custom size classes like "w-16 h-16"
    },
    borderColor: {
        type: String,
        default: "white", // white, pressed, or custom class
    },
    borderWidth: {
        type: String,
        default: "2", // 2, 4, or custom
    },
    bgColor: {
        type: String,
        default: "primary", // pressed, primary, or custom class
    },
    textColor: {
        type: String,
        default: "white", // white, black, or custom class
    },
    opacity: {
        type: [String, Number],
        default: null, // e.g., "70" for opacity-70
    },
    alt: {
        type: String,
        default: null,
    },
    responsive: {
        type: Boolean,
        default: false, // For responsive sizes like "w-6 h-6 sm:w-9 sm:h-9"
    },
    customTextSize: {
        type: String,
        default: null, // For custom text size classes like "text-xs sm:text-base md:text-lg"
    },
});

// Size mappings
const sizeClasses = {
    xs: "w-6 h-6",
    sm: "w-8 h-8",
    md: "w-10 h-10",
    lg: "w-16 h-16",
    xl: "w-24 h-24",
    "2xl": "w-32 h-32",
};

const responsiveSizeClasses = {
    xs: "w-6 h-6 sm:w-8 sm:h-8",
    sm: "w-8 h-8 sm:w-10 sm:h-10",
    md: "w-10 h-10 sm:w-12 sm:h-12",
    lg: "w-16 h-16 sm:w-20 sm:h-20",
    xl: "w-24 h-24 sm:w-32 sm:h-32",
    "2xl": "w-32 h-32 sm:w-40 sm:h-40",
};

// Text size mappings
const textSizeClasses = {
    xs: "text-xs",
    sm: "text-sm",
    md: "text-sm",
    lg: "text-lg",
    xl: "text-3xl",
    "2xl": "text-4xl",
};

const responsiveTextSizeClasses = {
    xs: "text-xs sm:text-sm",
    sm: "text-sm sm:text-base",
    md: "text-sm sm:text-base",
    lg: "text-lg sm:text-xl",
    xl: "text-3xl sm:text-4xl",
    "2xl": "text-4xl sm:text-5xl",
};

// Get size classes
const sizeClass = computed(() => {
    if (props.size === "custom" && props.customSize) {
        return props.customSize;
    }
    if (props.responsive) {
        return responsiveSizeClasses[props.size] || sizeClasses[props.size];
    }
    return sizeClasses[props.size] || props.customSize || "w-10 h-10";
});

// Get text size classes
const textSizeClass = computed(() => {
    if (props.customTextSize) {
        return props.customTextSize;
    }
    if (props.responsive) {
        return (
            responsiveTextSizeClasses[props.size] || textSizeClasses[props.size]
        );
    }
    return textSizeClasses[props.size] || "text-sm";
});

// Get border color class
const borderColorClass = computed(() => {
    if (props.borderColor === "white") return "border-white";
    if (props.borderColor === "pressed") return "border-pressed";
    return props.borderColor; // Custom class
});

// Get background color class
const bgColorClass = computed(() => {
    if (props.bgColor === "pressed") return "bg-pressed";
    if (props.bgColor === "primary") return "bg-primary";
    return props.bgColor; // Custom class
});

// Get text color class
const textColorClass = computed(() => {
    if (props.textColor === "white") return "text-white";
    if (props.textColor === "black") return "text-black";
    return props.textColor; // Custom class
});

// Get opacity class
const opacityClass = computed(() => {
    if (!props.opacity) return "";
    if (typeof props.opacity === "number") {
        return `opacity-${props.opacity}`;
    }
    return `opacity-${props.opacity}`;
});

// Get border width class
const borderWidthClass = computed(() => {
    return `border-${props.borderWidth}`;
});

// Get initial letter
const initial = computed(() => {
    if (!props.name) return "?";
    return props.name.charAt(0).toUpperCase();
});

// Get alt text
const altText = computed(() => {
    return props.alt || `${props.name}'s avatar` || "Avatar";
});

// Get aria label for avatar
const ariaLabel = computed(() => {
    if (props.src) {
        return props.alt || `${props.name}'s profile picture` || "User avatar";
    }
    return `${props.name}'s avatar (initial: ${initial.value})` || "User avatar";
});
</script>

<template>
    <div
        v-if="src"
        :class="[
            sizeClass,
            'rounded-full overflow-hidden',
            borderWidthClass,
            borderColorClass,
            opacityClass,
        ]"
        role="img"
        :aria-label="ariaLabel"
    >
        <img :src="src" :alt="altText" class="w-full h-full object-cover" aria-hidden="true" />
    </div>
    <div
        v-else
        :class="[
            sizeClass,
            'rounded-full flex items-center justify-center',
            borderWidthClass,
            borderColorClass,
            bgColorClass,
            textColorClass,
            opacityClass,
        ]"
        role="img"
        :aria-label="ariaLabel"
    >
        <span :class="[textSizeClass, 'font-medium']" aria-hidden="true">
            {{ initial }}
        </span>
    </div>
</template>
