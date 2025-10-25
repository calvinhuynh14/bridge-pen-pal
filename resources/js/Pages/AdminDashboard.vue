<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import CustomButton from "@/Components/CustomButton.vue";
import OrganizationSetupModal from "@/Components/OrganizationSetupModal.vue";
import { ref, onMounted, computed } from "vue";

const props = defineProps({
    needsOrganizationSetup: {
        type: Boolean,
        default: false,
    },
    volunteerApplications: {
        type: Array,
        default: () => [],
    },
    totalResidents: {
        type: Number,
        default: 0,
    },
});

const showModal = ref(false);

onMounted(() => {
    // Show modal if organization setup is needed
    if (props.needsOrganizationSetup) {
        showModal.value = true;
    }
});

const handleModalClose = () => {
    showModal.value = false;
};

const handleModalSuccess = () => {
    // Close the modal and update the needsOrganizationSetup state
    showModal.value = false;
    // The page will automatically update the needsOrganizationSetup prop on next navigation
};

// Computed properties for statistics
const totalResidents = computed(() => {
    return props.totalResidents || 0;
});

const approvedVolunteers = computed(() => {
    return props.volunteerApplications.filter(
        (app) => app.status === "approved"
    ).length;
});

const pendingVolunteers = computed(() => {
    return props.volunteerApplications.filter((app) => app.status === "pending")
        .length;
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout title="Admin Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Platform Statistics -->
                <div
                    class="bg-primary overflow-hidden shadow-lg rounded-lg p-6 mb-6 border-2 border-primary"
                >
                    <h3
                        class="text-lg font-semibold text-white mb-4 text-center"
                    >
                        Platform Statistics
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div
                            class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                        >
                            <div class="text-3xl font-bold text-white mb-2">
                                {{ totalResidents }}
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
                                {{ approvedVolunteers }}
                            </div>
                            <div
                                class="text-white sm:text-sm lg:text-lg font-medium"
                            >
                                Active Volunteers
                            </div>
                        </div>
                        <div
                            class="text-center bg-white bg-opacity-20 rounded-lg p-4"
                        >
                            <div class="text-3xl font-bold text-accent mb-2">
                                {{ pendingVolunteers }}
                            </div>
                            <div
                                class="text-white sm:text-sm lg:text-lg font-medium"
                            >
                                Pending Approvals
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organization Setup Modal -->
        <OrganizationSetupModal
            :show="showModal"
            @close="handleModalClose"
            @success="handleModalSuccess"
        />
    </AppLayout>
</template>
