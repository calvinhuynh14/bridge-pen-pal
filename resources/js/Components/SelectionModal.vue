<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "./Modal.vue";
import CustomButton from "./CustomButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    items: {
        type: Array,
        default: () => [],
    },
    currentSelection: {
        type: Array,
        default: () => [],
    },
    itemKey: {
        type: String,
        default: "id",
    },
    itemLabel: {
        type: String,
        default: "name",
    },
    updateRoute: {
        type: String,
        required: true,
    },
    formFieldName: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["close", "saved"]);

const form = useForm({
    [props.formFieldName]: props.currentSelection || [],
});

const selectedItems = ref(new Set(props.currentSelection || []));

// Watch for changes to currentSelection prop
watch(
    () => props.currentSelection,
    (newValue) => {
        selectedItems.value = new Set(newValue || []);
        form[props.formFieldName] = newValue || [];
    }
);

const toggleItem = (itemId) => {
    if (selectedItems.value.has(itemId)) {
        selectedItems.value.delete(itemId);
    } else {
        selectedItems.value.add(itemId);
    }
    form[props.formFieldName] = Array.from(selectedItems.value);
};

const closeModal = () => {
    emit("close");
};

const saveSelection = () => {
    form.put(route(props.updateRoute), {
        preserveScroll: true,
        onSuccess: () => {
            emit("saved");
            emit("close");
        },
    });
};

const isSelected = (itemId) => {
    return selectedItems.value.has(itemId);
};

const getItemLabel = (item) => {
    return item[props.itemLabel];
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="p-6 bg-background">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-3xl lg:text-4xl font-bold text-black">
                    {{ title }}
                </h3>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="w-6 h-6"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- Scrollable Items List -->
            <div
                class="max-h-[60vh] overflow-y-auto pr-2 mb-6"
                style="scrollbar-width: thin; scrollbar-color: #B8B8FF #DCDCFF"
            >
                <div class="space-y-2">
                    <button
                        v-for="item in items"
                        :key="item[itemKey]"
                        type="button"
                        @click="toggleItem(item[itemKey])"
                        :disabled="form.processing"
                        :class="[
                            'w-full text-left px-4 py-3 rounded-lg border-2 transition-all',
                            isSelected(item[itemKey])
                                ? 'border-primary bg-primary text-white font-medium'
                                : 'border-gray-300 bg-white hover:border-pressed hover:bg-pressed hover:text-white',
                            form.processing
                                ? 'opacity-50 cursor-not-allowed'
                                : 'cursor-pointer',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-base lg:text-lg">{{ getItemLabel(item) }}</span>
                            <svg
                                v-if="isSelected(item[itemKey])"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="w-5 h-5 text-white"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 12.75l6 6 9-13.5"
                                />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-4">
                <CustomButton
                    text="Cancel"
                    preset="neutral"
                    size="small"
                    @click="closeModal"
                    :disabled="form.processing"
                />
                <CustomButton
                    :text="form.processing ? 'Saving...' : 'Save'"
                    preset="primary"
                    size="small"
                    @click="saveSelection"
                    :disabled="form.processing"
                />
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* Custom scrollbar styling for webkit browsers */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #DCDCFF;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #B8B8FF;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #9B9BFF;
}
</style>

