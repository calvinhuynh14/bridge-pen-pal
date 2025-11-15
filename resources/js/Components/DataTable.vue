<script setup>
import { ref, computed, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import Avatar from "@/Components/Avatar.vue";
import SearchBar from "@/Components/SearchBar.vue";

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    type: {
        type: String,
        default: "resident", // 'resident', 'volunteer', or 'report'
        validator: (value) =>
            ["resident", "volunteer", "report"].includes(value),
    },
    sortField: {
        type: String,
        default: null,
    },
    sortDirection: {
        type: String,
        default: "desc",
    },
    searchQuery: {
        type: String,
        default: "",
    },
});

const emit = defineEmits([
    "sort",
    "view",
    "edit",
    "delete",
    "approve",
    "reject",
    "resolve",
    "dismiss",
    "ban",
    "view-user",
    "update:searchQuery",
]);

// Pin code visibility state
const visiblePins = ref(new Set());

// Client-side sorting state
// Default sort field based on type
const defaultSortField = computed(() => {
    if (props.sortField) return props.sortField;
    if (props.type === "report") return "created_at";
    if (props.type === "volunteer") return "application_date";
    return "name";
});

const sortField = ref(defaultSortField.value);
const sortDirection = ref(props.sortDirection || "desc");

// Search state
const localSearchQuery = ref(props.searchQuery || "");

// Watch for external search query changes
watch(
    () => props.searchQuery,
    (newValue) => {
        localSearchQuery.value = newValue;
    }
);

// Emit search query updates
watch(localSearchQuery, (newValue) => {
    emit("update:searchQuery", newValue);
});

// Client-side pagination state
const currentPage = ref(1);
const itemsPerPage = ref(10);

const togglePinVisibility = (itemId) => {
    if (visiblePins.value.has(itemId)) {
        visiblePins.value.delete(itemId);
    } else {
        visiblePins.value.add(itemId);
    }
};

const isPinVisible = (itemId) => {
    return visiblePins.value.has(itemId);
};

// Computed properties for different data types
const columns = computed(() => {
    if (props.type === "resident") {
        return [
            { key: "avatar", label: "Avatar", sortable: false, mobile: false },
            { key: "name", label: "Name", sortable: true, mobile: true },
            { key: "id", label: "ID", sortable: true, mobile: true },
            { key: "status", label: "Status", sortable: true, mobile: true },
            {
                key: "room_floor",
                label: "Room/Floor",
                sortable: false,
                mobile: true,
            },
            {
                key: "pin_code",
                label: "PIN Code",
                sortable: false,
                mobile: true,
            },
            {
                key: "actions",
                label: "Actions",
                sortable: false,
                mobile: false,
            },
        ];
    } else if (props.type === "volunteer") {
        return [
            { key: "name", label: "Name", sortable: true, mobile: true },
            { key: "status", label: "Status", sortable: true, mobile: true },
            {
                key: "application_date",
                label: "Applied",
                sortable: true,
                mobile: true,
            },
            {
                key: "actions",
                label: "Actions",
                sortable: false,
                mobile: false,
            },
        ];
    } else {
        // Report type
        return [
            {
                key: "reported_user_name",
                label: "Reported User",
                sortable: true,
                mobile: true,
            },
            { key: "reason", label: "Reason", sortable: false, mobile: true },
            { key: "status", label: "Status", sortable: true, mobile: true },
            { key: "created_at", label: "Date", sortable: true, mobile: true },
            {
                key: "actions",
                label: "Actions",
                sortable: false,
                mobile: false,
            },
        ];
    }
});

const getSortIcon = (field) => {
    if (sortField.value !== field) return "↕";
    return sortDirection.value === "asc" ? "↑" : "↓";
};

const handleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
};

// Computed property for filtered and sorted items
const filteredItems = computed(() => {
    if (!localSearchQuery.value || props.type === "report") {
        // For reports, search is handled server-side
        return props.items;
    }

    const searchLower = localSearchQuery.value.toLowerCase();
    return props.items.filter((item) => {
        if (props.type === "resident") {
            return (
                item.name?.toLowerCase().includes(searchLower) ||
                item.id?.toString().includes(searchLower)
            );
        } else if (props.type === "volunteer") {
            return item.name?.toLowerCase().includes(searchLower);
        }
        return true;
    });
});

// Computed property for sorted items
const sortedItems = computed(() => {
    const items = [...filteredItems.value];

    return items.sort((a, b) => {
        let aValue, bValue;

        switch (sortField.value) {
            case "name":
                aValue = a.name?.toLowerCase() || "";
                bValue = b.name?.toLowerCase() || "";
                break;
            case "reported_user_name":
                aValue = a.reported_user_name?.toLowerCase() || "";
                bValue = b.reported_user_name?.toLowerCase() || "";
                break;
            case "application_date":
                aValue = new Date(a.application_date);
                bValue = new Date(b.application_date);
                break;
            case "created_at":
                aValue = new Date(a.created_at);
                bValue = new Date(b.created_at);
                break;
            case "status":
                aValue = a.status?.toLowerCase() || "";
                bValue = b.status?.toLowerCase() || "";
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

// Pagination computed properties
const totalItems = computed(() => sortedItems.value.length);
const totalPages = computed(() =>
    Math.ceil(totalItems.value / itemsPerPage.value)
);
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);
const endIndex = computed(() =>
    Math.min(startIndex.value + itemsPerPage.value, totalItems.value)
);

const paginatedItems = computed(() => {
    return sortedItems.value.slice(startIndex.value, endIndex.value);
});

// Pagination methods
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

// Action buttons for different types
const getItemActions = (item) => {
    if (props.type === "resident") {
        return [
            {
                label: "View",
                action: "view",
                class: "bg-primary hover:bg-pressed text-white",
            },
            {
                label: "Edit",
                action: "edit",
                class: "bg-primary hover:bg-pressed text-white",
            },
            {
                label: "Delete",
                action: "delete",
                class: "bg-primary hover:bg-red-800 text-white",
            },
        ];
    } else if (props.type === "volunteer") {
        return [
            {
                label: "View",
                action: "view",
                class: "bg-primary hover:bg-pressed text-white",
            },
            {
                label: "Approve",
                action: "approve",
                class: "bg-primary hover:bg-pressed text-white",
            },
            {
                label: "Reject",
                action: "reject",
                class: "bg-primary hover:bg-pressed text-white",
            },
            {
                label: "Delete",
                action: "delete",
                class: "bg-primary hover:bg-red-800 text-white",
            },
        ];
    } else {
        // Report type
        return [
            {
                label: "View",
                action: "view",
                class: "bg-primary hover:bg-pressed text-white",
            },
        ];
    }
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
        case "reviewed":
            return `${baseClasses} bg-blue-100 text-blue-800 border border-blue-200`;
        case "resolved":
            return `${baseClasses} bg-green-100 text-green-800 border border-green-200`;
        case "dismissed":
            return `${baseClasses} bg-gray-100 text-gray-800 border border-gray-200`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800`;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString();
};

const truncateText = (text, maxLength = 50) => {
    if (!text) return "N/A";
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + "...";
};
</script>

<template>
    <div>
        <!-- Search Bar -->
        <div class="mb-4">
            <SearchBar
                v-model="localSearchQuery"
                :placeholder="`Search ${type}s...`"
            />
        </div>

        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-primary">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                            :class="{
                                'cursor-pointer hover:bg-pressed transition-colors':
                                    column.sortable,
                            }"
                            @click="
                                column.sortable ? handleSort(column.key) : null
                            "
                        >
                            <div
                                v-if="column.sortable"
                                class="flex items-center justify-center gap-1"
                            >
                                {{ column.label }}
                                <span class="text-sm">{{
                                    getSortIcon(column.key)
                                }}</span>
                            </div>
                            <span v-else>{{ column.label }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="paginatedItems.length === 0">
                        <td
                            :colspan="columns.length"
                            class="px-6 py-4 text-center text-gray-500"
                        >
                            No {{ type }}s found
                        </td>
                    </tr>
                    <tr
                        v-for="item in paginatedItems"
                        :key="item.id"
                        class="hover:bg-background transition-colors"
                    >
                        <!-- Dynamic columns based on configuration -->
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-4 whitespace-nowrap"
                            :class="{
                                'text-center':
                                    column.key === 'status' ||
                                    column.key === 'actions' ||
                                    column.key === 'pin_code' ||
                                    column.key === 'application_date' ||
                                    column.key === 'created_at',
                                'text-right': column.key === 'actions',
                            }"
                        >
                            <!-- Avatar -->
                            <div
                                v-if="column.key === 'avatar'"
                                class="flex justify-center"
                            >
                                <Avatar
                                    :src="item.profile_photo_url || item.avatar"
                                    :name="item.name"
                                    size="md"
                                    border-color="pressed"
                                    bg-color="primary"
                                    text-color="black"
                                />
                            </div>

                            <!-- Name -->
                            <div
                                v-else-if="column.key === 'name'"
                                class="text-sm font-medium text-black"
                            >
                                {{ item.name }}
                            </div>

                            <!-- ID -->
                            <div
                                v-else-if="column.key === 'id'"
                                class="text-sm text-black font-mono"
                            >
                                {{ item.id }}
                            </div>

                            <!-- Status -->
                            <span
                                v-else-if="column.key === 'status'"
                                :class="getStatusClasses(item.status)"
                            >
                                {{
                                    item.status.charAt(0).toUpperCase() +
                                    item.status.slice(1)
                                }}
                            </span>

                            <!-- Room/Floor for Residents -->
                            <div
                                v-else-if="column.key === 'room_floor'"
                                class="text-sm text-black"
                            >
                                {{ item.room_number || "N/A" }} /
                                {{ item.floor_number || "N/A" }}
                            </div>

                            <!-- PIN Code for Residents -->
                            <button
                                v-else-if="column.key === 'pin_code'"
                                @click="togglePinVisibility(item.id)"
                                class="text-sm font-mono bg-white border-2 border-primary px-3 py-1 rounded hover:bg-pressed hover:text-white transition-colors"
                            >
                                {{
                                    isPinVisible(item.id)
                                        ? item.pin_code
                                        : "••••••"
                                }}
                            </button>

                            <!-- Application Date for Volunteers -->
                            <div
                                v-else-if="column.key === 'application_date'"
                                class="text-sm text-black"
                            >
                                {{ formatDate(item.application_date) }}
                            </div>

                            <!-- Reported User Name for Reports -->
                            <div
                                v-else-if="column.key === 'reported_user_name'"
                                class="text-sm font-medium text-black"
                            >
                                {{ item.reported_user_name || "N/A" }}
                            </div>

                            <!-- Reason for Reports -->
                            <div
                                v-else-if="column.key === 'reason'"
                                class="text-sm text-black max-w-md"
                            >
                                {{ truncateText(item.reason, 50) }}
                            </div>

                            <!-- Created At for Reports -->
                            <div
                                v-else-if="column.key === 'created_at'"
                                class="text-sm text-black"
                            >
                                {{ formatDate(item.created_at) }}
                            </div>

                            <!-- Actions -->
                            <div
                                v-else-if="column.key === 'actions'"
                                class="flex flex-col space-y-1"
                            >
                                <button
                                    v-for="action in getItemActions(item)"
                                    :key="action.action"
                                    @click="emit(action.action, item)"
                                    :class="action.class"
                                    class="px-2 py-1 rounded text-xs font-medium transition-colors shadow-sm"
                                >
                                    {{ action.label }}
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
                v-if="paginatedItems.length === 0"
                class="p-6 text-center text-gray-500"
            >
                No {{ type }}s found
            </div>
            <div v-else class="divide-y divide-gray-200">
                <div
                    v-for="item in paginatedItems"
                    :key="item.id"
                    class="p-4 hover:bg-background transition-colors"
                >
                    <!-- Mobile Card Content -->
                    <div class="flex items-center justify-between">
                        <!-- Left side: Name and optional ID -->
                        <div class="flex items-center space-x-3">
                            <!-- Avatar only for residents -->
                            <div
                                v-if="type === 'resident'"
                                class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center"
                            >
                                <span class="text-gray-600 text-sm font-medium">
                                    {{ item.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-black">
                                    <template v-if="type === 'report'">
                                        {{ item.reported_user_name || "N/A" }}
                                    </template>
                                    <template v-else>
                                        {{ item.name }}
                                    </template>
                                </div>
                                <div
                                    v-if="type === 'resident'"
                                    class="text-xs text-gray-500"
                                >
                                    ID: {{ item.id }}
                                </div>
                                <div
                                    v-if="type === 'report'"
                                    class="text-xs text-gray-500"
                                >
                                    {{ truncateText(item.reason, 30) }}
                                </div>
                            </div>
                        </div>

                        <!-- Right side: Status and Actions -->
                        <div class="flex flex-col items-end space-y-2">
                            <!-- Status for reports -->
                            <span
                                v-if="type === 'report'"
                                :class="getStatusClasses(item.status)"
                            >
                                {{
                                    item.status.charAt(0).toUpperCase() +
                                    item.status.slice(1)
                                }}
                            </span>
                            <!-- Action Buttons -->
                            <div class="flex space-x-1">
                                <button
                                    v-for="action in getItemActions(item)"
                                    :key="action.action"
                                    @click="emit(action.action, item)"
                                    :class="action.class"
                                    class="px-2 py-1 rounded text-xs font-medium transition-colors flex items-center justify-center"
                                    :title="action.label"
                                >
                                    <svg
                                        v-if="action.action === 'view'"
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
                                    <svg
                                        v-else-if="action.action === 'approve'"
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        ></path>
                                    </svg>
                                    <svg
                                        v-else-if="action.action === 'reject'"
                                        class="w-4 h-4"
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
                                    <svg
                                        v-else-if="action.action === 'delete'"
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

        <!-- Pagination -->
        <div class="mt-6 bg-white border-t border-gray-200 px-6 py-4">
            <!-- Simple pagination for mobile -->
            <div class="flex justify-between items-center lg:hidden">
                <button
                    v-if="currentPage > 1"
                    @click="prevPage"
                    class="bg-primary hover:bg-pressed text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm"
                >
                    Previous
                </button>
                <span class="text-sm text-black font-medium">
                    Page {{ currentPage }} of {{ totalPages }}
                </span>
                <button
                    v-if="currentPage < totalPages"
                    @click="nextPage"
                    class="bg-primary hover:bg-pressed text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm"
                >
                    Next
                </button>
            </div>

            <!-- Full pagination for desktop -->
            <div class="hidden lg:flex lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ startIndex + 1 }}</span>
                        to
                        <span class="font-medium">{{ endIndex }}</span>
                        of
                        <span class="font-medium">{{ totalItems }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav
                        class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px"
                    >
                        <!-- Previous button -->
                        <button
                            v-if="currentPage > 1"
                            @click="prevPage"
                            class="relative inline-flex items-center px-3 py-2 rounded-l-lg border-2 border-primary bg-white text-sm font-medium text-primary hover:bg-pressed hover:text-white transition-colors"
                        >
                            <span class="sr-only">Previous</span>
                            <svg
                                class="h-4 w-4"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>

                        <!-- Page numbers -->
                        <template v-for="page in totalPages" :key="page">
                            <button
                                v-if="page === currentPage"
                                @click="goToPage(page)"
                                class="relative inline-flex items-center px-4 py-2 border-2 border-primary bg-primary text-sm font-semibold text-white"
                            >
                                {{ page }}
                            </button>
                            <button
                                v-else
                                @click="goToPage(page)"
                                class="relative inline-flex items-center px-4 py-2 border-2 border-primary bg-white text-sm font-medium text-primary hover:bg-pressed hover:text-white transition-colors"
                            >
                                {{ page }}
                            </button>
                        </template>

                        <!-- Next button -->
                        <button
                            v-if="currentPage < totalPages"
                            @click="nextPage"
                            class="relative inline-flex items-center px-3 py-2 rounded-r-lg border-2 border-primary bg-white text-sm font-medium text-primary hover:bg-pressed hover:text-white transition-colors"
                        >
                            <span class="sr-only">Next</span>
                            <svg
                                class="h-4 w-4"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>
