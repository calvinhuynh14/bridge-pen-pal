<script setup>
import { computed } from "vue";

const props = defineProps({
    message: String,
    id: {
        type: String,
        default: null,
    },
});

const errorId = computed(() => props.id || `error-${Math.random().toString(36).substr(2, 9)}`);
</script>

<template>
    <div 
        v-show="message" 
        :id="errorId"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
    >
        <p class="text-sm text-red-600 dark:text-red-400">
            {{ message }}
        </p>
    </div>
</template>
