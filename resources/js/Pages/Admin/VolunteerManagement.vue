<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ViewDetailsModal from "@/Components/ViewDetailsModal.vue";
import DataTable from "@/Components/DataTable.vue";

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

// Modal state
const showModal = ref(false);
const selectedApplication = ref(null);

// Computed properties for total counts from backend
const pendingCount = computed(() => {
    return props.statusCounts.pending;
});

const approvedCount = computed(() => {
    return props.statusCounts.approved;
});

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

// DataTable event handlers
const handleView = (application) => {
    openModal(application);
};

const handleApprove = (application) => {
    approveApplication(application.id);
};

const handleReject = (application) => {
    rejectApplication(application.id);
};

const handleDelete = (application) => {
    deleteApplication(application.id);
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
                    class="bg-white overflow-hidden shadow-lg rounded-lg border-2 border-primary p-6"
                >
                    <!-- DataTable Component -->
                    <DataTable
                        :items="volunteerApplications"
                        type="volunteer"
                        @view="handleView"
                        @approve="handleApprove"
                        @reject="handleReject"
                        @delete="handleDelete"
                    />
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
