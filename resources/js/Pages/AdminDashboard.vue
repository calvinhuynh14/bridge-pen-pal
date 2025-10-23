<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import CustomButton from "@/Components/CustomButton.vue";
import OrganizationSetupModal from "@/Components/OrganizationSetupModal.vue";
import { ref, onMounted } from "vue";

const props = defineProps({
    needsOrganizationSetup: {
        type: Boolean,
        default: false,
    },
    volunteerApplications: {
        type: Array,
        default: () => [],
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
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout title="Admin Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto">
                <!-- Platform Statistics -->
                <div
                    class="bg-primary overflow-hidden shadow-lg rounded-lg p-6"
                >
                    <h3
                        class="text-lg font-semibold text-white mb-6 text-center"
                    >
                        Platform Statistics
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-hover mb-2">
                                0
                            </div>
                            <div class="text-white text-sm">
                                Total Residents
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-hover mb-2">
                                {{
                                    volunteerApplications.filter(
                                        (app) => app.status === "approved"
                                    ).length
                                }}
                            </div>
                            <div class="text-white text-sm">
                                Active Volunteers
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-hover mb-2">
                                {{
                                    volunteerApplications.filter(
                                        (app) => app.status === "pending"
                                    ).length
                                }}
                            </div>
                            <div class="text-white text-sm">
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
