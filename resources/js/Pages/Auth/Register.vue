<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CustomButton from "@/Components/CustomButton.vue";

// Get user type from query parameter, default to 'resident'
const props = defineProps({
    type: {
        type: String,
        default: "resident",
    },
    google: {
        type: String,
        default: null,
    },
    googleUser: {
        type: Object,
        default: null,
    },
    organizations: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    // Common fields
    email: props.googleUser?.email || "",
    password: "",
    password_confirmation: "",
    terms: false,
    user_type: props.type, // Set user type from props

    // Resident fields
    name: props.googleUser?.name || "",

    // Volunteer fields
    first_name: props.googleUser?.name?.split(" ")[0] || "",
    last_name: props.googleUser?.name?.split(" ").slice(1).join(" ") || "",
    organization_id: "",
    application_notes: "",

    // Admin fields
    organization_name: "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () =>
            form.reset(
                "password",
                "password_confirmation",
                "name",
                "first_name",
                "last_name",
                "organization_id",
                "application_notes",
                "organization_name"
            ),
    });
};

// Get display text based on user type
const getUserTypeDisplay = (type) => {
    const types = {
        resident: "Resident",
        volunteer: "Volunteer",
        admin: "Admin",
    };
    return types[type] || "User";
};

const getTitle = (type) => {
    const titles = {
        resident: "Join as a Resident",
        volunteer: "Join as a Volunteer",
        admin: "Join as an Admin",
    };
    return titles[type] || "Join Bridge";
};

const getSubtitle = (type) => {
    const subtitles = {
        resident: "Connect with volunteers and share your stories",
        volunteer: "Help residents and make a difference",
        admin: "Manage the platform and support users",
    };
    return subtitles[type] || "Join our community";
};

// Check if this is a Google OAuth user
const isGoogleUser = computed(() => {
    return props.google === "true" && props.googleUser;
});
</script>

<template>
    <Head :title="`${getUserTypeDisplay(type)} Registration`" />

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

            <!-- Sign In Section -->
            <div class="text-center mb-4">
                <p class="text-hover font-bold text-lg">
                    Already have an account?
                </p>
                <Link :href="route('login', { type: type })">
                    <CustomButton
                        text="Sign In"
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

        <!-- Registration Form Section -->
        <section
            class="flex flex-col mx-8 lg:bg-primary md:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
        >
            <div class="flex-1 mb-8">
                <h1
                    class="hidden lg:block text-white text-2xl lg:text-4xl xl:text-6xl"
                >
                    Join Bridge
                </h1>
            </div>
            <!-- Registration Form -->
            <div class="rounded-lg px-8 max-w-md mx-auto space-y-4">
                <form @submit.prevent="submit">
                    <!-- Hidden field for user type -->
                    <input type="hidden" name="user_type" :value="type" />

                    <div class="space-y-4">
                        <!-- Resident: Full Name Field -->
                        <div v-if="type === 'resident'">
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Enter your full name"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
                        </div>

                        <!-- Volunteer: First Name and Last Name Fields -->
                        <div
                            v-if="type === 'volunteer'"
                            class="grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <div>
                                <TextInput
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                    required
                                    autofocus
                                    autocomplete="given-name"
                                    placeholder="First name"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.first_name"
                                />
                            </div>
                            <div>
                                <TextInput
                                    id="last_name"
                                    v-model="form.last_name"
                                    type="text"
                                    class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                    required
                                    autocomplete="family-name"
                                    placeholder="Last name"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.last_name"
                                />
                            </div>
                        </div>

                        <!-- Volunteer: Organization Selection -->
                        <div v-if="type === 'volunteer'">
                            <InputLabel
                                for="organization_id"
                                value="Select Organization"
                            />
                            <select
                                id="organization_id"
                                v-model="form.organization_id"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                required
                            >
                                <option value="">
                                    Choose an organization...
                                </option>
                                <option
                                    v-for="org in organizations"
                                    :key="org.id"
                                    :value="org.id"
                                >
                                    {{ org.name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.organization_id"
                            />
                        </div>

                        <!-- Volunteer: Application Notes -->
                        <div v-if="type === 'volunteer'">
                            <InputLabel
                                for="application_notes"
                                value="Why do you want to volunteer? (Optional)"
                            />
                            <textarea
                                id="application_notes"
                                v-model="form.application_notes"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                rows="3"
                                placeholder="Tell us why you're interested in becoming a pen pal volunteer..."
                            ></textarea>
                            <InputError
                                class="mt-2"
                                :message="form.errors.application_notes"
                            />
                        </div>

                        <!-- Admin: Organization Name Field -->
                        <div v-if="type === 'admin'">
                            <TextInput
                                id="organization_name"
                                v-model="form.organization_name"
                                type="text"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                required
                                autofocus
                                autocomplete="organization"
                                placeholder="Enter your organization name"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.organization_name"
                            />
                        </div>

                        <!-- Email Field -->
                        <div>
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :disabled="isGoogleUser"
                                required
                                autocomplete="email"
                                placeholder="Enter your email address"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <!-- Google OAuth Message -->
                        <div
                            v-if="isGoogleUser"
                            class="bg-green-50 border border-green-200 rounded-lg p-4"
                        >
                            <div class="flex items-center">
                                <img
                                    :src="googleUser.avatar"
                                    :alt="googleUser.name"
                                    class="w-8 h-8 rounded-full mr-3"
                                />
                                <div>
                                    <p
                                        class="text-sm text-green-800 font-medium"
                                    >
                                        Signed in with Google
                                    </p>
                                    <p class="text-xs text-green-600">
                                        {{ googleUser.name }} ({{
                                            googleUser.email
                                        }})
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Password Field - Hidden for Google users -->
                        <div v-if="!isGoogleUser">
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                required
                                autocomplete="new-password"
                                placeholder="Create a password"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <!-- Confirm Password Field - Hidden for Google users -->
                        <div v-if="!isGoogleUser">
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.password_confirmation"
                            />
                        </div>

                        <!-- Terms and Conditions -->
                        <div
                            v-if="
                                $page.props.jetstream
                                    .hasTermsAndPrivacyPolicyFeature
                            "
                            class="flex items-center"
                        >
                            <Checkbox
                                id="terms"
                                v-model:checked="form.terms"
                                name="terms"
                                required
                            />
                            <div class="ml-2">
                                <label
                                    for="terms"
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    I agree to the
                                    <Link
                                        :href="route('terms.show')"
                                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Terms of Service
                                    </Link>
                                    and
                                    <Link
                                        :href="route('policy.show')"
                                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Privacy Policy
                                    </Link>
                                </label>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.terms" />
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-primary lg:bg-background text-white lg:text-hover lg:border-hover lg:border-2 px-8 py-4 rounded-lg text-lg font-medium hover:bg-pressed transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{
                                form.processing
                                    ? isGoogleUser
                                        ? "Completing Registration..."
                                        : "Creating Account..."
                                    : isGoogleUser
                                    ? "Complete Registration"
                                    : "Create Account"
                            }}
                        </button>
                    </div>
                </form>

                <div class="hidden lg:block text-center space-y-4">
                    <!-- Divider line with OR - only visible on small screens -->
                    <div class="relative flex items-center">
                        <div class="flex-grow h-0.5 bg-black"></div>
                        <span class="px-3 text-black text-md">OR</span>
                        <div class="flex-grow h-0.5 bg-black"></div>
                    </div>
                </div>

                <div class="flex items-center justify-center">
                    <a :href="route('auth.google.redirect', { type: type })">
                        <img
                            src="/images/logos/web_neutral_sq_ctn.svg"
                            alt="Continue with Google"
                            class="h-10"
                        />
                    </a>
                </div>
            </div>
        </section>
    </div>
</template>
