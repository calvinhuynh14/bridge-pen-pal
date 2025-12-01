<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, computed, watch, onUnmounted } from "vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import CustomButton from "@/Components/CustomButton.vue";
import Dropdown from "@/Components/Dropdown.vue";

const props = defineProps({
    reports: {
        type: Array,
        default: () => [],
    },
    statistics: {
        type: Object,
        default: () => ({
            total: 0,
            pending: 0,
            resolved: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            status: "all",
            search: "",
        }),
    },
});

// Modal state
const showDetailsModal = ref(false);
const selectedReport = ref(null);

// Filter state
const activeStatusFilter = ref(props.filters.status || "all");
const searchQuery = ref(props.filters.search || "");

// Debounce search
const searchTimeout = ref(null);
watch(searchQuery, (newValue) => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
    searchTimeout.value = setTimeout(() => {
        updateFilters();
    }, 500); // 500ms debounce
});

onUnmounted(() => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
});

// Open report details modal
const openDetailsModal = (report) => {
    selectedReport.value = report;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedReport.value = null;
};

// Filter handlers
const setStatusFilter = (status) => {
    activeStatusFilter.value = status;
    updateFilters();
};

const updateFilters = () => {
    router.get(
        route("admin.reports"),
        {
            status: activeStatusFilter.value,
            search: searchQuery.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

// Action handlers
const handleResolve = (reportId) => {
    if (
        confirm(
            "Are you sure you want to mark this report as resolved? This action cannot be undone."
        )
    ) {
        router.post(
            route("admin.reports.resolve", reportId),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeDetailsModal();
                },
            }
        );
    }
};

const handleDismiss = (reportId) => {
    if (
        confirm(
            "Are you sure you want to dismiss this report? This action cannot be undone."
        )
    ) {
        router.post(
            route("admin.reports.dismiss", reportId),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeDetailsModal();
                },
            }
        );
    }
};

const handleBan = (reportId) => {
    if (
        confirm(
            "Are you sure you want to ban the reported user? This is a serious action."
        )
    ) {
        router.post(
            route("admin.reports.ban", reportId),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeDetailsModal();
                },
            }
        );
    }
};

const handleViewUser = (userId) => {
    // TODO: Navigate to user profile or open user details modal
};

// Format reports for DataTable
const formattedReports = computed(() => {
    return props.reports.map((report) => ({
        id: report.id,
        reported_user_name: report.reported_user_name || "N/A",
        reported_user_id: report.reported_user_id,
        reason: report.reason,
        status: report.status,
        created_at: report.created_at,
        resolved_at: report.resolved_at,
        reporter_name: report.reporter_name,
        reporter_id: report.reporter_id,
        reported_letter_id: report.reported_letter_id,
        admin_notes: report.admin_notes,
        resolved_by_name: report.resolved_by_name,
    }));
});

// Status filter buttons
const statusFilters = [
    { value: "all", label: "All", color: "bg-gray-500" },
    { value: "pending", label: "Pending", color: "bg-accent" },
    { value: "reviewed", label: "Reviewed", color: "bg-blue-500" },
    { value: "resolved", label: "Resolved", color: "bg-green-500" },
    { value: "dismissed", label: "Dismissed", color: "bg-gray-400" },
];
</script>

<template>
    <Head title="Report Management" />

    <AppLayout title="Report Management">
        <main role="main" aria-label="Report Management">
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1 class="sr-only">Report Management</h1>
                    <!-- Statistics -->
                    <section
                        aria-label="Report Statistics"
                        class="bg-primary overflow-hidden shadow-lg rounded-lg p-6 mb-6 border-2 border-primary"
                    >
                        <h2
                            class="text-lg font-semibold text-white mb-4 text-center"
                        >
                            Report Statistics
                        </h2>
                        <div
                            class="grid grid-cols-1 sm:grid-cols-3 gap-6"
                            role="list"
                        >
                            <div
                                class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                                role="listitem"
                                aria-label="Total Reports"
                            >
                                <div
                                    class="text-3xl font-bold text-white mb-2"
                                    aria-hidden="true"
                                >
                                    {{ statistics.total }}
                                </div>
                                <div
                                    class="text-white sm:text-sm lg:text-lg font-medium"
                                >
                                    Total Reports: {{ statistics.total }}
                                </div>
                            </div>
                            <div
                                class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                                role="listitem"
                                aria-label="Pending Reports"
                            >
                                <div
                                    class="text-3xl font-bold text-accent mb-2"
                                    aria-hidden="true"
                                >
                                    {{ statistics.pending }}
                                </div>
                                <div
                                    class="text-white sm:text-sm lg:text-lg font-medium"
                                >
                                    Pending Reports: {{ statistics.pending }}
                                </div>
                            </div>
                            <div
                                class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                                role="listitem"
                                aria-label="Resolved Reports"
                            >
                                <div
                                    class="text-3xl font-bold text-green-300 mb-2"
                                    aria-hidden="true"
                                >
                                    {{ statistics.resolved }}
                                </div>
                                <div
                                    class="text-white sm:text-sm lg:text-lg font-medium"
                                >
                                    Resolved Reports: {{ statistics.resolved }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Status Filter Dropdown -->
                    <section aria-label="Filter Reports" class="mb-6">
                        <Dropdown
                            align="left"
                            width="48"
                            aria-label="Filter reports by status"
                        >
                            <template #trigger="{ open }">
                                <button
                                    type="button"
                                    :aria-expanded="open ? 'true' : 'false'"
                                    aria-haspopup="true"
                                    aria-label="Filter reports by status"
                                    class="inline-flex items-center gap-2 px-2 py-1 text-sm md:px-4 md:py-2 md:text-sm rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 bg-primary text-white hover:bg-hover hover:text-white cursor-pointer"
                                >
                                    <span>
                                        {{
                                            statusFilters.find(
                                                (f) =>
                                                    f.value ===
                                                    activeStatusFilter
                                            )?.label || "Filter Status"
                                        }}
                                    </span>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        :class="[
                                            'size-4 transition-transform',
                                            open ? 'rotate-180' : '',
                                        ]"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m19.5 8.25-7.5 7.5-7.5-7.5"
                                        />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="py-1">
                                    <button
                                        v-for="filter in statusFilters"
                                        :key="filter.value"
                                        @click="setStatusFilter(filter.value)"
                                        :class="[
                                            'block w-full text-left px-4 py-2 text-sm text-white transition-colors',
                                            activeStatusFilter === filter.value
                                                ? 'bg-pressed font-semibold'
                                                : 'hover:bg-pressed hover:bg-opacity-80',
                                        ]"
                                        :aria-label="`Filter by ${filter.label}`"
                                        :aria-pressed="
                                            activeStatusFilter === filter.value
                                        "
                                    >
                                        {{ filter.label }}
                                    </button>
                                </div>
                            </template>
                        </Dropdown>
                    </section>

                    <!-- DataTable -->
                    <section
                        aria-label="Reports Table"
                        class="bg-white overflow-hidden shadow-lg rounded-lg border-2 border-primary p-6"
                    >
                        <DataTable
                            type="report"
                            :items="formattedReports"
                            :search-query="searchQuery"
                            @update:search-query="searchQuery = $event"
                            @view="openDetailsModal"
                            @resolve="handleResolve"
                            @dismiss="handleDismiss"
                            @ban="handleBan"
                            @view-user="handleViewUser"
                        />
                    </section>
                </div>

                <!-- Report Details Modal -->
                <Modal
                    :show="showDetailsModal"
                    @close="closeDetailsModal"
                    max-width="2xl"
                    title="Report Details"
                    header-bg="primary"
                >
                    <div class="bg-white px-6 py-4" v-if="selectedReport">
                        <div class="space-y-4">
                            <!-- Report Info -->
                            <div>
                                <h3
                                    class="text-lg font-semibold text-black mb-2"
                                >
                                    Report Information
                                </h3>
                                <div
                                    class="bg-white p-4 rounded-lg space-y-2 border-2 border-gray-200"
                                >
                                    <div>
                                        <span class="font-medium text-black"
                                            >Status:</span
                                        >
                                        <span
                                            :class="[
                                                'ml-2 px-2 py-1 rounded text-sm font-semibold',
                                                selectedReport.status ===
                                                'pending'
                                                    ? 'bg-accent text-black'
                                                    : selectedReport.status ===
                                                      'resolved'
                                                    ? 'bg-green-100 text-green-800'
                                                    : selectedReport.status ===
                                                      'dismissed'
                                                    ? 'bg-gray-100 text-gray-800'
                                                    : 'bg-blue-100 text-blue-800',
                                            ]"
                                        >
                                            {{
                                                selectedReport.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                selectedReport.status.slice(1)
                                            }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-black"
                                            >Reported Date:</span
                                        >
                                        <span class="ml-2 text-black">
                                            {{
                                                new Date(
                                                    selectedReport.created_at
                                                ).toLocaleString()
                                            }}
                                        </span>
                                    </div>

                                    <!-- Action Taken Section -->
                                    <div
                                        v-if="
                                            selectedReport.status !==
                                                'pending' &&
                                            selectedReport.resolved_by_name
                                        "
                                        class="mt-3 pt-3 border-t border-gray-300"
                                    >
                                        <div
                                            class="font-semibold text-black mb-2"
                                        >
                                            Action Taken:
                                        </div>
                                        <div class="text-black">
                                            <span
                                                v-if="
                                                    selectedReport.status ===
                                                    'resolved'
                                                "
                                                class="text-green-700 font-medium"
                                            >
                                                ✓ Resolved
                                            </span>
                                            <span
                                                v-else-if="
                                                    selectedReport.status ===
                                                    'dismissed'
                                                "
                                                class="text-gray-700 font-medium"
                                            >
                                                ✗ Dismissed
                                            </span>
                                            <span v-else class="font-medium">
                                                {{
                                                    selectedReport.status
                                                        .charAt(0)
                                                        .toUpperCase() +
                                                    selectedReport.status.slice(
                                                        1
                                                    )
                                                }}
                                            </span>
                                            <span class="ml-2">
                                                by
                                                {{
                                                    selectedReport.resolved_by_name
                                                }}
                                            </span>
                                            <span
                                                v-if="
                                                    selectedReport.resolved_at
                                                "
                                                class="ml-2 text-gray-600"
                                            >
                                                on
                                                {{
                                                    new Date(
                                                        selectedReport.resolved_at
                                                    ).toLocaleString()
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                    <div
                                        v-else-if="
                                            selectedReport.status === 'pending'
                                        "
                                        class="mt-3 pt-3 border-t border-gray-300"
                                    >
                                        <div class="text-gray-600 italic">
                                            No action taken yet
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporter Info -->
                            <div>
                                <h3
                                    class="text-lg font-semibold text-black mb-2"
                                >
                                    Reporter
                                </h3>
                                <div
                                    class="bg-white p-4 rounded-lg border-2 border-gray-200"
                                >
                                    <p class="font-medium text-black">
                                        {{ selectedReport.reporter_name }}
                                    </p>
                                </div>
                            </div>

                            <!-- Reported User Info -->
                            <div>
                                <h3
                                    class="text-lg font-semibold text-black mb-2"
                                >
                                    Reported User
                                </h3>
                                <div
                                    class="bg-white p-4 rounded-lg border-2 border-gray-200"
                                >
                                    <p class="font-medium text-black">
                                        {{
                                            selectedReport.reported_user_name ||
                                            "N/A"
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Reason -->
                            <div>
                                <h3
                                    class="text-lg font-semibold text-black mb-2"
                                >
                                    Reason
                                </h3>
                                <div
                                    class="bg-white p-4 rounded-lg border-2 border-gray-200"
                                >
                                    <p class="whitespace-pre-wrap text-black">
                                        {{ selectedReport.reason }}
                                    </p>
                                </div>
                            </div>

                            <!-- Admin Notes -->
                            <div v-if="selectedReport.admin_notes">
                                <h3
                                    class="text-lg font-semibold text-black mb-2"
                                >
                                    Admin Notes
                                </h3>
                                <div
                                    class="bg-white p-4 rounded-lg border-2 border-gray-200"
                                >
                                    <p class="whitespace-pre-wrap text-black">
                                        {{ selectedReport.admin_notes }}
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div
                                v-if="selectedReport.status === 'pending'"
                                class="flex flex-wrap gap-2 pt-4 border-t border-gray-300"
                            >
                                <CustomButton
                                    text="Resolve"
                                    preset="success"
                                    size="small"
                                    @click="handleResolve(selectedReport.id)"
                                />
                                <CustomButton
                                    text="Dismiss"
                                    preset="neutral"
                                    size="small"
                                    @click="handleDismiss(selectedReport.id)"
                                />
                                <CustomButton
                                    text="Ban User"
                                    preset="error"
                                    size="small"
                                    @click="handleBan(selectedReport.id)"
                                />
                                <CustomButton
                                    text="View User"
                                    preset="secondary"
                                    size="small"
                                    @click="
                                        handleViewUser(
                                            selectedReport.reported_user_id
                                        )
                                    "
                                />
                            </div>
                        </div>
                    </div>
                </Modal>
            </div>
        </main>
    </AppLayout>
</template>
