<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import CustomButton from "@/Components/CustomButton.vue";
import OrganizationSetupModal from "@/Components/OrganizationSetupModal.vue";
import FeaturedStoryManagement from "@/Components/FeaturedStoryManagement.vue";
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
    isEmailVerified: {
        type: Boolean,
        default: true,
    },
    featuredStory: {
        type: Object,
        default: null,
    },
});

const showModal = ref(false);
const verificationLinkSent = ref(false);

const verificationForm = useForm({});

const sendVerificationEmail = () => {
    verificationForm.post(route('verification.send'), {
        onSuccess: () => {
            verificationLinkSent.value = true;
            // Hide the success message after 5 seconds
            setTimeout(() => {
                verificationLinkSent.value = false;
            }, 5000);
        },
    });
};

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
                <!-- Email Verification Notice -->
                <div
                    v-if="!isEmailVerified"
                    class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-lg shadow-sm"
                >
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-yellow-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <!-- Success Message -->
                            <div
                                v-if="verificationLinkSent"
                                class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="h-5 w-5 text-green-600 mr-2"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <span class="text-sm text-green-800 font-medium">
                                        Verification email sent! Please check your inbox.
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        Verify Your Email Address
                                    </h3>
                                    <div class="mt-1 text-sm text-yellow-700">
                                        <p>
                                            Please verify your email address to ensure
                                            you can recover your account if you forget
                                            your password. Check your inbox for a
                                            verification link.
                                        </p>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <button
                                        type="button"
                                        @click="sendVerificationEmail"
                                        :disabled="verificationForm.processing"
                                        class="inline-flex items-center px-4 py-2 border border-yellow-400 text-sm font-medium rounded-lg text-yellow-800 bg-white hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{
                                            verificationForm.processing
                                                ? "Sending..."
                                                : "Send Verification Email"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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

                <!-- Featured Story Management -->
                <FeaturedStoryManagement
                    v-if="!needsOrganizationSetup"
                    :featuredStory="featuredStory"
                />
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
