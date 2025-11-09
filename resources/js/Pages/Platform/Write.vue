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
    <Head :title="`${getUserType()} - Write`" />

    <AppLayout :title="`${getUserType()} - Write`">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Write
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
                        Write a Letter
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Respond to your pen pal or write a new letter addressed
                        to someone. This feature will be fully implemented at a
                        later time.
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
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18.14 4.487l-1.687 1.688a1.875 1.875 0 0 1-2.652 2.652L4.16 16.07a4.5 4.5 0 0 0-1.897 1.13L2 18l.8-2.685a4.5 4.5 0 0 0 1.13-1.897l8.932-8.931Z"
                            />
                        </svg>
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            Coming Soon
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            The Write feature is currently under development.
                            You'll be able to compose and send letters here
                            soon!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

