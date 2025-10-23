<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
    residents: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: () => ({
            currentPage: 1,
            totalPages: 0,
            perPage: 10,
            total: 0,
            hasNextPage: false,
            hasPrevPage: false,
            nextPage: null,
            prevPage: null,
        }),
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

// Sorting state
const sortField = ref("application_date");
const sortDirection = ref("desc");

// Modal state
const showModal = ref(false);
const selectedResident = ref(null);

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
            case "application_date":
                aValue = new Date(a.application_date);
                bValue = new Date(b.application_date);
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

// Computed properties for total counts from backend
const pendingCount = computed(() => {
    return props.statusCounts.pending;
});

const approvedCount = computed(() => {
    return props.statusCounts.approved;
});

// Function to handle sorting
const handleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
};

// Function to get sort icon
const getSortIcon = (field) => {
    if (sortField.value !== field) {
        return ""; // No icon for neutral state
    }
    return sortDirection.value === "asc" ? "↑" : "↓";
};

// Modal functions
const openModal = (resident) => {
    selectedResident.value = resident;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedResident.value = null;
};

// Action methods
const editResident = (residentId) => {
    // TODO: Implement edit functionality
    console.log("Edit resident:", residentId);
};

const deleteResident = (residentId) => {
    if (
        confirm(
            "Are you sure you want to delete this resident? This action cannot be undone."
        )
    ) {
        // TODO: Implement delete functionality
        console.log("Delete resident:", residentId);
    }
};

// Function to get visible page numbers for pagination
const getVisiblePages = () => {
    const current = props.pagination.currentPage;
    const total = props.pagination.totalPages;
    const pages = [];

    if (total <= 7) {
        // Show all pages if 7 or fewer
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
    } else {
        // Always show first page
        pages.push(1);

        if (current > 4) {
            pages.push("...");
        }

        // Show pages around current page
        const start = Math.max(2, current - 1);
        const end = Math.min(total - 1, current + 1);

        for (let i = start; i <= end; i++) {
            if (i !== 1 && i !== total) {
                pages.push(i);
            }
        }

        if (current < total - 3) {
            pages.push("...");
        }

        // Always show last page
        if (total > 1) {
            pages.push(total);
        }
    }

    return pages;
};
</script>

<template>
    <Head title="Resident Management" />

    <AppLayout title="Resident Management">
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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div
                            class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                        >
                            <div class="text-3xl font-bold text-white mb-2">
                                {{ pagination.total }}
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
                            <div class="text-3xl font-bold text-accent mb-2">
                                {{ pendingCount }}
                            </div>
                            <div
                                class="text-white sm:text-sm lg:text-lg font-medium"
                            >
                                Pending Residents
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
                                Active Residents
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mb-6 flex flex-wrap gap-4 justify-center">
                    <button
                        class="bg-primary hover:bg-pressed text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        Print Residents
                    </button>
                    <button
                        class="bg-primary hover:bg-pressed text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        Batch Create
                    </button>
                    <button
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

                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-primary">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                                    >
                                        Avatar
                                    </th>
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
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="sortedResidents.length === 0">
                                    <td
                                        colspan="6"
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
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <div class="flex justify-center">
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
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <div
                                            class="text-sm font-medium text-black"
                                        >
                                            {{ resident.name }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <div
                                            class="text-sm font-medium text-black"
                                        >
                                            {{ resident.id }}
                                        </div>
                                    </td>
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
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{
                                                resident.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                resident.status.slice(1)
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center"
                                    >
                                        <div
                                            class="flex gap-2 justify-center flex-wrap"
                                        >
                                            <!-- View Details Button -->
                                            <button
                                                @click="openModal(resident)"
                                                class="bg-primary hover:bg-pressed text-white px-3 py-2 rounded-md text-xs font-medium transition-colors shadow-sm"
                                            >
                                                View
                                            </button>

                                            <!-- Edit Button -->
                                            <button
                                                @click="
                                                    editResident(resident.id)
                                                "
                                                class="bg-primary hover:bg-pressed text-white px-3 py-2 rounded-md text-xs font-medium transition-colors shadow-sm"
                                            >
                                                Edit
                                            </button>

                                            <!-- Delete Button -->
                                            <button
                                                @click="
                                                    deleteResident(resident.id)
                                                "
                                                class="bg-primary hover:bg-red-800 text-white px-3 py-2 rounded-md text-xs font-medium transition-colors shadow-sm"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden">
                        <div
                            v-if="sortedResidents.length === 0"
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
                                <!-- Mobile Card Content -->
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
                                                ID: {{ resident.id }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right side: Status and Actions -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Status Badge -->
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
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{
                                                resident.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                resident.status.slice(1)
                                            }}
                                        </span>

                                        <!-- Action Buttons - 2x2 Grid -->
                                        <div class="grid grid-cols-2 gap-1">
                                            <!-- View Button -->
                                            <button
                                                @click="openModal(resident)"
                                                class="bg-primary hover:bg-pressed text-white px-2 py-2 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                    class="size-4"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M15.75 2.25H21a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 1-1.5 0V4.81L8.03 17.03a.75.75 0 0 1-1.06-1.06L19.19 3.75h-3.44a.75.75 0 0 1 0-1.5Zm-10.5 4.5a1.5 1.5 0 0 0-1.5 1.5v10.5a1.5 1.5 0 0 0 1.5 1.5h10.5a1.5 1.5 0 0 0 1.5-1.5V10.5a.75.75 0 0 1 1.5 0v8.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V8.25a3 3 0 0 1 3-3h8.25a.75.75 0 0 1 0 1.5H5.25Z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>

                                            <!-- Edit Button -->
                                            <button
                                                @click="
                                                    editResident(resident.id)
                                                "
                                                class="bg-primary hover:bg-pressed text-white px-2 py-2 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                    class="size-4"
                                                >
                                                    <path
                                                        d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"
                                                    />
                                                    <path
                                                        d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"
                                                    />
                                                </svg>
                                            </button>

                                            <!-- Delete Button -->
                                            <button
                                                @click="
                                                    deleteResident(resident.id)
                                                "
                                                class="bg-primary hover:bg-red-800 text-white px-2 py-2 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                    class="size-4"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="pagination.totalPages > 1"
                        class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
                    >
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="pagination.hasPrevPage"
                                :href="`/admin/residents?page=${pagination.prevPage}`"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="pagination.hasNextPage"
                                :href="`/admin/residents?page=${pagination.nextPage}`"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div
                            class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{
                                        (pagination.currentPage - 1) *
                                            pagination.perPage +
                                        1
                                    }}</span>
                                    to
                                    <span class="font-medium">{{
                                        Math.min(
                                            pagination.currentPage *
                                                pagination.perPage,
                                            pagination.total
                                        )
                                    }}</span>
                                    of
                                    <span class="font-medium">{{
                                        pagination.total
                                    }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav
                                    class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    aria-label="Pagination"
                                >
                                    <Link
                                        v-if="pagination.hasPrevPage"
                                        :href="`/admin/residents?page=${pagination.prevPage}`"
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        <span class="sr-only">Previous</span>
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </Link>
                                    <template
                                        v-for="page in getVisiblePages()"
                                        :key="page"
                                    >
                                        <Link
                                            v-if="page !== '...'"
                                            :href="`/admin/residents?page=${page}`"
                                            :class="[
                                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                page === pagination.currentPage
                                                    ? 'z-10 bg-primary border-primary text-white'
                                                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            ]"
                                        >
                                            {{ page }}
                                        </Link>
                                        <span
                                            v-else
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                                        >
                                            ...
                                        </span>
                                    </template>
                                    <Link
                                        v-if="pagination.hasNextPage"
                                        :href="`/admin/residents?page=${pagination.nextPage}`"
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        <span class="sr-only">Next</span>
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Details Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click="closeModal"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
                @click.stop
            >
                <!-- Modal Header -->
                <div
                    class="flex justify-between items-center p-6 border-b border-gray-200"
                >
                    <h3 class="text-lg font-semibold text-gray-900">
                        Resident Details
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
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
                <div class="p-6" v-if="selectedResident">
                    <!-- Avatar and Name -->
                    <div class="flex items-center space-x-4 mb-6">
                        <div
                            class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center"
                        >
                            <span class="text-gray-600 text-xl font-medium">
                                {{
                                    selectedResident.name
                                        .charAt(0)
                                        .toUpperCase()
                                }}
                            </span>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900">
                                {{ selectedResident.name }}
                            </h4>
                            <p class="text-gray-600">
                                {{ selectedResident.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Status
                        </label>
                        <span
                            :class="{
                                'bg-accent text-black':
                                    selectedResident.status === 'pending',
                                'bg-green-100 text-green-800 border border-green-200':
                                    selectedResident.status === 'approved',
                                'bg-red-100 text-red-800 border border-red-200':
                                    selectedResident.status === 'rejected',
                            }"
                            class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                        >
                            {{
                                selectedResident.status
                                    .charAt(0)
                                    .toUpperCase() +
                                selectedResident.status.slice(1)
                            }}
                        </span>
                    </div>

                    <!-- ID -->
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Resident ID
                        </label>
                        <p class="text-gray-900 font-mono">
                            {{ selectedResident.id }}
                        </p>
                    </div>

                    <!-- Application Notes -->
                    <div v-if="selectedResident.application_notes" class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Application Notes
                        </label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">
                                {{ selectedResident.application_notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Medical Notes -->
                    <div v-if="selectedResident.medical_notes" class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Medical Notes
                        </label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">
                                {{ selectedResident.medical_notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end p-6 border-t border-gray-200">
                    <button
                        @click="closeModal"
                        class="bg-primary hover:bg-pressed text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
