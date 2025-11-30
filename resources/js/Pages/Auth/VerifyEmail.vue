<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import CustomButton from "@/Components/CustomButton.vue";
import SimpleHeader from "@/Components/SimpleHeader.vue";

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <Head title="Email Verification" />

    <!-- Header -->
    <SimpleHeader />

    <!-- Main Container -->
    <main
        class="flex flex-col lg:flex-row min-h-screen bg-background items-center justify-center p-2 lg:p-4 gap-4"
        role="main"
    >
        <!-- Hero Section -->
        <section
            class="flex flex-col max-w-7xl bg-background rounded-lg lg:m-8 lg:p-8 lg:gap-8 text-center items-center justify-center"
            aria-label="Email verification hero section"
        >
            <div class="flex-1">
                <h1 class="text-primary text-2xl lg:text-4xl xl:text-6xl">
                    Verify Your Email
                </h1>
                <h2 class="text-hover text-lg lg:text-2xl xl:text-4xl mt-2">
                    Check your inbox
                </h2>
            </div>

            <div
                class="flex lg:flex items-center justify-center w-2/3 lg:w-1/2"
            >
                <img
                    src="/images/logos/logo-with-name-purple.svg"
                    alt=""
                    aria-hidden="true"
                    class="w-full h-auto object-contain max-w-[280px] lg:max-w-none lg:w-full"
                />
            </div>
        </section>

        <!-- Verification Section -->
        <section
            class="flex flex-col mx-2 lg:mx-8 lg:bg-primary lg:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
            aria-label="Email verification form section"
        >
            <!-- Verification Content -->
            <div class="rounded-lg px-4 lg:px-8 max-w-md mx-auto space-y-6">
                <!-- Instructions -->
                <div class="text-center">
                    <h2
                        class="text-2xl lg:text-4xl xl:text-6xl font-bold text-primary lg:text-white mb-4"
                    >
                        Verify Your Email Address
                    </h2>

                    <p
                        class="text-primary lg:text-white text-base lg:text-lg font-medium mb-6"
                    >
                        Before continuing, please verify your email address by
                        clicking on the link we just emailed to you. If you
                        didn't receive the email, we can send you another.
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="verificationLinkSent"
                    role="status"
                    aria-live="polite"
                    aria-atomic="true"
                    class="mb-4 p-3 bg-green-50 lg:bg-green-100 border border-green-200 rounded-lg"
                >
                    <div class="flex items-center">
                        <svg
                            class="h-5 w-5 text-green-600 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="text-green-800 font-medium text-sm">
                            A new verification link has been sent to your email
                            address.
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <form
                    @submit.prevent="submit"
                    aria-label="Email verification form"
                >
                    <div class="space-y-4">
                        <CustomButton
                            type="submit"
                            text="Resend Verification Email"
                            preset="neutral"
                            size="medium"
                            class="w-full"
                            :isLoading="form.processing"
                            ariaLabel="Resend verification email"
                        />

                        <!-- Additional Actions -->
                        <div class="flex flex-col gap-2">
                            <Link
                                :href="route('application.submitted')"
                                aria-label="Go back to application status"
                            >
                                <CustomButton
                                    text="Back to Application Status"
                                    preset="neutral"
                                    size="small"
                                    ariaLabel="Go back to application status"
                                    class="w-full"
                                />
                            </Link>

                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                aria-label="Log out"
                            >
                                <CustomButton
                                    text="Log Out"
                                    preset="neutral"
                                    size="small"
                                    ariaLabel="Log out"
                                    class="w-full"
                                />
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>
</template>
