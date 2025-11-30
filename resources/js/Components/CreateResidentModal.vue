<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import Modal from "@/Components/Modal.vue";
import CustomButton from "@/Components/CustomButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    organizationId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(["close", "created"]);

const form = useForm({
    name: "",
    date_of_birth: "",
    room_number: "",
    floor_number: "",
    pin_code: "",
    username: "", // Auto-generated resident ID
});

// Auto-generate username when modal opens
watch(
    () => props.show,
    async (isOpen) => {
        if (isOpen) {
            // Generate the next sequential ID from the backend
            await generateNextResidentId();
            // Auto-generate PIN code as well
            generateNewPin();
        } else {
            form.reset();
        }
    }
);

// Generate the next sequential resident ID
const generateNextResidentId = async () => {
    if (!props.organizationId) {
        // Fallback if organization ID is not available
        const randomId = Math.floor(100000 + Math.random() * 900000);
        form.username = randomId.toString();
        return;
    }

    try {
        // Fetch the next sequential ID from the backend using axios (includes CSRF token automatically)
        const response = await axios.get(`/api/admin/residents/next-id`, {
            params: {
                organization_id: props.organizationId,
            },
        });

        if (response.data && response.data.next_id) {
            form.username = response.data.next_id;
        } else {
            // Fallback: start with organization ID + 00001
            const orgIdStr = props.organizationId.toString();
            const padding = 6 - orgIdStr.length;
            form.username = orgIdStr + "0".repeat(padding - 1) + "1";
        }
    } catch (error) {
        console.error("Error generating resident ID:", error);
        // Fallback: start with organization ID + 00001
        const orgIdStr = props.organizationId.toString();
        const padding = 6 - orgIdStr.length;
        form.username = orgIdStr + "0".repeat(padding - 1) + "1";
    }
};

const closeModal = () => {
    form.reset();
    emit("close");
};

const submitForm = () => {
    form.post(route("admin.residents.store"), {
        onSuccess: () => {
            emit("created");
            closeModal();
        },
        onError: (errors) => {
            console.error("Create errors:", errors);
        },
    });
};

const generateNewPin = () => {
    // Generate a new 6-digit PIN
    const newPin = Math.floor(100000 + Math.random() * 900000).toString();
    form.pin_code = newPin;
};

const regenerateUsername = async () => {
    // Regenerate the next sequential ID from the backend
    await generateNextResidentId();
};
</script>

<template>
    <Modal 
        :show="show" 
        @close="closeModal" 
        max-width="md"
        title="Create Resident"
        header-bg="primary"
    >
        <div class="bg-white px-6 py-4">

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Name Field -->
                <div>
                    <label for="create-resident-name" class="block text-sm font-medium text-black mb-2">
                        Full Name
                    </label>
                    <input
                        id="create-resident-name"
                        v-model="form.name"
                        type="text"
                        class="w-full px-4 py-3 border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        :class="{ 'border-red-500': form.errors.name }"
                        placeholder="Enter resident's full name"
                        required
                    />
                    <div
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- Resident ID Field (Auto-generated) -->
                <div>
                    <label for="create-resident-username" class="block text-sm font-medium text-black mb-2">
                        Resident ID (Username)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input
                            id="create-resident-username"
                            v-model="form.username"
                            type="text"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            class="flex-1 px-4 py-3 border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent font-mono bg-gray-50"
                            :class="{ 'border-red-500': form.errors.username }"
                            :placeholder="
                                props.organizationId
                                    ? `${props.organizationId}XXXXX`
                                    : 'XXXXXX'
                            "
                            required
                        />
                        <button
                            type="button"
                            @click="regenerateUsername"
                            class="px-4 py-3 bg-accent hover:bg-pressed text-black rounded-lg font-medium transition-colors sm:w-auto w-full"
                        >
                            Regenerate
                        </button>
                    </div>
                    <div
                        v-if="form.errors.username"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.username }}
                    </div>
                    <p class="mt-1 text-xs text-gray-600">
                        Resident ID is a 6-digit number (auto-generated
                        sequentially). This will be used as the username for
                        login.
                    </p>
                </div>

                <!-- Date of Birth Field -->
                <div>
                    <label for="create-resident-dob" class="block text-sm font-medium text-black mb-2">
                        Date of Birth
                    </label>
                    <input
                        id="create-resident-dob"
                        v-model="form.date_of_birth"
                        type="date"
                        class="w-full px-4 py-3 border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        :class="{ 'border-red-500': form.errors.date_of_birth }"
                        required
                    />
                    <div
                        v-if="form.errors.date_of_birth"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.date_of_birth }}
                    </div>
                </div>

                <!-- Room and Floor Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            for="create-resident-room"
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Room Number
                        </label>
                        <input
                            id="create-resident-room"
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
                            for="create-resident-floor"
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Floor Number
                        </label>
                        <input
                            id="create-resident-floor"
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
                    <label for="create-resident-pin" class="block text-sm font-medium text-black mb-2">
                        PIN Code
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input
                            id="create-resident-pin"
                            v-model="form.pin_code"
                            type="text"
                            maxlength="6"
                            class="flex-1 px-4 py-3 border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                            :class="{ 'border-red-500': form.errors.pin_code }"
                            placeholder="6-digit PIN"
                            required
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
                        :text="form.processing ? 'Creating...' : 'Create Resident'"
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
