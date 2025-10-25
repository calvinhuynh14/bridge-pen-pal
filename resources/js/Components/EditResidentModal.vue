<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    resident: {
        type: Object,
        default: () => null,
    },
});

const emit = defineEmits(["close", "updated"]);

const form = useForm({
    name: "",
    room_number: "",
    floor_number: "",
    pin_code: "",
});

// Watch for resident changes to populate form
watch(
    () => props.resident,
    (newResident) => {
        if (newResident) {
            form.name = newResident.name || "";
            form.room_number = newResident.room_number || "";
            form.floor_number = newResident.floor_number || "";
            form.pin_code = newResident.pin_code || "";
        }
    },
    { immediate: true }
);

const closeModal = () => {
    form.reset();
    emit("close");
};

const submitForm = () => {
    if (!props.resident) return;

    form.put(route("admin.residents.update", props.resident.id), {
        onSuccess: () => {
            emit("updated");
            closeModal();
        },
        onError: (errors) => {
            console.error("Update errors:", errors);
        },
    });
};

const generateNewPin = () => {
    // Generate a new 6-digit PIN
    const newPin = Math.floor(100000 + Math.random() * 900000).toString();
    form.pin_code = newPin;
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="md">
        <div class="p-6 bg-background">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-black">Edit Resident</h3>
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

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Name Field -->
                <div>
                    <label class="block text-sm font-medium text-black mb-2">
                        Full Name
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-4 py-3 border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        :class="{ 'border-red-500': form.errors.name }"
                        placeholder="Enter resident's full name"
                    />
                    <div
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- Room and Floor Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Room Number
                        </label>
                        <input
                            v-model="form.room_number"
                            type="text"
                            class="w-full px-4 py-3 border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{
                                'border-red-500': form.errors.room_number,
                            }"
                            placeholder="e.g., 101"
                        />
                        <div
                            v-if="form.errors.room_number"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.room_number }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Floor Number
                        </label>
                        <input
                            v-model="form.floor_number"
                            type="text"
                            class="w-full px-4 py-3 border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{
                                'border-red-500': form.errors.floor_number,
                            }"
                            placeholder="e.g., 1"
                        />
                        <div
                            v-if="form.errors.floor_number"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.floor_number }}
                        </div>
                    </div>
                </div>

                <!-- PIN Code Field -->
                <div>
                    <label class="block text-sm font-medium text-black mb-2">
                        PIN Code
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input
                            v-model="form.pin_code"
                            type="text"
                            maxlength="6"
                            class="flex-1 px-4 py-3 border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                            :class="{ 'border-red-500': form.errors.pin_code }"
                            placeholder="6-digit PIN"
                        />
                        <button
                            type="button"
                            @click="generateNewPin"
                            class="px-4 py-3 bg-accent hover:bg-pressed text-black rounded-lg font-medium transition-colors sm:w-auto w-full"
                        >
                            Generate
                        </button>
                    </div>
                    <div
                        v-if="form.errors.pin_code"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.pin_code }}
                    </div>
                    <p class="mt-1 text-xs text-gray-600">
                        PIN code must be 6 digits. Residents use this to log in.
                    </p>
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
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3 bg-primary hover:bg-pressed text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Resident</span>
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
