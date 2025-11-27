<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import CustomButton from "@/Components/CustomButton.vue";

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
    <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Background overlay -->
        <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="handleClose"
        ></div>

        <!-- Modal container -->
        <div
            class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
            <!-- Modal panel -->
            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            >
                <!-- Header -->
                <div class="bg-primary px-6 py-4">
                    <h3
                        class="text-lg font-semibold text-black"
                        id="modal-title"
                    >
                        Reject Volunteer Application
                    </h3>
                    <p class="mt-1 text-sm text-black/80">
                        Are you sure you want to reject this application?
                        Optionally provide a reason for rejection.
                    </p>
                </div>

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
                        size="medium"
                        class="w-full sm:w-auto"
                        @click="handleConfirm"
                        :disabled="form.processing"
                    />
                    <CustomButton
                        text="Cancel"
                        preset="neutral"
                        size="medium"
                        class="mt-3 w-full sm:mt-0 sm:w-auto"
                        @click="handleClose"
                        :disabled="form.processing"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

