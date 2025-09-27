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
                    {{ getTitle(type) }}
                </h1>
                <h2
                    class="hidden lg:block text-hover text-lg lg:text-2xl xl:text-4xl"
                >
                    {{ getSubtitle(type) }}
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

            <!-- Sign Up Section -->
            <div class="text-center mb-4">
                <p class="text-hover font-bold text-lg">Not a volunteer?</p>
                <Link :href="route('register', { type: 'volunteer' })">
                    <CustomButton
                        text="Sign Up"
                        preset="neutral"
                        size="small"
                    />
                </Link>
            </div>

            <div class="text-center">
                <button
                    class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 transition-colors lg:hidden"
                >
                    <img
                        src="https://developers.google.com/identity/images/g-logo.png"
                        alt="Google"
                        class="w-5 h-5 mr-3"
                    />
                    <span class="text-gray-700 font-medium"
                        >Continue with Google</span
                    >
                </button>

                <!-- Divider line with OR - only visible on small screens -->
                <div class="md:hidden mt-4">
                    <div class="relative flex items-center">
                        <div class="flex-grow h-0.5 bg-gray-300"></div>
                        <span class="px-3 text-gray-500 text-sm bg-background"
                            >OR</span
                        >
                        <div class="flex-grow h-0.5 bg-gray-300"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Login Form Section -->
        <section
            class="flex flex-col mx-8 lg:bg-primary md:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
        >
            <div class="flex-1 mb-8">
                <h1
                    class="hidden lg:block text-white text-2xl lg:text-4xl xl:text-6xl"
                >
                    Log in to Bridge
                </h1>
            </div>
            <!-- Login Form -->
            <div class="rounded-lg px-8 max-w-md mx-auto space-y-4">
                <div v-if="status" class="font-medium text-sm text-green-600">
                    {{ status }}
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
                                placeholder="Enter your email"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <!-- Password Field -->
                        <div>
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            />
                            <InputError
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
                                class="ml-2 text-md text-white font-bold dark:text-white"
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
                            class="w-full bg-primary lg:bg-background text-white hover:text-white lg:text-hover lg:border-hover lg:border-2 px-8 py-4 rounded-lg text-lg font-medium hover:bg-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? "Signing In..." : "Sign In" }}
                        </button>

                        <!-- Forgot Password Link -->
                        <div class="mt-2 text-center">
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-hover hover:text-pressed text-lg"
                            >
                                Forgot your password?
                            </Link>
                        </div>
                    </div>
                </form>

                <div class="hidden lg:block text-center space-y-4">
                    <!-- Divider line with OR - only visible on small screens -->
                    <div class="relative flex items-center">
                        <div class="flex-grow h-0.5 bg-black"></div>
                        <span class="px-3 text-black text-md">OR</span>
                        <div class="flex-grow h-0.5 bg-black"></div>
                    </div>

                    <button
                        class="w-full flex items-center justify-center px-4 py-3 border border-black rounded-lg bg-white hover:bg-gray-50 transition-colors"
                    >
                        <img
                            src="https://developers.google.com/identity/images/g-logo.png"
                            alt="Google"
                            class="w-5 h-5 mr-3"
                        />
                        <span class="text-gray-700 font-medium"
                            >Continue with Google</span
                        >
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
