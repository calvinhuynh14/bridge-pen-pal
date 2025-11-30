<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import CustomButton from "@/Components/CustomButton.vue";

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
    <Modal 
        :show="show" 
        @close="closeModal" 
        max-width="md"
        title="Edit Resident"
        header-bg="primary"
    >
        <div class="bg-white px-6 py-4">

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Name Field -->
                <div>
                    <label for="edit-resident-name" class="block text-sm font-medium text-black mb-2">
                        Full Name
                    </label>
                    <input
                        id="edit-resident-name"
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
                            for="edit-resident-room"
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Room Number
                        </label>
                        <input
                            id="edit-resident-room"
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
                            for="edit-resident-floor"
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Floor Number
                        </label>
                        <input
                            id="edit-resident-floor"
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
                    <label for="edit-resident-pin" class="block text-sm font-medium text-black mb-2">
                        PIN Code
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input
                            id="edit-resident-pin"
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
                <div class="bg-gray-50 -mx-6 -mb-4 px-6 py-4 mt-6 sm:flex sm:flex-row-reverse sm:gap-3">
                    <CustomButton
                        :text="form.processing ? 'Updating...' : 'Update Resident'"
                        preset="primary"
                        size="small"
                        type="submit"
                        class="w-full sm:w-auto"
                        :disabled="form.processing"
                    />
                    <CustomButton
                        text="Cancel"
                        preset="neutral"
                        size="small"
                        type="button"
                        class="mt-3 w-full sm:mt-0 sm:w-auto"
                        @click="closeModal"
                        :disabled="form.processing"
                    />
                </div>
            </form>
        </div>
    </Modal>
</template>
