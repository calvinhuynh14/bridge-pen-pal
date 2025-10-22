<script setup>
import { Head, Link } from "@inertiajs/vue3";
import CustomButton from "@/Components/CustomButton.vue";
</script>

<template>
    <Head title="Application Submitted" />

    <!-- Main Container -->
    <div
        class="flex flex-col md:flex-row lg:flex-row min-h-screen bg-background items-center justify-center p-4 gap-4"
    >
        <!-- Hero Section -->
        <section
            class="flex flex-col max-w-7xl bg-background rounded-lg lg:m-8 lg:p-8 lg:gap-8 text-center items-center justify-center"
        >
            <div class="flex-1">
                <h1
                    class="hidden lg:block text-primary text-2xl lg:text-4xl xl:text-6xl"
                >
                    Application Submitted!
                </h1>
                <h2
                    class="hidden lg:block text-hover text-lg lg:text-2xl xl:text-4xl"
                >
                    Thank you for your interest
                </h2>
            </div>

            <div
                class="flex items-center justify-center w-3/4 md:w-3/5 lg:w-1/2"
            >
                <img
                    src="/images/logos/logo-with-name-purple.svg"
                    alt="Bridge Logo"
                    class="w-full h-full object-contain md:w-1/2 lg:w-full"
                />
            </div>

            <!-- Success Icon for mobile -->
            <div
                class="lg:hidden mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4"
            >
                <svg
                    class="h-8 w-8 text-green-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    ></path>
                </svg>
            </div>
        </section>

        <!-- Application Status Section -->
        <section
            class="flex flex-col mx-8 lg:bg-primary md:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
        >
            <div class="flex-1 mb-8">
                <h1
                    class="hidden lg:block text-white text-2xl lg:text-4xl xl:text-6xl"
                >
                    Application Status
                </h1>
            </div>

            <!-- Application Status Content -->
            <div class="rounded-lg px-8 max-w-md mx-auto space-y-6">
                <!-- Success Message -->
                <div class="text-center">
                    <h2
                        class="text-2xl font-bold text-gray-900 lg:text-white mb-4"
                    >
                        Application Submitted Successfully!
                    </h2>

                    <p class="text-gray-600 lg:text-hover mb-6">
                        Thank you for your interest in becoming a pen pal
                        volunteer. Your application has been submitted and is
                        currently under review.
                    </p>
                </div>

                <!-- Application Status -->
                <div
                    class="bg-yellow-50 lg:bg-yellow-100 border border-yellow-200 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <svg
                            class="h-5 w-5 text-yellow-600 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <span class="text-yellow-800 font-medium"
                            >Status: Pending Review</span
                        >
                    </div>
                </div>

                <!-- What's Next Section -->
                <div
                    class="bg-blue-50 lg:bg-blue-100 border border-blue-200 rounded-lg p-4 text-left"
                >
                    <h3 class="font-semibold text-blue-900 mb-2">
                        What happens next?
                    </h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>
                            • Your application will be reviewed by the
                            organization
                        </li>
                        <li>
                            • You'll receive an email notification once a
                            decision is made
                        </li>
                        <li>
                            • If approved, you'll be able to log in and start
                            your pen pal journey
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div>
                    <Link href="/" class="block mb-6">
                        <CustomButton
                            text="Return to Home"
                            preset="secondary"
                            size="medium"
                            class="w-full"
                        />
                    </Link>

                    <Link href="/login?type=volunteer" class="block">
                        <CustomButton
                            text="Already have an account? Sign in"
                            preset="neutral"
                            size="small"
                            class="w-full"
                        />
                    </Link>
                </div>
            </div>
        </section>
    </div>
</template>
