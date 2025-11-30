<script setup>
import { ref, watch, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    letter: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close"]);

const form = useForm({
    reason: "",
});

const isSubmitting = computed(() => form.processing);
const isSuccess = ref(false);

// Reset form when modal closes
watch(
    () => props.show,
    (isOpen) => {
        if (!isOpen) {
            form.reset();
            form.clearErrors();
            isSuccess.value = false;
        }
    }
);

const closeModal = () => {
    emit("close");
};

const submitReport = () => {
    if (!props.letter) return;

    form.post(`/platform/letters/${props.letter.id}/report`, {
        onSuccess: () => {
            isSuccess.value = true;
            // Don't close modal, just hide input fields
        },
        onError: (errors) => {
            // Errors will be displayed inline via form.errors
        },
    });
};

// Character counter
const characterCount = computed(() => form.reason.length);
const remainingCharacters = computed(() => 500 - characterCount.value);
const isCharacterCountValid = computed(() => {
    return characterCount.value >= 20 && characterCount.value <= 500;
});

// Character count color classes
const characterCountClass = computed(() => {
    if (characterCount.value === 0) return "text-gray-700";
    if (characterCount.value < 20) return "text-yellow-700";
    if (characterCount.value > 500) return "text-red-700";
    return "text-gray-800";
});
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="md">
        <div class="p-6 bg-white">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-black">Report Letter</h3>
                <button
                    @click="closeModal"
                    class="text-black hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    aria-label="Close report modal"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="w-6 h-6"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- Success Message -->
            <div v-if="isSuccess" class="mb-4">
                <div
                    class="bg-green-50 border-2 border-green-200 rounded-lg p-4"
                >
                    <p class="text-green-800 font-medium">
                        Report submitted successfully. Our team will review it
                        shortly.
                    </p>
                </div>
            </div>

            <!-- Letter Info (only show if not success) -->
            <div v-if="!isSuccess && letter" class="mb-4">
                <p class="text-sm text-gray-700 mb-2">
                    Reporting letter from
                    <span class="font-semibold text-black">{{
                        letter.sender_name
                    }}</span>
                </p>
                <div class="bg-white border-2 border-gray-200 rounded-lg p-3">
                    <p
                        class="text-sm text-gray-900 line-clamp-3"
                        style="
                            display: -webkit-box;
                            -webkit-line-clamp: 3;
                            -webkit-box-orient: vertical;
                            overflow: hidden;
                        "
                    >
                        {{ letter.content }}
                    </p>
                </div>
            </div>

            <!-- Report Form (only show if not success) -->
            <div v-if="!isSuccess">
                <label
                    for="reportReason"
                    class="block text-sm font-medium text-black mb-2"
                >
                    Reason for reporting
                    <span class="text-red-600">*</span>
                </label>
                <textarea
                    id="reportReason"
                    v-model="form.reason"
                    rows="4"
                    class="w-full px-4 py-2 border-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none placeholder:text-gray-500"
                    :class="{
                        'border-red-500': form.errors.reason,
                        'border-gray-300': !form.errors.reason,
                    }"
                    placeholder="Please describe why you are reporting this letter (minimum 20 characters)..."
                    maxlength="500"
                ></textarea>
                <InputError
                    v-if="form.errors.reason"
                    :message="form.errors.reason"
                    class="mt-1"
                />
                <InputError
                    v-if="form.errors.message"
                    :message="form.errors.message"
                    class="mt-1"
                />

                <!-- Character Counter -->
                <div class="mt-2 flex justify-between items-center">
                    <p class="text-sm font-medium text-gray-800" :class="characterCountClass">
                        <span v-if="characterCount < 20">
                            {{ 20 - characterCount }} more characters required
                        </span>
                        <span v-else>
                            {{ remainingCharacters }} characters remaining
                        </span>
                    </p>
                    <p class="text-sm font-medium" :class="characterCountClass">
                        {{ characterCount }}/500
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 justify-end mt-6">
                <button
                    v-if="!isSuccess"
                    @click="closeModal"
                    class="px-4 py-2 bg-white border-2 border-primary text-primary rounded-lg font-medium hover:bg-primary hover:text-white transition-colors"
                >
                    Cancel
                </button>
                <button
                    v-if="!isSuccess"
                    @click="submitReport"
                    :disabled="!isCharacterCountValid || isSubmitting"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="isSubmitting"> Submitting... </span>
                    <span v-else> Submit Report </span>
                </button>
                <button
                    v-if="isSuccess"
                    @click="closeModal"
                    class="px-4 py-2 bg-primary hover:bg-pressed text-white rounded-lg font-medium transition-colors"
                >
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template>
