<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ViewDetailsModal from "@/Components/ViewDetailsModal.vue";
import RejectionReasonModal from "@/Components/RejectionReasonModal.vue";
import DeleteConfirmationModal from "@/Components/DeleteConfirmationModal.vue";
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
const showRejectionModal = ref(false);
const applicationToReject = ref(null);
const showDeleteModal = ref(false);
const applicationToDelete = ref(null);

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

const rejectApplication = (applicationId, rejectionReason = null) => {
    const form = useForm({
        rejection_reason: rejectionReason || null,
    });
    form.post(route("admin.volunteers.reject", applicationId), {
        onSuccess: () => {
            showRejectionModal.value = false;
            applicationToReject.value = null;
        },
    });
};

const handleRejectClick = (application) => {
    applicationToReject.value = application;
    showRejectionModal.value = true;
};

const handleRejectionConfirm = ({ applicationId, rejectionReason }) => {
    rejectApplication(applicationId, rejectionReason);
};

const handleRejectionClose = () => {
    showRejectionModal.value = false;
    applicationToReject.value = null;
};

const deleteApplication = (applicationId) => {
    // Find the application object
    const application = props.volunteerApplications.find(
        (app) => app.id === applicationId
    );
    if (application) {
        applicationToDelete.value = application;
        showDeleteModal.value = true;
    }
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    applicationToDelete.value = null;
};

const handleVolunteerDeleted = () => {
    // Refresh the page to show updated data
    window.location.reload();
};

// DataTable event handlers
const handleView = (application) => {
    openModal(application);
};

const handleApprove = (application) => {
    approveApplication(application.id);
};

const handleReject = (application) => {
    handleRejectClick(application);
};

const handleDelete = (application) => {
    deleteApplication(application.id);
};
</script>

<template>
    <Head title="Volunteer Management" />

    <AppLayout title="Volunteer Management">
        <main role="main" aria-label="Volunteer Management">
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1 class="sr-only">Volunteer Management</h1>
                    <!-- Success Message -->
                    <div
                        v-if="flash.success"
                        class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                        role="alert"
                        aria-live="assertive"
                        aria-atomic="true"
                    >
                        <span class="block sm:inline">{{ flash.success }}</span>
                    </div>
                    <!-- Quick Stats -->
                    <section
                        aria-label="Volunteer Statistics"
                        class="bg-primary overflow-hidden shadow-lg rounded-lg p-6 mb-6 border-2 border-primary"
                    >
                        <h2
                            class="text-lg font-semibold text-white mb-4 text-center"
                        >
                            Volunteer Statistics
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6" role="list">
                            <div
                                class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                                role="listitem"
                                aria-label="Total Applications"
                            >
                                <div class="text-3xl font-bold text-white mb-2" aria-hidden="true">
                                    {{ pagination.total }}
                                </div>
                                <div
                                    class="text-white sm:text-sm lg:text-lg font-medium"
                                >
                                    Total Applications: {{ pagination.total }}
                                </div>
                            </div>
                            <div
                                class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                                role="listitem"
                                aria-label="Pending Applications"
                            >
                                <div class="text-3xl font-bold text-accent mb-2" aria-hidden="true">
                                    {{ pendingCount }}
                                </div>
                                <div
                                    class="text-white sm:text-sm lg:text-lg font-medium"
                                >
                                    Pending Applications: {{ pendingCount }}
                                </div>
                            </div>
                            <div
                                class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                                role="listitem"
                                aria-label="Approved Volunteers"
                            >
                                <div class="text-3xl font-bold text-white mb-2" aria-hidden="true">
                                    {{ approvedCount }}
                                </div>
                                <div
                                    class="text-white sm:text-sm lg:text-lg font-medium"
                                >
                                    Approved Volunteers: {{ approvedCount }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Volunteer Applications Table -->
                    <section
                        aria-label="Volunteer Applications Table"
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
                    </section>
                </div>
            </div>
        </main>

        <!-- View Details Modal -->
        <ViewDetailsModal
            :show="showModal"
            :selected-item="selectedApplication"
            item-type="volunteer"
            @close="closeModal"
        />

        <!-- Rejection Reason Modal -->
        <RejectionReasonModal
            :show="showRejectionModal"
            :application="applicationToReject"
            @close="handleRejectionClose"
            @confirm="handleRejectionConfirm"
        />

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmationModal
            :show="showDeleteModal"
            :item="applicationToDelete"
            item-type="volunteer"
            @close="closeDeleteModal"
            @deleted="handleVolunteerDeleted"
        />
    </AppLayout>
</template>
