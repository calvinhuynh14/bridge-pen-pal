<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Resident Management" />

    <AppLayout title="Resident Management">
        <div class="py-12 bg-background min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg p-6"
                >
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white mb-6"
                    >
                        Resident Management
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        This is where you'll manage resident accounts, create
                        new residents, and handle resident-related tasks.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
