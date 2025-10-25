<script setup>
import Modal from "@/Components/Modal.vue";
import { ref } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    selectedItem: {
        type: Object,
        default: null,
    },
    itemType: {
        type: String,
        default: "resident", // 'resident' or 'volunteer'
    },
});

const emit = defineEmits(["close"]);

// PIN visibility state
const isPinVisible = ref(false);

const togglePinVisibility = () => {
    isPinVisible.value = !isPinVisible.value;
};

const closeModal = () => {
    emit("close");
};

const getStatusClasses = (status) => {
    const baseClasses =
        "inline-flex px-3 py-1 text-sm font-semibold rounded-full";

    switch (status) {
        case "pending":
            return `${baseClasses} bg-accent text-black`;
        case "approved":
            return `${baseClasses} bg-green-100 text-green-800 border border-green-200`;
        case "rejected":
            return `${baseClasses} bg-red-100 text-red-800 border border-red-200`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800`;
    }
};

const getItemTitle = () => {
    return props.itemType === "volunteer"
        ? "Volunteer Details"
        : "Resident Details";
};

const getIdLabel = () => {
    return props.itemType === "volunteer" ? "Volunteer ID" : "Resident ID";
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="md">
        <div class="p-6 bg-background">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-black">
                    {{ getItemTitle() }}
                </h3>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-pressed transition-colors"
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

            <!-- Modal Body -->
            <div v-if="selectedItem" class="space-y-6">
                <!-- Avatar and Name -->
                <div class="flex items-center space-x-4">
                    <div
                        class="w-16 h-16 bg-primary rounded-full flex items-center justify-center border-2 border-pressed"
                    >
                        <span class="text-black text-xl font-medium">
                            {{ selectedItem.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <div>
                        <h4 class="text-xl font-semibold text-black">
                            {{ selectedItem.name }}
                        </h4>
                        <p class="text-gray-600" v-if="selectedItem.email">
                            {{ selectedItem.email }}
                        </p>
                        <p
                            class="text-gray-600"
                            v-else-if="itemType === 'volunteer'"
                        >
                            Email address
                        </p>
                        <p class="text-gray-600" v-else>No email address</p>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-black mb-2">
                        Status
                    </label>
                    <span :class="getStatusClasses(selectedItem.status)">
                        {{
                            selectedItem.status.charAt(0).toUpperCase() +
                            selectedItem.status.slice(1)
                        }}
                    </span>
                </div>

                <!-- ID (only for residents) -->
                <div v-if="itemType === 'resident'">
                    <label class="block text-sm font-medium text-black mb-2">
                        {{ getIdLabel() }}
                    </label>
                    <p
                        class="text-black font-mono bg-white border-2 border-primary px-3 py-2 rounded-lg"
                    >
                        {{ selectedItem.username || selectedItem.id }}
                    </p>
                </div>

                <!-- Additional fields for residents -->
                <div
                    v-if="itemType === 'resident' && selectedItem.room_number"
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                >
                    <div v-if="selectedItem.room_number">
                        <label
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Room Number
                        </label>
                        <p
                            class="text-black bg-white border-2 border-primary px-3 py-2 rounded-lg"
                        >
                            {{ selectedItem.room_number }}
                        </p>
                    </div>
                    <div v-if="selectedItem.floor_number">
                        <label
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Floor Number
                        </label>
                        <p
                            class="text-black bg-white border-2 border-primary px-3 py-2 rounded-lg"
                        >
                            {{ selectedItem.floor_number }}
                        </p>
                    </div>
                </div>

                <!-- Date of Birth for residents -->
                <div
                    v-if="itemType === 'resident' && selectedItem.date_of_birth"
                >
                    <label class="block text-sm font-medium text-black mb-2">
                        Date of Birth
                    </label>
                    <p
                        class="text-black bg-white border-2 border-primary px-3 py-2 rounded-lg"
                    >
                        {{ selectedItem.date_of_birth }}
                    </p>
                </div>

                <!-- PIN Code for residents -->
                <div v-if="itemType === 'resident' && selectedItem.pin_code">
                    <label class="block text-sm font-medium text-black mb-2">
                        PIN Code
                    </label>
                    <button
                        @click="togglePinVisibility"
                        class="text-black font-mono bg-white border-2 border-primary px-3 py-2 rounded-lg hover:bg-pressed hover:text-white transition-colors"
                    >
                        {{ isPinVisible ? selectedItem.pin_code : "••••••" }}
                    </button>
                </div>

                <!-- Volunteer-specific fields -->
                <div v-if="itemType === 'volunteer'">
                    <!-- Application Date -->
                    <div v-if="selectedItem.application_date">
                        <label
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Application Date
                        </label>
                        <p
                            class="text-black bg-white border-2 border-primary px-3 py-2 rounded-lg"
                        >
                            {{ formatDate(selectedItem.application_date) }}
                        </p>
                    </div>

                    <!-- Application Notes -->
                    <div v-if="selectedItem.application_notes">
                        <label
                            class="block text-sm font-medium text-black mb-2"
                        >
                            Application Notes
                        </label>
                        <div
                            class="text-black bg-white border-2 border-primary px-3 py-2 rounded-lg min-h-[100px] whitespace-pre-wrap"
                        >
                            {{ selectedItem.application_notes }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end mt-8 pt-6 border-t-2 border-primary">
                <button
                    @click="closeModal"
                    class="bg-primary hover:bg-pressed text-black px-6 py-3 rounded-lg text-sm font-medium transition-colors shadow-sm"
                >
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template>
