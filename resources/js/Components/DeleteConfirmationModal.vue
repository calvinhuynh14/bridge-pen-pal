<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    item: {
        type: Object,
        default: () => null,
    },
    itemType: {
        type: String,
        default: "resident", // 'resident' or 'volunteer'
    },
});

const emit = defineEmits(["close", "deleted"]);

const form = useForm({});

const closeModal = () => {
    form.reset();
    emit("close");
};

const confirmDelete = () => {
    if (!props.item) return;

    const routeName =
        props.itemType === "resident"
            ? "admin.residents.destroy"
            : "admin.volunteers.destroy";

    form.delete(route(routeName, props.item.id), {
        onSuccess: () => {
            emit("deleted");
            closeModal();
        },
        onError: (errors) => {
            console.error("Delete errors:", errors);
        },
    });
};

const getItemName = () => {
    return props.item?.name || "this item";
};

const getItemTypeLabel = () => {
    return props.itemType === "volunteer" ? "volunteer" : "resident";
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="md">
        <div class="p-6 bg-background">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-black">
                    Delete
                    {{
                        getItemTypeLabel().charAt(0).toUpperCase() +
                        getItemTypeLabel().slice(1)
                    }}
                </h3>
                <button
                    @click="closeModal"
                    class="text-black hover:text-gray-600 transition-colors"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        ></path>
                    </svg>
                </button>
            </div>

            <!-- Warning Content -->
            <div class="mb-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg
                            class="w-8 h-8 text-red-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                            />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-lg font-medium text-black">
                            Are you sure?
                        </h4>
                    </div>
                </div>

                <div class="text-sm text-gray-600">
                    <p class="mb-2">
                        You are about to permanently delete
                        <strong class="text-black">{{ getItemName() }}</strong
                        >.
                    </p>
                    <p class="mb-2">This action cannot be undone and will:</p>
                    <ul class="list-disc list-inside space-y-1 ml-4">
                        <li>
                            Remove the {{ getItemTypeLabel() }}'s account and
                            all associated data
                        </li>
                        <li>
                            Delete any pen-pal connections or correspondence
                        </li>
                        <li>
                            Remove the {{ getItemTypeLabel() }} from all
                            organization records
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div
                class="flex justify-end space-x-3 pt-4 border-t border-gray-200"
            >
                <button
                    type="button"
                    @click="closeModal"
                    class="px-6 py-3 bg-white border-2 border-primary text-primary rounded-lg font-medium hover:bg-pressed hover:text-white transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="confirmDelete"
                    :disabled="form.processing"
                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Deleting...</span>
                    <span v-else
                        >Delete
                        {{
                            getItemTypeLabel().charAt(0).toUpperCase() +
                            getItemTypeLabel().slice(1)
                        }}</span
                    >
                </button>
            </div>
        </div>
    </Modal>
</template>
