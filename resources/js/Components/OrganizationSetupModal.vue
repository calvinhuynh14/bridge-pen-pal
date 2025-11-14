<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-2 sm:p-4"
    >
        <!-- Modal panel -->
        <div
            class="relative bg-white rounded-lg shadow-2xl w-full max-w-lg overflow-hidden"
            @click.stop
        >
            <form @submit.prevent="submitForm">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <!-- Icon -->
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary sm:mx-0 sm:h-10 sm:w-10"
                        >
                            <svg
                                class="h-6 w-6 text-pressed"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                        </div>

                        <!-- Content -->
                        <div
                            class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full"
                        >
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900"
                            >
                                Complete Your Admin Setup
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    To complete your admin account setup, please
                                    provide your organization name.
                                </p>
                            </div>
                            <div class="mt-4">
                                <label
                                    for="organization_name"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Organization Name
                                </label>
                                <input
                                    id="organization_name"
                                    v-model="form.organization_name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full bg-white rounded-lg border-2 border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 px-3 py-2 text-sm text-gray-900 focus:outline-none transition-colors"
                                    placeholder="Enter your organization name"
                                />
                                <div
                                    v-if="form.errors.organization_name"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.organization_name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer with button and helpful caption -->
                <div
                    class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-col sm:gap-3"
                >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary hover:bg-pressed text-base font-medium text-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:w-auto sm:ml-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Organization</span>
                    </button>

                    <!-- Helpful caption with question icon -->
                    <div
                        class="flex items-start gap-2 text-xs text-gray-600 bg-gray-100 rounded-lg p-3"
                    >
                        <svg
                            class="w-4 h-4 text-primary flex-shrink-0 mt-0.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"
                            />
                        </svg>
                        <p>
                            You must complete this setup before accessing other
                            admin features. This ensures your organization is
                            properly configured.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "success"]);

const form = useForm({
    organization_name: "",
});

const submitForm = () => {
    form.post("/organization", {
        onSuccess: () => {
            emit("success");
        },
        onError: (errors) => {
            console.error("Organization creation failed:", errors);
        },
    });
};
</script>
