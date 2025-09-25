<script setup>
import { onMounted, ref } from "vue";

defineProps({
    modelValue: String,
    placeholder: {
        type: String,
        default: "",
    },
});

defineEmits(["update:modelValue"]);

const input = ref(null);

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
        class="border-primary dark:border-gray-700 dark:bg-inputField dark:text-black focus:border-hover dark:focus:border-hover focus:ring-hover dark:focus:ring-hover rounded-md shadow-sm"
        :value="modelValue"
        :placeholder="placeholder"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
