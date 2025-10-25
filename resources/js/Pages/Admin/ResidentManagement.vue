<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ResidentBatchModal from "@/Components/ResidentBatchModal.vue";
import ViewDetailsModal from "@/Components/ViewDetailsModal.vue";
import EditResidentModal from "@/Components/EditResidentModal.vue";
import DeleteConfirmationModal from "@/Components/DeleteConfirmationModal.vue";
import DataTable from "@/Components/DataTable.vue";

const props = defineProps({
    residents: {
        type: Array,
        default: () => [],
    },
    statusCounts: {
        type: Object,
        default: () => ({
            pending: 0,
            approved: 0,
            rejected: 0,
        }),
    },
    flash: {
        type: Object,
        default: () => ({}),
    },
});

// Sorting is now handled client-side in the DataTable component

// Modal state
const showModal = ref(false);
const selectedResident = ref(null);
const showBatchModal = ref(false);
const showEditModal = ref(false);
const editingResident = ref(null);
const showDeleteModal = ref(false);
const deletingResident = ref(null);

// PIN code visibility state
const visiblePins = ref(new Set());

// Sorting state
const sortField = ref("name");
const sortDirection = ref("asc");

// Computed properties for total counts from backend
const pendingCount = computed(() => {
    return props.statusCounts.pending;
});

const approvedCount = computed(() => {
    return props.statusCounts.approved;
});

const rejectedCount = computed(() => {
    return props.statusCounts.rejected;
});

const totalCount = computed(() => {
    return pendingCount.value + approvedCount.value + rejectedCount.value;
});

// Sorting is handled by the DataTable component

// Modal functions
const openModal = (resident) => {
    selectedResident.value = resident;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedResident.value = null;
};

// DataTable event handlers
const handleView = (resident) => {
    selectedResident.value = resident;
    showModal.value = true;
};

const handleEdit = (resident) => {
    editingResident.value = resident;
    showEditModal.value = true;
};

const handleDelete = (resident) => {
    deletingResident.value = resident;
    showDeleteModal.value = true;
};

// PIN toggle functions
const togglePinVisibility = (residentId) => {
    if (visiblePins.value.has(residentId)) {
        visiblePins.value.delete(residentId);
    } else {
        visiblePins.value.add(residentId);
    }
};

const isPinVisible = (residentId) => {
    return visiblePins.value.has(residentId);
};

// Sorting functions
const handleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
};

const getSortIcon = (field) => {
    if (sortField.value !== field) return "↕";
    return sortDirection.value === "asc" ? "↑" : "↓";
};

// Computed property for sorted residents
const sortedResidents = computed(() => {
    const residents = [...props.residents];

    return residents.sort((a, b) => {
        let aValue, bValue;

        switch (sortField.value) {
            case "name":
                aValue = a.name.toLowerCase();
                bValue = b.name.toLowerCase();
                break;
            case "id":
                aValue = parseInt(a.id);
                bValue = parseInt(b.id);
                break;
            case "status":
                aValue = a.status.toLowerCase();
                bValue = b.status.toLowerCase();
                break;
            default:
                return 0;
        }

        if (aValue < bValue) {
            return sortDirection.value === "asc" ? -1 : 1;
        }
        if (aValue > bValue) {
            return sortDirection.value === "asc" ? 1 : -1;
        }
        return 0;
    });
});

// Action methods
const editResident = (residentId) => {
    // TODO: Implement edit functionality
    console.log("Edit resident:", residentId);
};

const deleteResident = (residentId) => {
    // TODO: Implement delete functionality
    console.log("Delete resident:", residentId);
};

const createResident = () => {
    // TODO: Implement create functionality
    console.log("Create resident");
};

const openBatchModal = () => {
    showBatchModal.value = true;
};

const closeBatchModal = () => {
    showBatchModal.value = false;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingResident.value = null;
};

const handleResidentUpdated = () => {
    // Refresh the page to show updated data
    window.location.reload();
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deletingResident.value = null;
};

const handleResidentDeleted = () => {
    // Refresh the page to show updated data
    window.location.reload();
};
</script>

<template>
    <AppLayout>
        <Head title="Resident Management" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div
                    v-if="flash.success"
                    class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert"
                >
                    <span class="block sm:inline">{{ flash.success }}</span>
                </div>

                <!-- Quick Stats -->
                <div
                    class="bg-primary overflow-hidden shadow-lg rounded-lg p-6 mb-6 border-2 border-primary"
                >
                    <h3
                        class="text-lg font-semibold text-white mb-4 text-center"
                    >
                        Resident Statistics
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                        >
                            <div class="text-3xl font-bold text-white mb-2">
                                {{ totalCount }}
                            </div>
                            <div
                                class="text-white sm:text-sm lg:text-lg font-medium"
                            >
                                Total Residents
                            </div>
                        </div>
                        <div
                            class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                        >
                            <div class="text-3xl font-bold text-white mb-2">
                                {{ pendingCount }}
                            </div>
                            <div
                                class="text-white sm:text-sm lg:text-lg font-medium"
                            >
                                Pending
                            </div>
                        </div>
                        <div
                            class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                        >
                            <div class="text-3xl font-bold text-white mb-2">
                                {{ approvedCount }}
                            </div>
                            <div
                                class="text-white sm:text-sm lg:text-lg font-medium"
                            >
                                Approved
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mb-6 flex flex-col md:flex-row gap-4">
                    <button
                        @click="openBatchModal"
                        class="bg-primary hover:bg-pressed text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        Batch Create
                    </button>
                    <button
                        @click="createResident"
                        class="bg-primary hover:bg-pressed text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        Manual Create
                    </button>
                </div>

                <!-- Residents Table -->
                <div
                    class="bg-white overflow-hidden shadow-lg rounded-lg border-2 border-primary"
                >
                    <div class="px-6 py-4 bg-primary">
                        <h3 class="text-2xl font-semibold text-black">
                            Residents
                        </h3>
                        <p class="text-medium text-black opacity-90">
                            Manage residents in your organization
                        </p>
                    </div>

                    <!-- Simplified DataTable -->
                    <!-- Mobile Cards -->
                    <div class="lg:hidden">
                        <div
                            v-if="residents.length === 0"
                            class="p-6 text-center text-gray-500"
                        >
                            No residents found
                        </div>
                        <div v-else class="divide-y divide-gray-200">
                            <div
                                v-for="resident in sortedResidents"
                                :key="resident.id"
                                class="p-4 hover:bg-background transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <!-- Left side: Avatar and Name -->
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center"
                                        >
                                            <span
                                                class="text-gray-600 text-sm font-medium"
                                            >
                                                {{
                                                    resident.name
                                                        .charAt(0)
                                                        .toUpperCase()
                                                }}
                                            </span>
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm font-medium text-black"
                                            >
                                                {{ resident.name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                ID:
                                                {{
                                                    resident.username ||
                                                    resident.id
                                                }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right side: Actions -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Action Buttons -->
                                        <div class="flex space-x-1">
                                            <!-- View Button -->
                                            <button
                                                @click="handleView(resident)"
                                                class="bg-primary hover:bg-pressed text-white px-2 py-1 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                                title="View"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                    ></path>
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                    ></path>
                                                </svg>
                                            </button>

                                            <!-- Edit Button -->
                                            <button
                                                @click="handleEdit(resident)"
                                                class="bg-primary hover:bg-pressed text-white px-2 py-1 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                                title="Edit"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                    ></path>
                                                </svg>
                                            </button>

                                            <!-- Delete Button -->
                                            <button
                                                @click="handleDelete(resident)"
                                                class="bg-primary hover:bg-red-800 text-white px-2 py-1 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                                title="Delete"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    ></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-primary">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider cursor-pointer hover:bg-pressed transition-colors"
                                        @click="handleSort('name')"
                                    >
                                        <div
                                            class="flex items-center justify-center gap-1"
                                        >
                                            Name
                                            <span class="text-sm">{{
                                                getSortIcon("name")
                                            }}</span>
                                        </div>
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider cursor-pointer hover:bg-pressed transition-colors"
                                        @click="handleSort('id')"
                                    >
                                        <div
                                            class="flex items-center justify-center gap-1"
                                        >
                                            ID
                                            <span class="text-sm">{{
                                                getSortIcon("id")
                                            }}</span>
                                        </div>
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider cursor-pointer hover:bg-pressed transition-colors"
                                        @click="handleSort('status')"
                                    >
                                        <div
                                            class="flex items-center justify-center gap-1"
                                        >
                                            Status
                                            <span class="text-sm">{{
                                                getSortIcon("status")
                                            }}</span>
                                        </div>
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                                    >
                                        PIN Code
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="residents.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-4 text-center text-gray-500"
                                    >
                                        No residents found
                                    </td>
                                </tr>
                                <tr
                                    v-for="resident in sortedResidents"
                                    :key="resident.id"
                                    class="hover:bg-background transition-colors"
                                >
                                    <!-- Name -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-medium text-black"
                                        >
                                            {{ resident.name }}
                                        </div>
                                    </td>

                                    <!-- ID -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm text-black font-mono"
                                        >
                                            {{
                                                resident.username || resident.id
                                            }}
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <span
                                            :class="{
                                                'bg-accent text-black':
                                                    resident.status ===
                                                    'pending',
                                                'bg-green-100 text-green-800 border border-green-200':
                                                    resident.status ===
                                                    'approved',
                                                'bg-red-100 text-red-800 border border-red-200':
                                                    resident.status ===
                                                    'rejected',
                                            }"
                                            class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                                        >
                                            {{
                                                resident.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                resident.status.slice(1)
                                            }}
                                        </span>
                                    </td>

                                    <!-- PIN Code -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <button
                                            @click="
                                                togglePinVisibility(resident.id)
                                            "
                                            class="text-sm font-mono bg-white border-2 border-primary px-3 py-1 rounded hover:bg-pressed hover:text-white transition-colors"
                                        >
                                            {{
                                                isPinVisible(resident.id)
                                                    ? resident.pin_code
                                                    : "••••••"
                                            }}
                                        </button>
                                    </td>

                                    <!-- Actions -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
                                    >
                                        <div class="flex flex-col space-y-1">
                                            <button
                                                @click="handleView(resident)"
                                                class="bg-primary hover:bg-pressed text-white px-2 py-1 rounded text-xs font-medium transition-colors shadow-sm"
                                            >
                                                View
                                            </button>
                                            <button
                                                @click="handleEdit(resident)"
                                                class="bg-primary hover:bg-pressed text-white px-2 py-1 rounded text-xs font-medium transition-colors shadow-sm"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="handleDelete(resident)"
                                                class="bg-primary hover:bg-red-800 text-white px-2 py-1 rounded text-xs font-medium transition-colors shadow-sm"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Details Modal -->
        <ViewDetailsModal
            :show="showModal"
            :selected-item="selectedResident"
            item-type="resident"
            @close="closeModal"
        />

        <!-- Batch Create Modal -->
        <ResidentBatchModal
            :show="showBatchModal"
            :organization-id="1"
            :success="flash.success"
            :results="flash.results"
            :errors="flash.errors"
            @close="showBatchModal = false"
        />

        <!-- Edit Resident Modal -->
        <EditResidentModal
            :show="showEditModal"
            :resident="editingResident"
            @close="closeEditModal"
            @updated="handleResidentUpdated"
        />

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmationModal
            :show="showDeleteModal"
            :item="deletingResident"
            item-type="resident"
            @close="closeDeleteModal"
            @deleted="handleResidentDeleted"
        />
    </AppLayout>
</template>
