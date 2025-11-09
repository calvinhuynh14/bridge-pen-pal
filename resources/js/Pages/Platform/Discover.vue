<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

// Get current user from page props
const page = usePage();
const user = computed(() => page.props.auth.user);

// Helper functions to check user type
const isVolunteer = computed(() => user.value?.user_type === "volunteer");
const isResident = computed(() => user.value?.user_type === "resident");

// Get user type display
const getUserType = () => {
    if (isVolunteer.value) return "Volunteer";
    if (isResident.value) return "Resident";
    return "User";
};
</script>

<template>
    <Head :title="`${getUserType()} - Discover`" />

    <AppLayout :title="`${getUserType()} - Discover`">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Discover
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 mb-8"
                >
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white mb-2"
                    >
                        Discover Open Letters
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        This is where you can find open letters to respond to.
                        This feature will be fully implemented at a later time.
                    </p>
                </div>

                <!-- Placeholder Content -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <div class="text-center py-12">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-16 mx-auto text-gray-400 mb-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                            />
                        </svg>
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            Coming Soon
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            The Discover feature is currently under development.
                            You'll be able to browse and respond to open letters
                            here soon!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

