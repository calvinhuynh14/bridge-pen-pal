<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Select from "@/Components/Select.vue";
import CustomButton from "@/Components/CustomButton.vue";
import SimpleHeader from "@/Components/SimpleHeader.vue";

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

// Get user type ID based on user type name
const getUserTypeId = (type) => {
    const typeIds = {
        resident: 3, // Based on the migration order
        volunteer: 2,
        admin: 1,
    };
    return typeIds[type] || 2; // Default to volunteer
};

const form = useForm({
    // Common fields
    email: props.googleUser?.email || "",
    password: "",
    password_confirmation: "",
    terms: false,
    user_type_id: getUserTypeId(props.type), // Will be set based on user type

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

// Helper function to get error messages as an array
const getErrorMessages = () => {
    // Collect all error messages
    const errorMessages = [];

    // Check for email already exists error
    if (form.errors.email) {
        const emailError = Array.isArray(form.errors.email)
            ? form.errors.email[0]
            : form.errors.email;
        if (
            emailError.includes("already been taken") ||
            emailError.includes("already exists")
        ) {
            errorMessages.push(
                "This email address is already registered. Please use a different email or try logging in."
            );
        } else if (emailError.includes("required")) {
            errorMessages.push("Email address is required.");
        } else if (emailError.includes("valid email")) {
            errorMessages.push("Please enter a valid email address.");
        } else {
            errorMessages.push(emailError);
        }
    }

    // Check for password errors
    if (form.errors.password) {
        const passwordError = Array.isArray(form.errors.password)
            ? form.errors.password[0]
            : form.errors.password;
        errorMessages.push(passwordError);
    }

    // Check for password confirmation errors
    if (form.errors.password_confirmation) {
        const confirmError = Array.isArray(form.errors.password_confirmation)
            ? form.errors.password_confirmation[0]
            : form.errors.password_confirmation;
        if (confirmError.includes("match")) {
            errorMessages.push("Password confirmation does not match.");
        } else {
            errorMessages.push(confirmError);
        }
    }

    // Check for required field errors
    if (form.errors.name) {
        const error = Array.isArray(form.errors.name)
            ? form.errors.name[0]
            : form.errors.name;
        errorMessages.push(error || "Full name is required.");
    }

    if (form.errors.first_name) {
        const error = Array.isArray(form.errors.first_name)
            ? form.errors.first_name[0]
            : form.errors.first_name;
        errorMessages.push(error || "First name is required.");
    }

    if (form.errors.last_name) {
        const error = Array.isArray(form.errors.last_name)
            ? form.errors.last_name[0]
            : form.errors.last_name;
        errorMessages.push(error || "Last name is required.");
    }

    if (form.errors.organization_id) {
        const error = Array.isArray(form.errors.organization_id)
            ? form.errors.organization_id[0]
            : form.errors.organization_id;
        errorMessages.push(error || "Please select an organization.");
    }

    if (form.errors.organization_name) {
        const error = Array.isArray(form.errors.organization_name)
            ? form.errors.organization_name[0]
            : form.errors.organization_name;
        errorMessages.push(error || "Organization name is required.");
    }

    if (form.errors.terms) {
        const error = Array.isArray(form.errors.terms)
            ? form.errors.terms[0]
            : form.errors.terms;
        errorMessages.push(
            error ||
                "You must agree to the Terms of Service and Privacy Policy."
        );
    }

    if (form.errors.application_notes) {
        const error = Array.isArray(form.errors.application_notes)
            ? form.errors.application_notes[0]
            : form.errors.application_notes;
        errorMessages.push(error);
    }

    // Check for other common errors
    if (form.errors.message) {
        const error = Array.isArray(form.errors.message)
            ? form.errors.message[0]
            : form.errors.message;
        errorMessages.push(error);
    }

    if (form.errors.error) {
        const error = Array.isArray(form.errors.error)
            ? form.errors.error[0]
            : form.errors.error;
        errorMessages.push(error);
    }

    // Get any remaining errors from other fields
    const processedFields = [
        "email",
        "password",
        "password_confirmation",
        "name",
        "first_name",
        "last_name",
        "organization_id",
        "organization_name",
        "terms",
        "application_notes",
        "message",
        "error",
    ];

    for (const [key, value] of Object.entries(form.errors)) {
        if (!processedFields.includes(key)) {
            const error = Array.isArray(value) ? value[0] : value;
            errorMessages.push(error);
        }
    }

    return errorMessages.length > 0
        ? errorMessages
        : ["Please check your information and try again."];
};

const submit = () => {
    // Ensure user_type_id is set before submission
    form.user_type_id = getUserTypeId(props.type);

    form.post(route("register"), {
        onSuccess: () => {
            // Fortify will handle the redirect automatically

            // Reset form fields
            form.reset(
                "password",
                "password_confirmation",
                "name",
                "first_name",
                "last_name",
                "organization_id",
                "application_notes",
                "organization_name"
            );
        },
        onError: () => {
            // Errors are handled by form.errors
        },
        onFinish: () => {
            // This runs after success or error
        },
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

// Convert organizations to Select component format
const organizationOptions = computed(() => {
    return props.organizations.map((org) => ({
        value: org.id,
        label: org.name,
    }));
});

// Character limit for application notes
const APPLICATION_NOTES_MAX_LENGTH = 2000;
const applicationNotesLength = computed(() => {
    return form.application_notes?.length || 0;
});
const applicationNotesRemaining = computed(() => {
    return APPLICATION_NOTES_MAX_LENGTH - applicationNotesLength.value;
});

// Ensure user_type_id is set correctly in the form
form.user_type_id = getUserTypeId(props.type);
</script>

<template>
    <Head :title="`${getUserTypeDisplay(type)} Registration`" />

    <!-- Header -->
    <SimpleHeader />

    <!-- Main Container -->
    <main
        class="flex flex-col md:flex-row lg:flex-row min-h-screen bg-background items-center justify-center p-4 gap-4"
        role="main"
    >
        <!-- Hero Section -->
        <section
            class="flex flex-col max-w-7xl bg-background rounded-lg lg:m-8 lg:p-8 lg:gap-8 text-center items-center justify-center"
            aria-label="Registration hero section"
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
                class="flex items-center justify-center w-3/4 md:w-3/5 lg:w-1/2"
                aria-hidden="true"
            >
                <img
                    src="/images/logos/logo-with-name-purple.svg"
                    alt=""
                    class="w-full h-full object-contain md:w-1/2 lg:w-full"
                />
            </div>

            <!-- Sign In Section -->
            <div class="text-center mb-4">
                <p class="text-hover font-bold text-lg">
                    Already have an account?
                </p>
                <Link
                    :href="route('login', { type: type })"
                    :aria-label="`Sign in as ${type}`"
                >
                    <CustomButton
                        text="Sign In"
                        preset="neutral"
                        size="small"
                        :ariaLabel="`Sign in as ${type}`"
                    />
                </Link>
            </div>

            <div v-if="type === 'resident'" class="text-center">
                <a
                    :href="route('auth.google.redirect', { type: type })"
                    class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 transition-colors lg:hidden"
                    aria-label="Continue with Google"
                >
                    <img
                        src="https://developers.google.com/identity/images/g-logo.png"
                        alt=""
                        class="w-5 h-5 mr-3"
                        aria-hidden="true"
                    />
                    <span class="text-gray-700 font-medium"
                        >Continue with Google</span
                    >
                </a>

                <!-- Divider line with OR - only visible on small screens -->
                <div class="md:hidden mt-4">
                    <div class="relative flex items-center" aria-hidden="true">
                        <div class="flex-grow h-0.5 bg-primary"></div>
                        <span class="px-3 text-primary text-sm bg-background"
                            >OR</span
                        >
                        <div class="flex-grow h-0.5 bg-primary"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Registration Form Section -->
        <section
            class="flex flex-col mx-8 lg:bg-primary md:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
            aria-label="Registration form section"
        >
            <!-- Registration Form -->
            <div
                class="rounded-lg px-0 lg:px-8 max-w-md lg:max-w-lg mx-auto space-y-4"
            >
                <!-- Error Message Box -->
                <div
                    v-if="Object.keys(form.errors).length > 0"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                    class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg"
                >
                    <ul class="list-disc list-inside space-y-1">
                        <li
                            v-for="(error, index) in getErrorMessages()"
                            :key="index"
                            class="text-sm text-red-600 font-medium"
                        >
                            {{ error }}
                        </li>
                    </ul>
                </div>

                <form @submit.prevent="submit" aria-label="Registration form">
                    <!-- Hidden field for user type -->
                    <input
                        type="hidden"
                        name="user_type_id"
                        :value="getUserTypeId(type)"
                    />

                    <div class="space-y-4">
                        <!-- Resident: Full Name Field -->
                        <div v-if="type === 'resident'">
                            <InputLabel
                                for="name"
                                value="Full Name"
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="
                                    form.errors.name ? `name-error` : null
                                "
                                autofocus
                                autocomplete="name"
                                placeholder="Enter your full name"
                            />
                            <InputError
                                id="name-error"
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
                                <InputLabel
                                    for="first_name"
                                    value="First Name"
                                    size="base"
                                    class="text-primary lg:text-white"
                                />
                                <TextInput
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                    :required="true"
                                    :errorId="
                                        form.errors.first_name
                                            ? `first_name-error`
                                            : null
                                    "
                                    autofocus
                                    autocomplete="given-name"
                                    placeholder="First name"
                                />
                                <InputError
                                    id="first_name-error"
                                    class="mt-2"
                                    :message="form.errors.first_name"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    for="last_name"
                                    value="Last Name"
                                    size="base"
                                    class="text-primary lg:text-white"
                                />
                                <TextInput
                                    id="last_name"
                                    v-model="form.last_name"
                                    type="text"
                                    class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                    :required="true"
                                    :errorId="
                                        form.errors.last_name
                                            ? `last_name-error`
                                            : null
                                    "
                                    autocomplete="family-name"
                                    placeholder="Last name"
                                />
                                <InputError
                                    id="last_name-error"
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
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <Select
                                id="organization_id"
                                v-model="form.organization_id"
                                :options="organizationOptions"
                                placeholder="Select..."
                                class="mt-2"
                                :required="true"
                                :errorId="
                                    form.errors.organization_id
                                        ? `organization_id-error`
                                        : null
                                "
                            />
                            <InputError
                                id="organization_id-error"
                                class="mt-2"
                                :message="form.errors.organization_id"
                            />
                        </div>

                        <!-- Volunteer: Application Notes -->
                        <div v-if="type === 'volunteer'">
                            <div class="flex justify-between items-center">
                                <InputLabel
                                    for="application_notes"
                                    value="Message for Administrators (Optional)"
                                    size="base"
                                    class="text-primary lg:text-white"
                                />
                                <span
                                    class="text-sm font-medium"
                                    :class="{
                                        'text-primary lg:text-white':
                                            applicationNotesRemaining > 100,
                                        'text-yellow-600':
                                            applicationNotesRemaining <= 100 &&
                                            applicationNotesRemaining > 0,
                                        'text-red-600':
                                            applicationNotesRemaining <= 0,
                                    }"
                                >
                                    {{ applicationNotesLength }} /
                                    {{ APPLICATION_NOTES_MAX_LENGTH }}
                                </span>
                            </div>
                            <textarea
                                id="application_notes"
                                v-model="form.application_notes"
                                rows="4"
                                :maxlength="APPLICATION_NOTES_MAX_LENGTH"
                                :aria-describedby="
                                    form.errors.application_notes
                                        ? `application_notes-error`
                                        : undefined
                                "
                                :aria-invalid="!!form.errors.application_notes"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary resize-y min-h-[100px]"
                                :class="{
                                    'border-yellow-500':
                                        applicationNotesRemaining <= 100 &&
                                        applicationNotesRemaining > 0,
                                    'border-red-500':
                                        applicationNotesRemaining <= 0,
                                }"
                                placeholder="Tell us why you'd like to volunteer and any relevant experience..."
                            ></textarea>
                            <InputError
                                id="application_notes-error"
                                class="mt-2"
                                :message="form.errors.application_notes"
                            />
                        </div>

                        <!-- Admin: Organization Name Field -->
                        <div v-if="type === 'admin'">
                            <InputLabel
                                for="organization_name"
                                value="Organization Name"
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="organization_name"
                                v-model="form.organization_name"
                                type="text"
                                class="mt-2 block w-full max-w-md mx-auto border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="
                                    form.errors.organization_name
                                        ? `organization_name-error`
                                        : null
                                "
                                autofocus
                                autocomplete="organization"
                                placeholder="Enter your organization name"
                            />
                            <InputError
                                id="organization_name-error"
                                class="mt-2"
                                :message="form.errors.organization_name"
                            />
                        </div>

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
                                :class="{
                                    'bg-gray-100 cursor-not-allowed opacity-75':
                                        isGoogleUser,
                                }"
                                :disabled="isGoogleUser"
                                :required="!isGoogleUser"
                                :errorId="
                                    form.errors.email ? `email-error` : null
                                "
                                autocomplete="email"
                                placeholder="Enter your email address"
                            />
                            <InputError
                                id="email-error"
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <!-- Google OAuth Message -->
                        <div
                            v-if="isGoogleUser"
                            role="status"
                            aria-live="polite"
                            aria-atomic="true"
                            class="bg-green-50 border border-green-200 rounded-lg p-4"
                        >
                            <div class="flex items-center">
                                <img
                                    :src="googleUser.avatar"
                                    :alt="`${googleUser.name}'s avatar`"
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
                                class="mt-2 block w-full max-w-md mx-auto border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="
                                    form.errors.password
                                        ? `password-error`
                                        : null
                                "
                                autocomplete="new-password"
                                placeholder="Create a password"
                            />
                            <p
                                id="password-requirements"
                                class="mt-1 text-sm font-medium text-primary lg:text-white"
                            >
                                Password must be at least 8 characters and
                                include uppercase, lowercase, number, and
                                special character.
                            </p>
                            <InputError
                                id="password-error"
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <!-- Confirm Password Field - Hidden for Google users -->
                        <div v-if="!isGoogleUser">
                            <InputLabel
                                for="password_confirmation"
                                value="Confirm Password"
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-2 block w-full max-w-md mx-auto border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="
                                    form.errors.password_confirmation
                                        ? `password_confirmation-error`
                                        : null
                                "
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                            />
                            <InputError
                                id="password_confirmation-error"
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
                                :required="true"
                                :aria-invalid="
                                    form.errors.terms ? 'true' : 'false'
                                "
                                :aria-describedby="
                                    form.errors.terms
                                        ? `terms-error`
                                        : undefined
                                "
                            />
                            <div class="ml-2">
                                <label
                                    for="terms"
                                    class="text-base text-primary lg:text-white font-medium"
                                >
                                    I agree to the
                                    <Link
                                        :href="route('terms.show')"
                                        class="underline text-primary lg:text-white hover:text-pressed lg:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                        aria-label="View Terms of Service"
                                    >
                                        Terms of Service
                                    </Link>
                                    and
                                    <Link
                                        :href="route('policy.show')"
                                        class="underline text-primary lg:text-white hover:text-pressed lg:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                        aria-label="View Privacy Policy"
                                    >
                                        Privacy Policy
                                    </Link>
                                </label>
                            </div>
                            <InputError
                                id="terms-error"
                                class="mt-2 ml-2"
                                :message="form.errors.terms"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <CustomButton
                            type="submit"
                            :text="
                                form.processing
                                    ? isGoogleUser
                                        ? 'Completing Registration...'
                                        : 'Creating Account...'
                                    : isGoogleUser
                                    ? 'Complete Registration'
                                    : 'Create Account'
                            "
                            preset="neutral"
                            size="medium"
                            :isLoading="form.processing"
                            :ariaLabel="
                                form.processing
                                    ? isGoogleUser
                                        ? 'Completing Registration...'
                                        : 'Creating Account...'
                                    : isGoogleUser
                                    ? 'Complete Registration'
                                    : 'Create Account'
                            "
                            class="w-full max-w-md mx-auto"
                        />
                    </div>
                </form>
            </div>
        </section>
    </main>
</template>
