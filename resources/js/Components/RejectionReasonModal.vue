<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import CustomButton from "@/Components/CustomButton.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    application: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "confirm"]);

const form = useForm({
    rejection_reason: "",
});

// Reset form when modal opens/closes
watch(
    () => props.show,
    (newValue) => {
        if (newValue) {
            form.reset();
            form.clearErrors();
        }
    }
);

const handleClose = () => {
    form.reset();
    form.clearErrors();
    emit("close");
};

const handleConfirm = () => {
    emit("confirm", {
        applicationId: props.application?.id,
        rejectionReason: form.rejection_reason || null,
    });
    form.reset();
    form.clearErrors();
};
</script>

<template>
    <Modal
        :show="show"
        @close="handleClose"
        max-width="lg"
        title="Reject Volunteer Application"
        description="Are you sure you want to reject this application? Optionally provide a reason for rejection."
        header-bg="primary"
    >
        <!-- Content -->
        <div class="bg-white px-6 py-4">
            <div class="mb-4">
                <label
                    for="rejection_reason"
                    class="block text-sm font-medium text-gray-700 mb-2"
                >
                    Reason for Rejection (Optional)
                </label>
                <textarea
                    id="rejection_reason"
                    v-model="form.rejection_reason"
                    rows="4"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                    placeholder="Enter reason for rejection (optional)..."
                    maxlength="500"
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                    {{ form.rejection_reason.length }}/500 characters
                </p>
                <p
                    v-if="form.errors.rejection_reason"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ form.errors.rejection_reason }}
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div
            class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3"
        >
            <CustomButton
                text="Reject Application"
                preset="error"
                size="small"
                class="w-full sm:w-auto"
                @click="handleConfirm"
                :disabled="form.processing"
            />
            <CustomButton
                text="Cancel"
                preset="neutral"
                size="small"
                class="mt-3 w-full sm:mt-0 sm:w-auto"
                @click="handleClose"
                :disabled="form.processing"
            />
        </div>
    </Modal>
</template>

