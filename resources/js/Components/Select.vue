<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useAttrs } from "vue";

const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: "",
    },
    options: {
        type: Array,
        required: true,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: "",
    },
    searchable: {
        type: Boolean,
        default: false,
    },
    searchPlaceholder: {
        type: String,
        default: "Search...",
    },
    minOptionsForSearch: {
        type: Number,
        default: 5,
    },
});

const emit = defineEmits(["update:modelValue"]);

const attrs = useAttrs();

// Separate class from other attributes
const buttonAttrs = computed(() => {
    const { class: _, ...rest } = attrs;
    return rest;
});

const containerClass = computed(() => {
    return attrs.class || "";
});

// Determine if search should be enabled
const isSearchable = computed(() => {
    return (
        props.searchable || props.options.length >= props.minOptionsForSearch
    );
});

// Dropdown state
const isOpen = ref(false);
const searchQuery = ref("");
const dropdownRef = ref(null);
const searchInputRef = ref(null);

// Filtered options based on search
const filteredOptions = computed(() => {
    if (!isSearchable.value || !searchQuery.value.trim()) {
        return props.options;
    }
    const query = searchQuery.value.toLowerCase();
    return props.options.filter((option) =>
        option.label.toLowerCase().includes(query)
    );
});

// Get selected option label
const selectedLabel = computed(() => {
    if (props.modelValue === null) {
        const nullOption = props.options.find((opt) => opt.value === null);
        return nullOption ? nullOption.label : "";
    }
    if (props.modelValue === "" && props.placeholder) {
        return props.placeholder;
    }
    const selected = props.options.find(
        (opt) => opt.value === props.modelValue
    );
    return selected ? selected.label : props.placeholder || "Select...";
});

// Handle option selection
const selectOption = (option) => {
    let value;
    if (option.value === null) {
        value = null;
    } else {
        value = option.value;
    }
    emit("update:modelValue", value);
    isOpen.value = false;
    searchQuery.value = "";
};

// Toggle dropdown
const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && isSearchable.value && searchInputRef.value) {
        // Focus search input when dropdown opens
        setTimeout(() => {
            searchInputRef.value?.focus();
        }, 50);
    }
};

// Close dropdown
const closeDropdown = () => {
    isOpen.value = false;
    searchQuery.value = "";
};

// Handle click outside
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

// Handle keyboard navigation
const handleKeydown = (event) => {
    if (!isOpen.value) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            toggleDropdown();
        }
        return;
    }

    if (event.key === "Escape") {
        event.preventDefault();
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" :class="['relative', containerClass]">
        <!-- Button/Input that triggers dropdown -->
        <button
            type="button"
            @click="toggleDropdown"
            @keydown="handleKeydown"
            v-bind="buttonAttrs"
            :class="[
                'block w-full bg-white rounded-lg border-2 border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 px-3 py-2 text-sm text-gray-900 focus:outline-none cursor-pointer transition-colors text-left',
            ]"
            :aria-expanded="isOpen"
            :aria-haspopup="true"
        >
            <span
                :class="[
                    selectedLabel === placeholder
                        ? 'text-gray-400'
                        : 'text-gray-900',
                ]"
            >
                {{ selectedLabel }}
            </span>
            <span
                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    :class="[
                        'size-4 text-gray-400 transition-transform',
                        isOpen ? 'rotate-180' : '',
                    ]"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m19.5 8.25-7.5 7.5-7.5-7.5"
                    />
                </svg>
            </span>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute z-50 w-full mt-1 bg-white rounded-lg border-2 border-gray-300 shadow-lg max-h-60 overflow-hidden"
        >
            <!-- Search Input (if searchable) -->
            <div v-if="isSearchable" class="p-2 border-b border-gray-200">
                <div class="relative">
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
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="block w-full pl-9 pr-3 py-2 text-sm border-0 rounded-lg focus:ring-0 focus:outline-none text-gray-900 placeholder-gray-400"
                        @click.stop
                    />
                </div>
            </div>

            <!-- Options List -->
            <div class="overflow-y-auto max-h-48">
                <!-- Placeholder option (if provided and no search) -->
                <button
                    v-if="placeholder && !searchQuery.trim()"
                    type="button"
                    @click="selectOption({ value: '', label: placeholder })"
                    class="w-full px-3 py-2 text-sm text-left text-gray-400 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                >
                    {{ placeholder }}
                </button>

                <!-- Filtered Options -->
                <button
                    v-for="option in filteredOptions"
                    :key="option.value === null ? '__null__' : option.value"
                    type="button"
                    @click="selectOption(option)"
                    :class="[
                        'w-full px-3 py-2 text-sm text-left text-gray-900 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none',
                        modelValue === option.value ||
                        (modelValue === null && option.value === null) ||
                        (modelValue === '' && option.value === '')
                            ? 'bg-primary/10 font-medium'
                            : '',
                    ]"
                >
                    {{ option.label }}
                </button>

                <!-- No results message -->
                <div
                    v-if="
                        isSearchable &&
                        searchQuery.trim() &&
                        filteredOptions.length === 0
                    "
                    class="px-3 py-2 text-sm text-gray-500 text-center"
                >
                    No results found
                </div>
            </div>
        </div>
    </div>
</template>
