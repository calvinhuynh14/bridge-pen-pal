<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ViewDetailsModal from "@/Components/ViewDetailsModal.vue";

const props = defineProps({
    volunteerApplications: {
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
const selectedApplication = ref(null);

// Computed property for sorted applications
const sortedApplications = computed(() => {
    const applications = [...props.volunteerApplications];

    return applications.sort((a, b) => {
        let aValue, bValue;

        switch (sortField.value) {
            case "name":
                aValue = a.name.toLowerCase();
                bValue = b.name.toLowerCase();
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
const openModal = (application) => {
    selectedApplication.value = application;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedApplication.value = null;
};

// Action methods
const approveApplication = (applicationId) => {
    const form = useForm({});
    form.post(route("admin.volunteers.approve", applicationId));
};

const rejectApplication = (applicationId) => {
    const form = useForm({});
    form.post(route("admin.volunteers.reject", applicationId));
};

const deleteApplication = (applicationId) => {
    if (
        confirm(
            "Are you sure you want to delete this volunteer application? This action cannot be undone."
        )
    ) {
        const form = useForm({});
        form.delete(route("admin.volunteers.delete", applicationId));
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
    <Head title="Volunteer Management" />

    <AppLayout title="Volunteer Management">
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
                        Volunteer Statistics
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
                                Total Applications
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
                                Pending Applications
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
                                Approved Volunteers
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Volunteer Applications Table -->
                <div
                    class="bg-white overflow-hidden shadow-lg rounded-lg border-2 border-primary"
                >
                    <div class="px-6 py-4 bg-primary">
                        <h3 class="text-2xl font-semibold text-black">
                            Volunteer Applications
                        </h3>
                        <p class="text-medium text-black opacity-90">
                            Review and manage volunteer applications for your
                            organization
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
                                            Volunteer
                                            <span class="text-sm">{{
                                                getSortIcon("name")
                                            }}</span>
                                        </div>
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider cursor-pointer hover:bg-pressed transition-colors"
                                        @click="handleSort('application_date')"
                                    >
                                        <div
                                            class="flex items-center justify-center gap-1"
                                        >
                                            Application Date
                                            <span class="text-sm">{{
                                                getSortIcon("application_date")
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
                                <tr v-if="sortedApplications.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-4 text-center text-gray-500"
                                    >
                                        No volunteer applications found
                                    </td>
                                </tr>
                                <tr
                                    v-for="application in sortedApplications"
                                    :key="application.id"
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
                                                        application.name
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
                                            {{ application.name }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-black text-center"
                                    >
                                        {{
                                            new Date(
                                                application.application_date
                                            ).toLocaleDateString()
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <span
                                            :class="{
                                                'bg-accent text-black':
                                                    application.status ===
                                                    'pending',
                                                'bg-green-100 text-green-800 border border-green-200':
                                                    application.status ===
                                                    'approved',
                                                'bg-red-100 text-red-800 border border-red-200':
                                                    application.status ===
                                                    'rejected',
                                            }"
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{
                                                application.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                application.status.slice(1)
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center"
                                    >
                                        <div
                                            class="flex gap-2 justify-center flex-wrap"
                                        >
                                            <!-- View Details Button (Always visible) -->
                                            <button
                                                @click="openModal(application)"
                                                class="bg-primary hover:bg-pressed text-white px-3 py-2 rounded-md text-xs font-medium transition-colors shadow-sm"
                                            >
                                                View Details
                                            </button>

                                            <!-- Approve Button (for pending and rejected) -->
                                            <button
                                                v-if="
                                                    application.status ===
                                                        'pending' ||
                                                    application.status ===
                                                        'rejected'
                                                "
                                                @click="
                                                    approveApplication(
                                                        application.id
                                                    )
                                                "
                                                class="bg-primary hover:bg-green-700 text-white px-3 py-2 rounded-md text-xs font-medium transition-colors shadow-sm"
                                            >
                                                Approve
                                            </button>

                                            <!-- Reject Button (for pending and approved) -->
                                            <button
                                                v-if="
                                                    application.status ===
                                                        'pending' ||
                                                    application.status ===
                                                        'approved'
                                                "
                                                @click="
                                                    rejectApplication(
                                                        application.id
                                                    )
                                                "
                                                class="bg-primary hover:bg-red-700 text-white px-3 py-2 rounded-md text-xs font-medium transition-colors shadow-sm"
                                            >
                                                Reject
                                            </button>

                                            <!-- Delete Button (for all statuses) -->
                                            <button
                                                @click="
                                                    deleteApplication(
                                                        application.id
                                                    )
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
                            v-if="sortedApplications.length === 0"
                            class="p-6 text-center text-gray-500"
                        >
                            No volunteer applications found
                        </div>
                        <div v-else class="divide-y divide-gray-200">
                            <div
                                v-for="application in sortedApplications"
                                :key="application.id"
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
                                                    application.name
                                                        .charAt(0)
                                                        .toUpperCase()
                                                }}
                                            </span>
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm font-medium text-black"
                                            >
                                                {{ application.name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{
                                                    new Date(
                                                        application.application_date
                                                    ).toLocaleDateString()
                                                }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right side: Status and Actions -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Status Badge -->
                                        <span
                                            :class="{
                                                'bg-accent text-black':
                                                    application.status ===
                                                    'pending',
                                                'bg-green-100 text-green-800 border border-green-200':
                                                    application.status ===
                                                    'approved',
                                                'bg-red-100 text-red-800 border border-red-200':
                                                    application.status ===
                                                    'rejected',
                                            }"
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{
                                                application.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                application.status.slice(1)
                                            }}
                                        </span>

                                        <!-- Action Buttons - 2x2 Grid -->
                                        <div class="grid grid-cols-2 gap-1">
                                            <!-- View Button (Always visible) -->
                                            <button
                                                @click="openModal(application)"
                                                class="bg-primary hover:bg-pressed text-white px-2 py-2 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                    class="size-5"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M15.75 2.25H21a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 1-1.5 0V4.81L8.03 17.03a.75.75 0 0 1-1.06-1.06L19.19 3.75h-3.44a.75.75 0 0 1 0-1.5Zm-10.5 4.5a1.5 1.5 0 0 0-1.5 1.5v10.5a1.5 1.5 0 0 0 1.5 1.5h10.5a1.5 1.5 0 0 0 1.5-1.5V10.5a.75.75 0 0 1 1.5 0v8.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V8.25a3 3 0 0 1 3-3h8.25a.75.75 0 0 1 0 1.5H5.25Z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>

                                            <!-- Approve Button (for pending and rejected) -->
                                            <button
                                                v-if="
                                                    application.status ===
                                                        'pending' ||
                                                    application.status ===
                                                        'rejected'
                                                "
                                                @click="
                                                    approveApplication(
                                                        application.id
                                                    )
                                                "
                                                class="bg-green-600 hover:bg-green-700 text-white px-2 py-2 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                    class="size-4"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>

                                            <!-- Reject Button (for pending and approved) -->
                                            <button
                                                v-if="
                                                    application.status ===
                                                        'pending' ||
                                                    application.status ===
                                                        'approved'
                                                "
                                                @click="
                                                    rejectApplication(
                                                        application.id
                                                    )
                                                "
                                                class="bg-red-600 hover:bg-red-700 text-white px-2 py-2 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                    class="size-4"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>

                                            <!-- Delete Button (for all statuses) -->
                                            <button
                                                @click="
                                                    deleteApplication(
                                                        application.id
                                                    )
                                                "
                                                class="bg-red-800 hover:bg-red-900 text-white px-2 py-2 rounded text-xs font-medium transition-colors flex items-center justify-center"
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
                </div>

                <!-- Pagination Controls -->
                <div
                    v-if="pagination.totalPages > 1"
                    class="mt-6 bg-white overflow-hidden shadow-lg rounded-lg border-2 border-primary"
                >
                    <div class="px-6 py-4">
                        <!-- Desktop Pagination -->
                        <div
                            class="hidden lg:flex items-center justify-between"
                        >
                            <!-- Pagination Info -->
                            <div class="text-sm text-gray-700">
                                Showing
                                {{
                                    (pagination.currentPage - 1) *
                                        pagination.perPage +
                                    1
                                }}
                                to
                                {{
                                    Math.min(
                                        pagination.currentPage *
                                            pagination.perPage,
                                        pagination.total
                                    )
                                }}
                                of {{ pagination.total }} results
                            </div>

                            <!-- Pagination Buttons -->
                            <div class="flex items-center space-x-2">
                                <!-- Previous Button -->
                                <Link
                                    v-if="pagination.hasPrevPage"
                                    :href="
                                        route('admin.volunteers', {
                                            page: pagination.prevPage,
                                        })
                                    "
                                    class="px-4 py-2 text-sm font-medium text-primary bg-white border-2 border-primary rounded-md hover:bg-primary hover:text-white transition-colors"
                                >
                                    Previous
                                </Link>
                                <span
                                    v-else
                                    class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-100 border-2 border-gray-200 rounded-md cursor-not-allowed"
                                >
                                    Previous
                                </span>

                                <!-- Page Numbers -->
                                <div class="flex items-center space-x-1">
                                    <template
                                        v-for="page in getVisiblePages()"
                                        :key="page"
                                    >
                                        <Link
                                            v-if="page !== '...'"
                                            :href="
                                                route('admin.volunteers', {
                                                    page: page,
                                                })
                                            "
                                            :class="{
                                                'bg-primary text-white border-2 border-primary':
                                                    page ===
                                                    pagination.currentPage,
                                                'bg-white text-primary border-2 border-primary hover:bg-primary hover:text-white':
                                                    page !==
                                                    pagination.currentPage,
                                            }"
                                            class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                                        >
                                            {{ page }}
                                        </Link>
                                        <span
                                            v-else
                                            class="px-3 py-2 text-sm font-medium text-gray-500"
                                        >
                                            ...
                                        </span>
                                    </template>
                                </div>

                                <!-- Next Button -->
                                <Link
                                    v-if="pagination.hasNextPage"
                                    :href="
                                        route('admin.volunteers', {
                                            page: pagination.nextPage,
                                        })
                                    "
                                    class="px-4 py-2 text-sm font-medium text-primary bg-white border-2 border-primary rounded-md hover:bg-primary hover:text-white transition-colors"
                                >
                                    Next
                                </Link>
                                <span
                                    v-else
                                    class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-100 border-2 border-gray-200 rounded-md cursor-not-allowed"
                                >
                                    Next
                                </span>
                            </div>
                        </div>

                        <!-- Mobile Pagination -->
                        <div class="lg:hidden">
                            <!-- Pagination Info -->
                            <div class="text-sm text-gray-700 text-center mb-4">
                                Page {{ pagination.currentPage }} of
                                {{ pagination.totalPages }}
                            </div>

                            <!-- Mobile Pagination Buttons -->
                            <div class="flex items-center justify-between">
                                <!-- Previous Button -->
                                <Link
                                    v-if="pagination.hasPrevPage"
                                    :href="
                                        route('admin.volunteers', {
                                            page: pagination.prevPage,
                                        })
                                    "
                                    class="px-4 py-2 text-sm font-medium text-primary bg-white border-2 border-primary rounded-md hover:bg-primary hover:text-white transition-colors"
                                >
                                    ← Previous
                                </Link>
                                <span
                                    v-else
                                    class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-100 border-2 border-gray-200 rounded-md cursor-not-allowed"
                                >
                                    ← Previous
                                </span>

                                <!-- Current Page -->
                                <div class="text-sm font-medium text-gray-700">
                                    {{ pagination.currentPage }} /
                                    {{ pagination.totalPages }}
                                </div>

                                <!-- Next Button -->
                                <Link
                                    v-if="pagination.hasNextPage"
                                    :href="
                                        route('admin.volunteers', {
                                            page: pagination.nextPage,
                                        })
                                    "
                                    class="px-4 py-2 text-sm font-medium text-primary bg-white border-2 border-primary rounded-md hover:bg-primary hover:text-white transition-colors"
                                >
                                    Next →
                                </Link>
                                <span
                                    v-else
                                    class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-100 border-2 border-gray-200 rounded-md cursor-not-allowed"
                                >
                                    Next →
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Details Modal -->
        <ViewDetailsModal
            :show="showModal"
            :selected-item="selectedApplication"
            item-type="volunteer"
            @close="closeModal"
        />
    </AppLayout>
</template>
