<script setup>
import { computed, onMounted, ref, useAttrs } from "vue";

const props = defineProps({
    modelValue: String,
    placeholder: {
        type: String,
        default: "",
    },
    errorId: {
        type: String,
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["update:modelValue"]);

const attrs = useAttrs();
const input = ref(null);
const inputId = computed(() => attrs.id || `input-${Math.random().toString(36).substr(2, 9)}`);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        :id="inputId"
        class="border-primary dark:border-gray-700 dark:bg-inputField dark:text-black focus:border-hover dark:focus:border-hover focus:ring-hover dark:focus:ring-hover rounded-md shadow-sm"
        :value="modelValue"
        :placeholder="placeholder"
        :aria-required="required"
        :aria-invalid="!!errorId"
        :aria-describedby="errorId || undefined"
        v-bind="$attrs"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
