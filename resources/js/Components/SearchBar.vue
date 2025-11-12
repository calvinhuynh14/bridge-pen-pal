<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "Search...",
    },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <div
        class="relative bg-white rounded-lg border-2 border-gray-300 focus-within:border-primary"
    >
        <div
            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-4 text-gray-400"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                />
            </svg>
        </div>
        <input
            :value="modelValue"
            @input="emit('update:modelValue', $event.target.value)"
            type="text"
            :placeholder="placeholder"
            class="block w-full pl-9 pr-3 py-2 text-sm border-0 rounded-lg focus:ring-0 focus:outline-none text-gray-900 placeholder-gray-400"
        />
    </div>
</template>
