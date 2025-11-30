<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CustomButton from "@/Components/CustomButton.vue";
import SimpleHeader from "@/Components/SimpleHeader.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};

// Helper function to get error message from form errors
const getErrorMessage = () => {
    // Check various error fields and return the first available message
    if (form.errors.email) {
        return Array.isArray(form.errors.email)
            ? form.errors.email[0]
            : form.errors.email;
    }
    if (form.errors.password) {
        return Array.isArray(form.errors.password)
            ? form.errors.password[0]
            : form.errors.password;
    }
    if (form.errors.message) {
        return Array.isArray(form.errors.message)
            ? form.errors.message[0]
            : form.errors.message;
    }
    if (form.errors.error) {
        return Array.isArray(form.errors.error)
            ? form.errors.error[0]
            : form.errors.error;
    }
    // Get first error from any field
    const firstErrorKey = Object.keys(form.errors)[0];
    if (firstErrorKey) {
        const firstError = form.errors[firstErrorKey];
        return Array.isArray(firstError) ? firstError[0] : firstError;
    }
    return "These credentials do not match our records.";
};
</script>

<template>
    <Head title="Admin Login" />

    <SimpleHeader />

    <!-- Main Container -->
    <main
        class="flex flex-col lg:flex-row min-h-screen bg-background items-center justify-center p-2 lg:p-4 gap-4"
        role="main"
    >
        <!-- Hero Section -->
        <section
            class="flex flex-col max-w-7xl bg-background rounded-lg lg:m-8 lg:p-8 lg:gap-8 text-center items-center justify-center"
            aria-label="Admin login hero section"
        >
            <div class="flex-1">
                <h1
                    class="text-primary text-2xl lg:text-4xl xl:text-6xl"
                >
                    Admin Login
                </h1>
                <h2
                    class="text-hover text-lg lg:text-2xl xl:text-4xl mt-2"
                >
                    Access your admin dashboard
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

            <!-- Sign Up Section -->
            <div class="text-center mb-4">
                <p class="text-hover font-bold text-lg">Not an admin?</p>
                <Link 
                    :href="route('register', { type: 'admin' })"
                    aria-label="Sign up as admin"
                >
                    <CustomButton
                        text="Sign Up"
                        preset="neutral"
                        size="small"
                        ariaLabel="Sign up as admin"
                    />
                </Link>
            </div>

            <div class="text-center">
                <a
                    :href="route('auth.google.redirect', { type: 'admin' })"
                    class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 transition-colors lg:hidden"
                    aria-label="Continue with Google"
                >
                    <img
                        src="https://developers.google.com/identity/images/g-logo.png"
                        alt=""
                        aria-hidden="true"
                        class="w-5 h-5 mr-3"
                    />
                    <span class="text-gray-700 font-medium"
                        >Continue with Google</span
                    >
                </a>

                <!-- Divider line with OR - only visible on small screens -->
                <div class="md:hidden mt-4">
                    <div class="relative flex items-center">
                        <div class="flex-grow h-0.5 bg-primary"></div>
                        <span 
                            class="px-3 text-primary text-sm bg-background"
                            aria-hidden="true"
                            >OR</span
                        >
                        <div class="flex-grow h-0.5 bg-primary"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Login Form Section -->
        <section
            class="flex flex-col mx-2 lg:mx-8 lg:bg-primary lg:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
            aria-label="Admin login form section"
        >
            <div class="flex-1 mb-8 text-center">
                <h1
                    class="text-primary lg:text-white text-2xl lg:text-4xl xl:text-6xl"
                >
                    Log in to Bridge
                </h1>
            </div>
            <!-- Login Form -->
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

                <!-- Error Message -->
                <div
                    v-if="Object.keys(form.errors).length > 0"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                    class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg"
                >
                    <p class="text-sm text-red-600 font-medium">
                        {{ getErrorMessage() }}
                    </p>
                </div>

                <form @submit.prevent="submit" aria-label="Admin login form">
                    <div class="space-y-4">
                        <!-- Email Field -->
                        <div>
                            <InputLabel
                                for="email"
                                value="Email"
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="form.errors.email ? `email-error` : null"
                                autofocus
                                autocomplete="email"
                                placeholder="Enter your email"
                            />
                            <InputError
                                id="email-error"
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <!-- Password Field -->
                        <div>
                            <InputLabel
                                for="password"
                                value="Password"
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="form.errors.password ? `password-error` : null"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            />
                            <InputError
                                id="password-error"
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <!-- Remember Me Checkbox -->
                        <div class="flex items-center">
                            <Checkbox
                                id="remember"
                                v-model:checked="form.remember"
                                name="remember"
                            />
                            <label
                                for="remember"
                                class="ml-2 text-base font-medium text-primary lg:text-white"
                            >
                                Remember me
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <CustomButton
                            type="submit"
                            text="Sign In"
                            preset="neutral"
                            size="medium"
                            :isLoading="form.processing"
                            ariaLabel="Sign in to your admin account"
                            class="w-full"
                        />

                        <!-- Forgot Password Link -->
                        <div class="mt-2 text-center">
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-primary hover:text-pressed text-lg lg:text-white lg:hover:text-white"
                                aria-label="Reset your password"
                            >
                                Forgot your password?
                            </Link>
                        </div>
                    </div>
                </form>

                <div class="hidden lg:block text-center space-y-4 mt-4">
                    <!-- Divider line with OR -->
                    <div class="relative flex items-center">
                        <div class="flex-grow h-0.5 bg-black"></div>
                        <span 
                            class="px-3 text-black text-md"
                            aria-hidden="true"
                            >OR</span
                        >
                        <div class="flex-grow h-0.5 bg-black"></div>
                    </div>

                    <a
                        :href="route('auth.google.redirect', { type: 'admin' })"
                        class="w-full flex items-center justify-center px-4 py-3 border border-black rounded-lg bg-white hover:bg-gray-50 transition-colors"
                        aria-label="Continue with Google"
                    >
                        <img
                            src="https://developers.google.com/identity/images/g-logo.png"
                            alt=""
                            aria-hidden="true"
                            class="w-5 h-5 mr-3"
                        />
                        <span class="text-gray-700 font-medium"
                            >Continue with Google</span
                        >
                    </a>
                </div>
            </div>
        </section>
    </main>
</template>
