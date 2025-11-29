<script setup>
import { computed } from "vue";

const props = defineProps({
    message: {
        type: String,
        required: true,
    },
    id: {
        type: String,
        default: null,
    },
    live: {
        type: String,
        default: "polite", // "polite" or "assertive"
        validator: (value) => ["polite", "assertive", "off"].includes(value),
    },
});

const messageId = computed(() => props.id || `success-${Math.random().toString(36).substr(2, 9)}`);
</script>

<template>
    <div
        v-show="message"
        :id="messageId"
        role="status"
        :aria-live="live"
        aria-atomic="true"
        class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg"
    >
        <p class="text-sm text-green-600 font-medium">
            {{ message }}
        </p>
    </div>
</template>

