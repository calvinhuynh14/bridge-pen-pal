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

// Get user type from query parameter, default to 'volunteer'
const props = defineProps({
    canResetPassword: Boolean,
    status: String,
    type: {
        type: String,
        default: "volunteer",
    },
});

const form = useForm({
    email: "",
    username: "",
    password: "",
    remember: false,
});

const submit = () => {
    // For residents, send username as 'email' field (Fortify expects 'email')
    // For others, use email normally
    const loginData =
        props.type === "resident"
            ? {
                  email: form.username, // Send username as email for Fortify compatibility
                  password: form.password,
                  remember: form.remember ? "on" : "",
              }
            : {
                  email: form.email,
                  password: form.password,
                  remember: form.remember ? "on" : "",
              };

    form.transform((data) => loginData).post(route("login"), {
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

// Get display text based on user type
const getUserTypeDisplay = (type) => {
    const types = {
        volunteer: "Volunteer",
        admin: "Admin",
        resident: "Resident",
    };
    return types[type] || "User";
};

const getTitle = (type) => {
    const titles = {
        volunteer: "Volunteer Login",
        admin: "Admin Login",
        resident: "Resident Login",
    };
    return titles[type] || "Login";
};

const getSubtitle = (type) => {
    const subtitles = {
        volunteer: "Access your volunteer dashboard",
        admin: "Access your admin dashboard",
        resident: "Access your resident dashboard",
    };
    return subtitles[type] || "Access your dashboard";
};
</script>

<template>
    <Head :title="`${getUserTypeDisplay(type)} Login`" />

    <!-- Header -->
    <SimpleHeader />

    <!-- Main Container -->
    <main
        :class="[
            'flex flex-col lg:flex-row',
            'min-h-screen bg-background items-center justify-center',
            type === 'resident'
                ? 'pt-2 pb-2 px-2 lg:p-4 gap-2 lg:gap-4'
                : 'p-2 lg:p-4 gap-4',
        ]"
        role="main"
    >
        <!-- Hero Section -->
        <section
            :class="[
                'flex flex-col max-w-7xl bg-background rounded-lg lg:m-8 lg:p-8 lg:gap-8 text-center',
                type === 'resident'
                    ? 'items-center justify-center lg:pt-0'
                    : 'items-center justify-center',
            ]"
            aria-label="Login hero section"
        >
            <div class="flex-1">
                <h1
                    class="text-primary text-2xl lg:text-4xl xl:text-6xl font-bold"
                >
                    {{ getTitle(type) }}
                </h1>
                <h2 class="text-hover text-lg lg:text-2xl xl:text-4xl mt-2">
                    {{ getSubtitle(type) }}
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

            <!-- Sign Up Section - for desktop only -->
            <div
                v-if="type !== 'resident'"
                class="hidden lg:block text-center mb-4"
            >
                <p class="text-hover font-bold text-lg">
                    Not a {{ type === "admin" ? "admin" : "volunteer" }}?
                </p>
                <Link
                    :href="route('register', { type: type })"
                    :aria-label="`Sign up as ${
                        type === 'admin' ? 'admin' : 'volunteer'
                    }`"
                >
                    <CustomButton
                        text="Sign Up"
                        preset="neutral"
                        size="small"
                        :ariaLabel="`Sign up as ${
                            type === 'admin' ? 'admin' : 'volunteer'
                        }`"
                    />
                </Link>
            </div>
        </section>

        <!-- Login Form Section -->
        <section
            class="flex flex-col mx-2 lg:mx-8 lg:bg-primary lg:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
            aria-label="Login form section"
        >
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

                <!-- Authentication Error Message -->
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

                <form @submit.prevent="submit" aria-label="Login form">
                    <div class="space-y-4">
                        <!-- Email Field (for volunteers and admins) -->
                        <div v-if="type !== 'resident'">
                            <InputLabel
                                for="email"
                                value="Email"
                                color=""
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="
                                    form.errors.email ? `email-error` : null
                                "
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

                        <!-- Username Field (for residents) -->
                        <div v-if="type === 'resident'">
                            <InputLabel
                                for="username"
                                value="Username"
                                color=""
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="username"
                                v-model="form.username"
                                type="text"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="
                                    form.errors.username
                                        ? `username-error`
                                        : null
                                "
                                autofocus
                                autocomplete="username"
                                placeholder="Enter your 6-digit username"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                inputmode="numeric"
                            />
                            <p
                                class="mt-1 text-sm text-primary lg:text-white"
                                id="username-hint"
                            >
                                6 digits only
                            </p>
                            <InputError
                                id="username-error"
                                class="mt-2"
                                :message="form.errors.username"
                            />
                        </div>

                        <!-- Password Field -->
                        <div>
                            <InputLabel
                                for="password"
                                :value="
                                    type === 'resident' ? 'PIN' : 'Password'
                                "
                                color=""
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="
                                    form.errors.password
                                        ? `password-error`
                                        : null
                                "
                                autocomplete="current-password"
                                :placeholder="
                                    type === 'resident'
                                        ? 'Enter your 6-digit PIN'
                                        : 'Enter your password'
                                "
                                :maxlength="type === 'resident' ? 6 : undefined"
                                :pattern="
                                    type === 'resident' ? '[0-9]{6}' : undefined
                                "
                                :inputmode="
                                    type === 'resident' ? 'numeric' : undefined
                                "
                            />
                            <p
                                v-if="type === 'resident'"
                                class="mt-1 text-sm text-primary lg:text-white"
                                id="password-hint"
                            >
                                6 digits only
                            </p>
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
                                class="ml-2 text-base text-primary lg:text-white font-medium"
                            >
                                Remember me
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            :aria-busy="form.processing"
                            aria-label="Sign in to your account"
                            class="w-full bg-primary lg:bg-background text-white hover:text-white lg:text-hover lg:border-hover lg:border-2 px-8 py-4 rounded-lg text-lg font-medium hover:bg-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                        >
                            {{ form.processing ? "Signing In..." : "Sign In" }}
                        </button>

                        <!-- Forgot Password Link -->
                        <div class="mt-2 text-center">
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-primary underline lg:text-white hover:text-pressed lg:hover:text-white text-lg"
                                aria-label="Reset your password"
                            >
                                Forgot your password?
                            </Link>
                        </div>
                    </div>
                </form>

                <!-- Google sign-in for mobile (non-residents) -->
                <div
                    v-if="type !== 'resident'"
                    class="lg:hidden text-center space-y-4 mt-4"
                >
                    <!-- Divider line with OR -->
                    <div class="relative flex items-center">
                        <div class="flex-grow h-0.5 bg-primary"></div>
                        <span class="px-3 text-primary text-sm bg-background"
                            >OR</span
                        >
                        <div class="flex-grow h-0.5 bg-primary"></div>
                    </div>

                    <div class="flex items-center justify-center">
                        <a
                            :href="
                                route('auth.google.redirect', { type: type })
                            "
                            aria-label="Continue with Google"
                        >
                            <img
                                src="/images/logos/web_neutral_sq_ctn.svg"
                                alt=""
                                class="h-10"
                                aria-hidden="true"
                            />
                            <span class="sr-only">Continue with Google</span>
                        </a>
                    </div>
                </div>

                <!-- Google sign-in for desktop (non-residents) -->
                <div
                    v-if="type !== 'resident'"
                    class="hidden lg:block text-center space-y-4 mt-4"
                >
                    <!-- Divider line with OR -->
                    <div class="relative flex items-center" aria-hidden="true">
                        <div class="flex-grow h-0.5 bg-black"></div>
                        <span class="px-3 text-black text-md">OR</span>
                        <div class="flex-grow h-0.5 bg-black"></div>
                    </div>

                    <div class="flex items-center justify-center">
                        <a
                            :href="
                                route('auth.google.redirect', { type: type })
                            "
                            aria-label="Continue with Google"
                        >
                            <img
                                src="/images/logos/web_neutral_sq_ctn.svg"
                                alt=""
                                class="h-10"
                                aria-hidden="true"
                            />
                            <span class="sr-only">Continue with Google</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sign Up Section - at bottom for mobile -->
        <div v-if="type !== 'resident'" class="lg:hidden text-center mt-4 mb-4">
            <p class="text-hover font-bold text-lg">
                Not a {{ type === "admin" ? "admin" : "volunteer" }}?
            </p>
            <Link
                :href="route('register', { type: type })"
                :aria-label="`Sign up as ${
                    type === 'admin' ? 'admin' : 'volunteer'
                }`"
            >
                <CustomButton
                    text="Sign Up"
                    preset="neutral"
                    size="small"
                    :ariaLabel="`Sign up as ${
                        type === 'admin' ? 'admin' : 'volunteer'
                    }`"
                />
            </Link>
        </div>
    </main>
</template>
