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
        <div class="py-12 bg-background min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Admin Management Cards -->
                <div
                    class="grid grid-cols-1 m-4 items-center justify-center md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <!-- Manage Residents Card -->
                    <div
                        class="bg-primary overflow-hidden shadow-lg rounded-lg p-6"
                    >
                        <h3 class="text-lg font-semibold text-hover">
                            Manage Residents
                        </h3>
                        <p class="text-white font-bold mb-4">
                            Create, edit, and manage resident accounts
                        </p>
                        <Link :href="route('admin.residents')">
                            <CustomButton
                                text="Manage Residents"
                                preset="secondary"
                                size="medium"
                            />
                        </Link>
                    </div>

                    <!-- Manage Volunteers Card -->
                    <div
                        class="bg-primary overflow-hidden shadow-lg rounded-lg p-6"
                    >
                        <h3 class="text-lg font-semibold text-hover">
                            Manage Volunteers
                        </h3>
                        <p class="text-white font-bold mb-4">
                            Approve, deny, and manage volunteer applications
                        </p>
                        <CustomButton
                            text="Manage Volunteers"
                            preset="secondary"
                            size="medium"
                        />
                    </div>

                    <!-- Reports & Moderation Card -->
                    <div
                        class="bg-primary overflow-hidden shadow-lg rounded-lg p-6"
                    >
                        <h3 class="text-lg font-semibold text-hover">
                            Reports & Moderation
                        </h3>
                        <p class="text-white font-bold mb-4">
                            Review reports and moderate platform content
                        </p>
                        <CustomButton
                            text="View Reports"
                            preset="secondary"
                            size="medium"
                        />
                    </div>
                </div>

                <!-- Quick Stats -->
                <div
                    class="bg-primary overflow-hidden shadow-lg rounded-lg p-6 m-4"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Platform Statistics
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-hover">0</div>
                            <div class="text-white">Total Residents</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-hover">0</div>
                            <div class="text-white">Active Volunteers</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-hover">0</div>
                            <div class="text-white">Pending Approvals</div>
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
