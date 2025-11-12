<script setup>
import { defineProps, defineEmits, computed } from "vue";

const props = defineProps({
    sortOrder: {
        type: String,
        default: "newest",
    },
    sortOptions: {
        type: Array,
        default: () => [
            { value: "newest", label: "Newest First" },
            { value: "oldest", label: "Oldest First" },
        ],
    },
    filterValue: {
        type: String,
        default: "all",
    },
    filterOptions: {
        type: Array,
        default: () => [{ value: "all", label: "All" }],
    },
    sortLabel: {
        type: String,
        default: "Sort by",
    },
    filterLabel: {
        type: String,
        default: "Filter by",
    },
    sortId: {
        type: String,
        default: null,
    },
    filterId: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["update:sortOrder", "update:filterValue"]);

// Generate unique IDs for accessibility
const sortSelectId = computed(
    () =>
        props.sortId || `sort-select-${Math.random().toString(36).substr(2, 9)}`
);
const filterSelectId = computed(
    () =>
        props.filterId ||
        `filter-select-${Math.random().toString(36).substr(2, 9)}`
);
</script>

<template>
    <div class="flex gap-2 flex-wrap">
        <!-- Sort Order -->
        <div class="flex-1 min-w-[120px]">
            <label :for="sortSelectId" class="sr-only">
                {{ sortLabel }}
            </label>
            <select
                :id="sortSelectId"
                :value="sortOrder"
                @change="emit('update:sortOrder', $event.target.value)"
                :aria-label="sortLabel"
                class="w-full bg-white rounded-lg border-2 border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 px-3 py-2 text-sm text-gray-900 focus:outline-none cursor-pointer transition-colors"
            >
                <option
                    v-for="option in sortOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>

        <!-- Filter -->
        <div
            v-if="filterOptions && filterOptions.length > 0"
            class="flex-1 min-w-[120px]"
        >
            <label :for="filterSelectId" class="sr-only">
                {{ filterLabel }}
            </label>
            <select
                :id="filterSelectId"
                :value="filterValue"
                @change="emit('update:filterValue', $event.target.value)"
                :aria-label="filterLabel"
                class="w-full bg-white rounded-lg border-2 border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 px-3 py-2 text-sm text-gray-900 focus:outline-none cursor-pointer transition-colors"
            >
                <option
                    v-for="option in filterOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>
    </div>
</template>
