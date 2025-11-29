<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import CustomButton from '@/Components/CustomButton.vue';
import SimpleHeader from '@/Components/SimpleHeader.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

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
                    Forgot Password?
                </h1>
                <h2
                    class="hidden lg:block text-hover text-lg lg:text-2xl xl:text-4xl"
                >
                    No problem, we'll help you reset it
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

        <!-- Password Reset Form Section -->
        <section
            class="flex flex-col mx-2 lg:mx-8 lg:bg-primary lg:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
        >
            <div class="flex-1 mb-8 text-center">
                <h1
                    class="hidden lg:block text-white text-2xl lg:text-4xl xl:text-6xl"
                >
                    Reset Password
                </h1>
            </div>

            <!-- Password Reset Form -->
            <div class="rounded-lg px-4 lg:px-4 max-w-md mx-auto space-y-4">
                <!-- "Reset Password" heading for mobile -->
                <div class="flex lg:hidden mb-2 text-center">
                    <h1 class="text-primary text-2xl font-bold w-full">
                        Reset Password
                    </h1>
        </div>

                <!-- Success Message -->
                <div
                    v-if="status"
                    class="mb-4 p-3 bg-green-50 lg:bg-green-100 border border-green-200 rounded-lg"
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
                        <span class="text-green-800 font-medium text-sm">
            {{ status }}
                        </span>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mb-4 text-center">
                    <p class="text-gray-600 lg:text-black text-base lg:text-lg">
                        Forgot your password? No problem. Just let us know your
                        email address and we will email you a password reset link
                        that will allow you to choose a new one.
                    </p>
                </div>

                <!-- Error Message -->
                <div
                    v-if="Object.keys(form.errors).length > 0"
                    class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg"
                >
                    <p class="text-sm text-red-600 font-medium">
                        {{ form.errors.email || 'Please check your email address.' }}
                    </p>
        </div>

        <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <!-- Email Field -->
            <div>
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                    required
                    autofocus
                                autocomplete="email"
                                placeholder="Enter your email address"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-primary lg:bg-background text-white hover:text-white lg:text-hover lg:border-hover lg:border-2 px-8 py-4 rounded-lg text-lg font-medium hover:bg-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{
                                form.processing
                                    ? 'Sending...'
                                    : 'Email Password Reset Link'
                            }}
                        </button>

                        <!-- Back to Login Button -->
                        <div class="mt-4">
                            <Link :href="route('login')">
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
                                    <span>Back to Login</span>
                                </button>
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</template>
