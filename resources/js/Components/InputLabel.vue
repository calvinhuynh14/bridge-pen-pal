<script setup>
import { computed, useAttrs } from "vue";

const props = defineProps({
    value: String,
    for: {
        type: String,
        default: null,
    },
});

const attrs = useAttrs();
const labelFor = computed(() => props.for || attrs.for || null);
</script>

<template>
    <label 
        :for="labelFor"
        class="block font-medium text-sm text-black dark:text-black"
    >
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
