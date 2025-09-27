<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

// Get current user from page props
const page = usePage();
const user = computed(() => page.props.auth.user);

// Helper functions to check user type
const isVolunteer = computed(() => user.value?.user_type === "volunteer");
const isResident = computed(() => user.value?.user_type === "resident");

// Get welcome message based on user type
const getWelcomeMessage = () => {
    if (isVolunteer.value)
        return `Welcome back, ${user.value.name}! Ready to help your community?`;
    if (isResident.value)
        return `Welcome back, ${user.value.name}! Stay connected with your community.`;
    return "Welcome to Bridge!";
};

// Get user type display
const getUserType = () => {
    if (isVolunteer.value) return "Volunteer";
    if (isResident.value) return "Resident";
    return "User";
};
</script>

<template>
    <Head :title="`${getUserType()} Home`" />

    <AppLayout :title="`${getUserType()} Home`">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                {{ getUserType() }} Home
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 mb-8"
                >
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white mb-2"
                    >
                        {{ getWelcomeMessage() }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        This is your main platform home page where you can
                        access all features.
                    </p>
                </div>

                <!-- Main Platform Features -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8"
                >
                    <!-- Profile Settings Card -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Profile Settings
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Update your profile information and preferences
                        </p>
                        <Link
                            :href="route('profile.show')"
                            class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-pressed transition-colors inline-block"
                        >
                            View Profile
                        </Link>
                    </div>

                    <!-- Volunteer-specific features -->
                    <div
                        v-if="isVolunteer"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Volunteer Opportunities
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Find and join volunteer opportunities in your
                            community
                        </p>
                        <button
                            class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-pressed transition-colors"
                        >
                            Browse Opportunities
                        </button>
                    </div>

                    <!-- Resident-specific features -->
                    <div
                        v-if="isResident"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Community Updates
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Stay updated with your community news and events
                        </p>
                        <button
                            class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-pressed transition-colors"
                        >
                            View Updates
                        </button>
                    </div>

                    <!-- Messages/Communication -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Messages
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Connect with other community members
                        </p>
                        <button
                            class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-pressed transition-colors"
                        >
                            View Messages
                        </button>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Recent Activity
                    </h3>
                    <div class="text-gray-600 dark:text-gray-400">
                        <p>No recent activity to display.</p>
                        <p class="text-sm mt-2">
                            Start exploring the platform to see your activity
                            here!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
