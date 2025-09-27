<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

// Get current user from page props
const page = usePage();
const user = computed(() => page.props.auth.user);

// Helper functions to check user type
const isAdmin = computed(() => user.value?.user_type === "admin");
const isVolunteer = computed(() => user.value?.user_type === "volunteer");
const isResident = computed(() => user.value?.user_type === "resident");

// Get profile title based on user type
const getProfileTitle = () => {
    if (isAdmin.value) return "Admin Profile Settings";
    if (isVolunteer.value) return "Volunteer Profile Settings";
    if (isResident.value) return "Resident Profile Settings";
    return "Profile Settings";
};
</script>

<template>
    <Head :title="getProfileTitle()" />

    <AppLayout :title="getProfileTitle()">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                {{ getProfileTitle() }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Profile Information Card -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 mb-8"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Profile Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Info -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Name
                            </label>
                            <input
                                type="text"
                                :value="user.name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                readonly
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Email
                            </label>
                            <input
                                type="email"
                                :value="user.email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                readonly
                            />
                        </div>

                        <!-- User Type Specific Fields -->
                        <div v-if="isVolunteer">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Volunteer Status
                            </label>
                            <input
                                type="text"
                                value="Active Volunteer"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                readonly
                            />
                        </div>

                        <div v-if="isResident">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Resident ID
                            </label>
                            <input
                                type="text"
                                value="R-001"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                readonly
                            />
                        </div>

                        <div v-if="isAdmin">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Organization
                            </label>
                            <input
                                type="text"
                                :value="user.name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                readonly
                            />
                        </div>
                    </div>

                    <div class="mt-6">
                        <button
                            class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-pressed transition-colors"
                        >
                            Edit Profile
                        </button>
                    </div>
                </div>

                <!-- Account Settings Card -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 mb-8"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Account Settings
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    Change Password
                                </h4>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    Update your account password
                                </p>
                            </div>
                            <button
                                class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-pressed transition-colors"
                            >
                                Change Password
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <h4
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    Two-Factor Authentication
                                </h4>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    Add an extra layer of security to your
                                    account
                                </p>
                            </div>
                            <button
                                class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-pressed transition-colors"
                            >
                                Enable 2FA
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <h4
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    Delete Account
                                </h4>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    Permanently delete your account and all data
                                </p>
                            </div>
                            <button
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors"
                            >
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User Type Specific Settings -->
                <div
                    v-if="isVolunteer"
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Volunteer Preferences
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Availability
                            </label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                            >
                                <option>Weekdays</option>
                                <option>Weekends</option>
                                <option>Both</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Preferred Activities
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-primary focus:ring-primary"
                                    />
                                    <span
                                        class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                        >Community Events</span
                                    >
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-primary focus:ring-primary"
                                    />
                                    <span
                                        class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                        >Administrative Tasks</span
                                    >
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-primary focus:ring-primary"
                                    />
                                    <span
                                        class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                        >Direct Resident Support</span
                                    >
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="isResident"
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Resident Preferences
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Communication Preferences
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-primary focus:ring-primary"
                                        checked
                                    />
                                    <span
                                        class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                        >Email Notifications</span
                                    >
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-primary focus:ring-primary"
                                    />
                                    <span
                                        class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                        >SMS Notifications</span
                                    >
                                </label>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Privacy Settings
                            </label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                            >
                                <option>Public</option>
                                <option>Friends Only</option>
                                <option>Private</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
