<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import CustomButton from "@/Components/CustomButton.vue";

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
    <Modal 
        :show="show" 
        @close="closeModal" 
        max-width="md"
        :title="`Delete ${getItemTypeLabel().charAt(0).toUpperCase() + getItemTypeLabel().slice(1)}`"
        header-bg="primary"
    >
        <div class="bg-white px-6 py-4">

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
                class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3"
            >
                <CustomButton
                    :text="form.processing ? 'Deleting...' : `Delete ${getItemTypeLabel().charAt(0).toUpperCase() + getItemTypeLabel().slice(1)}`"
                    preset="error"
                    size="small"
                    class="w-full sm:w-auto"
                    @click="confirmDelete"
                    :disabled="form.processing"
                />
                <CustomButton
                    text="Cancel"
                    preset="neutral"
                    size="small"
                    class="mt-3 w-full sm:mt-0 sm:w-auto"
                    @click="closeModal"
                    :disabled="form.processing"
                />
            </div>
        </div>
    </Modal>
</template>
