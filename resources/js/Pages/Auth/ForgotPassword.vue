<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import CustomButton from "@/Components/CustomButton.vue";
import SimpleHeader from "@/Components/SimpleHeader.vue";

defineProps({
    status: String,
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <Head title="Forgot Password" />

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
            aria-label="Forgot password hero section"
        >
            <div class="flex-1">
                <h1
                    class="text-primary text-2xl lg:text-4xl xl:text-6xl font-bold"
                >
                    Forgot Password?
                </h1>
                <h2 class="text-hover text-lg lg:text-2xl xl:text-4xl mt-2">
                    No problem, we'll help you reset it
                </h2>
            </div>

            <div
                class="flex lg:flex items-center justify-center w-2/3 lg:w-1/2"
                aria-hidden="true"
            >
                <img
                    src="/images/logos/logo-with-name-purple.svg"
                    alt=""
                    class="w-full h-auto object-contain max-w-[280px] lg:max-w-none lg:w-full"
                />
            </div>
        </section>

        <!-- Password Reset Form Section -->
        <section
            class="flex flex-col mx-2 lg:mx-8 lg:bg-primary lg:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
            aria-label="Password reset form section"
        >
            <!-- Password Reset Form -->
            <div class="rounded-lg px-4 lg:px-4 max-w-md mx-auto space-y-4">
                <!-- Success Message -->
                <div
                    v-if="status"
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
                            {{ status }}
                        </span>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mb-4 text-center">
                    <p
                        class="text-primary lg:text-white text-base lg:text-lg font-medium"
                    >
                        Forgot your password? No problem. Just let us know your
                        email address and we will email you a password reset
                        link that will allow you to choose a new one.
                    </p>
                </div>

                <form @submit.prevent="submit" aria-label="Password reset form">
                    <div class="space-y-4">
                        <!-- Email Field -->
                        <div>
                            <InputLabel
                                for="email"
                                value="Email Address"
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-2 block w-full max-w-md mx-auto border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="
                                    form.errors.email ? `email-error` : null
                                "
                                autofocus
                                autocomplete="email"
                                placeholder="Enter your email address"
                            />
                            <InputError
                                id="email-error"
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <CustomButton
                            text="Email Password Reset Link"
                            preset="neutral"
                            size="medium"
                            :isLoading="form.processing"
                            ariaLabel="Send password reset link"
                            type="submit"
                            class="w-full"
                        />

                        <!-- Back to Login Button -->
                        <div class="mt-4 flex justify-center">
                            <Link
                                :href="route('login')"
                                aria-label="Go back to login page"
                            >
                                <CustomButton
                                    text="Back to Login"
                                    preset="neutral"
                                    size="small"
                                    ariaLabel="Go back to login page"
                                />
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>
</template>
