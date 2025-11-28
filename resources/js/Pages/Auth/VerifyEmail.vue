<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import CustomButton from '@/Components/CustomButton.vue';
import SimpleHeader from '@/Components/SimpleHeader.vue';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Email Verification" />

    <!-- Header -->
    <SimpleHeader />

    <!-- Main Container -->
    <div
        class="flex flex-col lg:flex-row min-h-screen bg-background items-center justify-center p-2 lg:p-4 gap-4"
    >
        <!-- Hero Section -->
        <section
            class="flex flex-col max-w-7xl bg-background rounded-lg lg:m-8 lg:p-8 lg:gap-8 text-center items-center justify-center"
        >
            <div class="flex-1">
                <h1
                    class="hidden lg:block text-primary text-2xl lg:text-4xl xl:text-6xl"
                >
                    Verify Your Email
                </h1>
                <h2
                    class="hidden lg:block text-hover text-lg lg:text-2xl xl:text-4xl"
                >
                    Check your inbox
                </h2>
        </div>

            <div
                class="flex lg:flex items-center justify-center w-2/3 lg:w-1/2"
            >
                <img
                    src="/images/logos/logo-with-name-purple.svg"
                    alt="Bridge Logo"
                    class="w-full h-auto object-contain max-w-[280px] lg:max-w-none lg:w-full"
                />
        </div>
        </section>

        <!-- Verification Section -->
        <section
            class="flex flex-col mx-2 lg:mx-8 lg:bg-primary lg:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
        >
            <!-- Verification Content -->
            <div class="rounded-lg px-4 lg:px-8 max-w-md mx-auto space-y-6">
                <!-- Instructions -->
                <div class="text-center">
                    <h2
                        class="text-2xl font-bold text-gray-900 lg:text-black mb-4"
                    >
                        Verify Your Email Address
                    </h2>

                    <p class="text-gray-600 lg:text-black mb-6">
                        Before continuing, please verify your email address by
                        clicking on the link we just emailed to you. If you
                        didn't receive the email, we can send you another.
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="verificationLinkSent"
                    class="bg-green-50 lg:bg-green-100 border border-green-200 rounded-lg p-4"
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
                        <span class="text-green-800 font-medium">
                            A new verification link has been sent to your email
                            address.
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <CustomButton
                            type="submit"
                            text="Resend Verification Email"
                            preset="neutral"
                            size="medium"
                            class="w-full"
                            :disabled="form.processing"
                        />

                        <!-- Additional Actions -->
                        <div class="flex flex-col gap-2">
                            <Link :href="route('application.submitted')">
                                <button
                                    type="button"
                                    class="w-full bg-white text-pressed hover:bg-hover hover:text-white border-4 border-primary rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 px-3 py-1.5 text-xs md:px-4 md:py-2 md:text-sm flex items-center justify-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-4"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M11.03 3.97a.75.75 0 0 1 0 1.06l-6.22 6.22H21a.75.75 0 0 1 0 1.5H4.81l6.22 6.22a.75.75 0 1 1-1.06 1.06l-7.5-7.5a.75.75 0 0 1 0-1.06l7.5-7.5a.75.75 0 0 1 1.06 0Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <span>Back to Application Status</span>
                                </button>
                            </Link>

                            <Link :href="route('logout')" method="post" as="button">
                                <button
                                    type="button"
                                    class="w-full bg-white text-pressed hover:bg-hover hover:text-white border-4 border-primary rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 px-3 py-1.5 text-xs md:px-4 md:py-2 md:text-sm flex items-center justify-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-4"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M7.5 3.75A1.5 1.5 0 0 0 6 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5h6a1.5 1.5 0 0 0 1.5-1.5V15a.75.75 0 0 1 1.5 0v3.75a3 3 0 0 1-3 3h-6a3 3 0 0 1-3-3V5.25a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3V9A.75.75 0 0 1 15 9V5.25a1.5 1.5 0 0 0-1.5-1.5h-6Zm10.72 4.72a.75.75 0 0 1 1.06 0l3 3a.75.75 0 0 1 0 1.06l-3 3a.75.75 0 1 1-1.06-1.06l1.72-1.72H9a.75.75 0 0 1 0-1.5h10.94l-1.72-1.72a.75.75 0 0 1 0-1.06Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <span>Log Out</span>
                                </button>
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</template>
