<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ResidentBatchModal from "@/Components/ResidentBatchModal.vue";
import ViewDetailsModal from "@/Components/ViewDetailsModal.vue";
import EditResidentModal from "@/Components/EditResidentModal.vue";
import CreateResidentModal from "@/Components/CreateResidentModal.vue";
import DeleteConfirmationModal from "@/Components/DeleteConfirmationModal.vue";
import DataTable from "@/Components/DataTable.vue";

const props = defineProps({
    residents: {
        type: Array,
        default: () => [],
    },
    organizationId: {
        type: Number,
        default: null,
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
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingResident = ref(null);
const showDeleteModal = ref(false);
const deletingResident = ref(null);

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

// Action methods
const editResident = (residentId) => {
    // TODO: Implement edit functionality
};

const deleteResident = (residentId) => {
    // TODO: Implement delete functionality
};

const createResident = () => {
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const handleResidentCreated = () => {
    // Refresh the page to show new resident
    window.location.reload();
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

// Print functionality
const printResidents = () => {
    // Print all residents (no status filtering needed)
    const residentsToPrint = props.residents;

    if (residentsToPrint.length === 0) {
        alert("No residents to print.");
        return;
    }

    // Debug: Log first resident to verify data structure
    if (approvedResidents.length > 0) {
        console.log("Sample resident data:", approvedResidents[0]);
        console.log("Resident ID (r.id):", approvedResidents[0].id);
        console.log("Username (User ID):", approvedResidents[0].username);
    }

    // Escape HTML to prevent XSS
    const escapeHtml = (text) => {
        if (!text) return "N/A";
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    };

    // Create print window content
    const printWindow = window.open("", "_blank");
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Resident Account Information</title>
            <style>
                @media print {
                    @page {
                        margin: 0.5in;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                    }
                    .no-print {
                        display: none;
                    }
                }
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                }
                h1 {
                    text-align: center;
                    margin-bottom: 20px;
                    color: #333;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    page-break-inside: auto;
                }
                tr {
                    page-break-inside: avoid;
                    page-break-after: auto;
                }
                thead {
                    display: table-header-group;
                }
                tfoot {
                    display: table-footer-group;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 12px;
                    text-align: left;
                    font-size: 14px;
                }
                th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                    text-align: center;
                }
                td {
                    text-align: center;
                }
                .resident-name {
                    font-weight: bold;
                }
                .user-id, .pin-code {
                    font-family: 'Courier New', monospace;
                    font-size: 16px;
                    letter-spacing: 2px;
                }
                .print-date {
                    text-align: right;
                    margin-bottom: 10px;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="print-date">Printed: ${escapeHtml(
                new Date().toLocaleString()
            )}</div>
            <h1>Resident Account Information</h1>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Resident Name</th>
                        <th style="width: 30%;">User ID</th>
                        <th style="width: 30%;">PIN Code</th>
                    </tr>
                </thead>
                <tbody>
                    ${residentsToPrint
                        .map((resident) => {
                            // IMPORTANT: Use username (User ID - 6 digit login ID), NOT id (resident table primary key)
                            // The username field contains the 6-digit User ID that residents use to log in
                            const userID = resident.username;
                            if (!userID) {
                                console.warn(
                                    "Resident missing username:",
                                    resident
                                );
                            }
                            return `
                        <tr>
                            <td class="resident-name">${escapeHtml(
                                resident.name || "N/A"
                            )}</td>
                            <td class="user-id">${escapeHtml(
                                userID || "N/A"
                            )}</td>
                            <td class="pin-code">${escapeHtml(
                                resident.pin_code || "N/A"
                            )}</td>
                        </tr>
                    `;
                        })
                        .join("")}
                </tbody>
            </table>
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();

    // Wait for content to load, then trigger print
    printWindow.onload = () => {
        setTimeout(() => {
            printWindow.print();
        }, 250);
    };
};
</script>

<template>
    <AppLayout>
        <Head title="Resident Management" />

        <main role="main" aria-label="Resident Management">
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1 class="sr-only">Resident Management</h1>
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
                        aria-label="Resident Statistics"
                        class="bg-primary overflow-hidden shadow-lg rounded-lg p-6 mb-6 border-2 border-primary"
                    >
                        <h2
                            class="text-lg font-semibold text-white mb-4 text-center"
                        >
                            Resident Statistics
                        </h2>
                        <div class="flex justify-center" role="list">
                            <div
                                class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                                role="listitem"
                                aria-label="Total Residents"
                            >
                                <div
                                    class="text-3xl font-bold text-white mb-2"
                                    aria-hidden="true"
                                >
                                    {{ totalCount }}
                                </div>
                                <div
                                    class="text-white sm:text-sm lg:text-lg font-medium"
                                >
                                    Total Residents: {{ totalCount }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Action Buttons -->
                    <section aria-label="Resident Actions" class="mb-6">
                        <div
                            class="flex flex-col md:flex-row gap-4"
                            role="group"
                            aria-label="Create resident options"
                        >
                            <button
                                @click="openBatchModal"
                                aria-label="Open batch create residents modal"
                                class="bg-primary hover:bg-pressed text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                            >
                                Batch Create
                            </button>
                            <button
                                @click="createResident"
                                aria-label="Open manual create resident modal"
                                class="bg-primary hover:bg-pressed text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                            >
                                Manual Create
                            </button>
                            <button
                                @click="printResidents"
                                aria-label="Print resident account information"
                                class="bg-primary hover:bg-pressed text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                            >
                                Print Accounts
                            </button>
                        </div>
                    </section>

                    <!-- Residents Table -->
                    <section
                        aria-label="Residents Table"
                        class="bg-white overflow-hidden shadow-lg rounded-lg border-2 border-primary p-6"
                    >
                        <!-- DataTable Component -->
                        <DataTable
                            :items="residents"
                            type="resident"
                            @view="handleView"
                            @edit="handleEdit"
                            @delete="handleDelete"
                        />
                    </section>
                </div>
            </div>
        </main>

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

        <!-- Create Resident Modal -->
        <CreateResidentModal
            :show="showCreateModal"
            :organization-id="organizationId"
            @close="closeCreateModal"
            @created="handleResidentCreated"
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
