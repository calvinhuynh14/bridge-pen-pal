<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, usePage, router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import CustomButton from "@/Components/CustomButton.vue";
import AvatarSelector from "@/Components/AvatarSelector.vue";
import InterestSelectionModal from "@/Components/InterestSelectionModal.vue";
import LanguageSelectionModal from "@/Components/LanguageSelectionModal.vue";

// Get current user from page props
const page = usePage();
const user = computed(() => page.props.auth.user);
const props = defineProps({
    organizationName: {
        type: String,
        default: null,
    },
    availableAvatars: {
        type: Array,
        default: () => [],
    },
    currentAvatar: {
        type: String,
        default: null,
    },
    availableInterests: {
        type: Array,
        default: () => [],
    },
    userInterests: {
        type: Array,
        default: () => [],
    },
    availableLanguages: {
        type: Array,
        default: () => [],
    },
    userLanguages: {
        type: Array,
        default: () => [],
    },
    isAnonymous: {
        type: Boolean,
        default: false,
    },
    anonymousName: {
        type: String,
        default: null,
    },
});

// Helper functions to check user type
const isAdmin = computed(() => user.value?.user_type === "admin");
const isVolunteer = computed(() => user.value?.user_type === "volunteer");
const isResident = computed(() => user.value?.user_type === "resident");

// Get organization name
const organizationName = computed(() => {
    return props.organizationName || "No organization assigned";
});

// Form for profile updates
const profileForm = useForm({
    name: user.value?.name || "",
    email: isVolunteer.value ? user.value?.email || "" : "",
    organization_name: isAdmin.value ? props.organizationName || "" : "",
});

// Success message refs
const profileUpdateSuccess = ref(false);
const passwordUpdateSuccess = ref(false);

// Interest modal state
const showInterestModal = ref(false);

// Language modal state
const showLanguageModal = ref(false);

// Anonymous name prop
const anonymousName = computed(() => props.anonymousName);

// Anonymous mode state - use computed to sync with props
const isAnonymousMode = computed({
    get: () => props.isAnonymous || false,
    set: (value) => {
        // This will be handled by the update function
    },
});

// Anonymous mode form
const anonymousForm = useForm({
    is_anonymous: props.isAnonymous || false,
});

// Watch for prop changes and update form
watch(
    () => props.isAnonymous,
    (newValue) => {
        anonymousForm.is_anonymous = Boolean(newValue);
    },
    { immediate: true }
);

// Update anonymous mode
const updateAnonymousMode = (event) => {
    const newValue = event.target.checked;

    // Update form value
    anonymousForm.is_anonymous = newValue;

    anonymousForm.put(route("profile.anonymous.update"), {
        preserveScroll: true,
        onSuccess: () => {
            // Reload page props to get updated anonymous status and name
            router.reload({ only: ["isAnonymous", "anonymousName"] });
        },
        onError: (errors) => {
            console.error("Profile Show - Update failed:", errors);
            // Revert on error
            anonymousForm.is_anonymous = !newValue;
        },
    });
};

// Get selected interests names
const selectedInterestsNames = computed(() => {
    if (
        !props.availableInterests ||
        !props.userInterests ||
        props.availableInterests.length === 0
    ) {
        return [];
    }
    return props.availableInterests
        .filter((interest) => props.userInterests.includes(interest.id))
        .map((interest) => interest.name);
});

// Props references for template
const availableInterests = computed(() => props.availableInterests || []);
const userInterests = computed(() => props.userInterests || []);
const availableLanguages = computed(() => props.availableLanguages || []);
const userLanguages = computed(() => props.userLanguages || []);

// Get selected languages names
const selectedLanguagesNames = computed(() => {
    if (
        !availableLanguages.value ||
        !userLanguages.value ||
        availableLanguages.value.length === 0
    ) {
        return [];
    }
    return availableLanguages.value
        .filter((language) => userLanguages.value.includes(language.id))
        .map((language) => language.name);
});

// Check if user might be a Google OAuth user (we can't definitively know, but we'll show a note)
// For now, we'll allow email changes but show a note
const isGoogleOAuthUser = computed(() => {
    // We can't definitively detect this, but we'll show a note anyway
    // Users who signed up with Google OAuth should know they did
    return false; // This would need to be passed from backend if we add a flag
});

// Form for password update
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

// Helper function to get password error messages as an array
const getPasswordErrorMessages = () => {
    const errorMessages = [];

    // Get errors from form.errors - errors are nested under updatePassword due to errorBag
    const errors = passwordForm.errors || {};
    const updatePasswordErrors = errors.updatePassword || errors;

    // Only check for password errors (ignore current_password and password_confirmation)
    if (updatePasswordErrors.password) {
        const passwordErrors = Array.isArray(updatePasswordErrors.password)
            ? updatePasswordErrors.password
            : [updatePasswordErrors.password];

        // Check if password is the same as current password
        let isSamePassword = false;
        passwordErrors.forEach((passwordError) => {
            const errorLower = passwordError.toLowerCase();
            if (
                errorLower.includes("different") ||
                errorLower.includes("must be different")
            ) {
                isSamePassword = true;
                errorMessages.push(
                    "The new password must be different from your current password."
                );
            }
        });

        // If not the same password error, show requirements error
        if (!isSamePassword) {
            errorMessages.push("Password must contain:");
            errorMessages.push("• At least 8 characters");
            errorMessages.push("• At least one capital letter");
            errorMessages.push("• At least one number");
            errorMessages.push("• At least one special character");
        }
    }

    return errorMessages;
};

// Update profile
const updateProfile = () => {
    // Volunteers can update name and email
    // Admins can update name, email, and organization name
    // Residents cannot update anything
    let formData;

    if (isAdmin.value) {
        // Admins can only update organization name
        formData = {
            organization_name: profileForm.organization_name,
        };
    } else if (isVolunteer.value) {
        // Volunteers can only update name, not email
        formData = { name: profileForm.name };
    } else {
        return; // Residents cannot update
    }

    profileForm
        .transform(() => formData)
        .put(route("user-profile-information.update"), {
            preserveScroll: true,
            onSuccess: () => {
                profileUpdateSuccess.value = true;
                setTimeout(() => {
                    profileUpdateSuccess.value = false;
                }, 5000);
                // Don't reset form, keep the updated values
                // For admins, refresh the page to get updated organization name
                if (isAdmin.value) {
                    window.location.reload();
                }
            },
        });
};

// Update password (volunteers and admins)
const updatePassword = () => {
    passwordForm.put(route("user-password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            passwordUpdateSuccess.value = true;
            setTimeout(() => {
                passwordUpdateSuccess.value = false;
            }, 5000);
        },
    });
};

// Get profile title based on user type
const getProfileTitle = () => {
    if (isAdmin.value) return "Admin Profile Settings";
    if (isVolunteer.value) return "Volunteer Profile Settings";
    if (isResident.value) return "Resident Profile Settings";
    return "Profile Settings";
};
</script>

<template>
    <Head :title="getProfileTitle()" />

    <AppLayout :title="getProfileTitle()">
        <template #header>
            <h2
                class="font-semibold text-2xl lg:text-3xl text-primary leading-tight"
            >
                {{ getProfileTitle() }}
            </h2>
        </template>

        <main class="py-12" role="main">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="sr-only">{{ getProfileTitle() }}</h1>
                <!-- Avatar Selection Section -->
                <section
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl rounded-lg p-4 sm:p-6 mb-8"
                    aria-label="Avatar selection"
                >
                    <AvatarSelector
                        :available-avatars="availableAvatars"
                        :current-avatar="currentAvatar"
                        :user-name="user?.name || ''"
                    />
                </section>

                <!-- Profile Information Card -->
                <section
                    class="bg-primary overflow-hidden shadow-xl rounded-lg p-4 sm:p-6 mb-8"
                    aria-label="Profile information"
                >
                    <h2
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Profile Information
                    </h2>

                    <form
                        @submit.prevent="updateProfile"
                        aria-label="Profile information form"
                    >
                        <!-- Success Message -->
                        <div
                            v-if="profileUpdateSuccess"
                            role="status"
                            aria-live="polite"
                            aria-atomic="true"
                            class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg"
                        >
                            <p class="text-base text-green-600 font-medium">
                                Profile updated successfully!
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name Field (Editable for volunteers only, read-only for residents, hidden for admins) -->
                            <div v-if="!isAdmin">
                                <InputLabel
                                    for="name"
                                    value="Name"
                                    color="white"
                                    size="lg"
                                />
                                <TextInput
                                    id="name"
                                    v-model="profileForm.name"
                                    type="text"
                                    class="mt-2 block w-full text-lg"
                                    :class="
                                        isResident
                                            ? 'bg-gray-100 cursor-not-allowed opacity-75'
                                            : ''
                                    "
                                    :disabled="isResident"
                                    required
                                    :autofocus="!isResident"
                                    :error-id="
                                        profileForm.errors.name
                                            ? 'name-error'
                                            : undefined
                                    "
                                />
                                <InputError
                                    id="name-error"
                                    class="mt-2"
                                    :message="profileForm.errors.name"
                                />
                            </div>

                            <!-- Email Field (Read-only for volunteers and admins) -->
                            <div v-if="isVolunteer || isAdmin">
                                <InputLabel
                                    for="email"
                                    value="Email Address"
                                    color="white"
                                    size="lg"
                                />
                                <TextInput
                                    id="email"
                                    :value="user?.email || ''"
                                    type="email"
                                    class="mt-2 block w-full text-base bg-gray-100 cursor-not-allowed opacity-75"
                                    disabled
                                    readonly
                                    aria-label="Email address (read-only)"
                                />
                                <p class="mt-1 text-base text-white">
                                    Email address cannot be changed.
                                </p>
                            </div>

                            <!-- Organization Field (Editable for admins, read-only for volunteers and residents) -->
                            <div v-if="isAdmin">
                                <InputLabel
                                    for="organization_name"
                                    value="Organization Name"
                                    color="white"
                                    size="lg"
                                />
                                <TextInput
                                    id="organization_name"
                                    v-model="profileForm.organization_name"
                                    type="text"
                                    class="mt-2 block w-full text-lg"
                                    required
                                    :error-id="
                                        profileForm.errors.organization_name
                                            ? 'organization_name-error'
                                            : undefined
                                    "
                                />
                                <InputError
                                    id="organization_name-error"
                                    class="mt-2"
                                    :message="
                                        profileForm.errors.organization_name
                                    "
                                />
                            </div>

                            <!-- Organization Field (Read-only for volunteers and residents) -->
                            <div v-if="isVolunteer || isResident">
                                <InputLabel
                                    for="organization"
                                    value="Organization"
                                    color="white"
                                    size="lg"
                                />
                                <TextInput
                                    id="organization"
                                    :value="organizationName"
                                    type="text"
                                    class="mt-2 block w-full text-base bg-gray-100 cursor-not-allowed opacity-75"
                                    disabled
                                    aria-label="Organization (read-only)"
                                />
                            </div>
                        </div>

                        <!-- Save Button (Volunteers and Admins - residents cannot update anything) -->
                        <div v-if="isVolunteer || isAdmin" class="mt-6">
                            <CustomButton
                                :text="
                                    profileForm.processing
                                        ? 'Saving...'
                                        : 'Save Changes'
                                "
                                preset="neutral"
                                size="small"
                                :disabled="profileForm.processing"
                                :isLoading="profileForm.processing"
                                ariaLabel="Save profile changes"
                            />
                        </div>
                    </form>
                </section>

                <!-- Account Settings Card (Volunteers and Admins) -->
                <section
                    v-if="isVolunteer || isAdmin"
                    class="bg-primary overflow-hidden shadow-xl rounded-lg p-4 sm:p-6 mb-8"
                    aria-label="Account settings"
                >
                    <h2
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Account Settings
                    </h2>

                    <div class="space-y-6">
                        <!-- Change Password Section -->
                        <div>
                            <h3
                                class="text-base lg:text-lg font-medium text-white mb-2"
                            >
                                Change Password
                            </h3>
                            <p class="text-lg text-white mb-4">
                                Update your account password. Your password must
                                include:
                            </p>
                            <ul
                                class="text-lg text-white mb-4 list-disc list-inside space-y-1"
                                role="list"
                                aria-label="Password requirements"
                            >
                                <li role="listitem">At least 8 characters</li>
                                <li role="listitem">
                                    At least one capital letter
                                </li>
                                <li role="listitem">At least one number</li>
                                <li role="listitem">
                                    At least one special character
                                </li>
                            </ul>
                            <form
                                @submit.prevent="updatePassword"
                                aria-label="Change password form"
                            >
                                <!-- Success Message -->
                                <div
                                    v-if="passwordUpdateSuccess"
                                    role="status"
                                    aria-live="polite"
                                    aria-atomic="true"
                                    class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg"
                                >
                                    <p
                                        class="text-sm text-green-600 font-medium"
                                    >
                                        Password updated successfully!
                                    </p>
                                </div>
                                <!-- Error Message -->
                                <div
                                    v-if="getPasswordErrorMessages().length > 0"
                                    role="alert"
                                    aria-live="assertive"
                                    aria-atomic="true"
                                    class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg"
                                >
                                    <ul class="list-disc list-inside space-y-1">
                                        <li
                                            v-for="(
                                                error, index
                                            ) in getPasswordErrorMessages()"
                                            :key="index"
                                            class="text-base text-red-600 font-medium"
                                        >
                                            {{ error }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel
                                            for="current_password"
                                            value="Current Password"
                                            color="white"
                                            size="lg"
                                        />
                                        <TextInput
                                            id="current_password"
                                            v-model="
                                                passwordForm.current_password
                                            "
                                            type="password"
                                            class="mt-2 block w-full text-lg"
                                            required
                                            autocomplete="current-password"
                                            :error-id="
                                                passwordForm.errors
                                                    .current_password
                                                    ? 'current_password-error'
                                                    : undefined
                                            "
                                        />
                                        <InputError
                                            id="current_password-error"
                                            class="mt-2"
                                            :message="
                                                passwordForm.errors
                                                    .current_password
                                            "
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="password"
                                            value="New Password"
                                            color="white"
                                            size="lg"
                                        />
                                        <TextInput
                                            id="password"
                                            v-model="passwordForm.password"
                                            type="password"
                                            class="mt-2 block w-full text-lg"
                                            required
                                            autocomplete="new-password"
                                            :error-id="
                                                getPasswordErrorMessages()
                                                    .length > 0
                                                    ? 'password-error'
                                                    : undefined
                                            "
                                        />
                                        <InputError
                                            id="password-error"
                                            class="mt-2"
                                            :message="
                                                getPasswordErrorMessages()
                                                    .length > 0
                                                    ? getPasswordErrorMessages().join(
                                                          ' '
                                                      )
                                                    : undefined
                                            "
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="password_confirmation"
                                            value="Confirm New Password"
                                            color="white"
                                            size="lg"
                                        />
                                        <TextInput
                                            id="password_confirmation"
                                            v-model="
                                                passwordForm.password_confirmation
                                            "
                                            type="password"
                                            class="mt-2 block w-full text-lg"
                                            required
                                            autocomplete="new-password"
                                            :error-id="
                                                passwordForm.errors
                                                    .password_confirmation
                                                    ? 'password_confirmation-error'
                                                    : undefined
                                            "
                                        />
                                        <InputError
                                            id="password_confirmation-error"
                                            class="mt-2"
                                            :message="
                                                passwordForm.errors
                                                    .password_confirmation
                                            "
                                        />
                                    </div>
                                    <div>
                                        <CustomButton
                                            :text="
                                                passwordForm.processing
                                                    ? 'Updating...'
                                                    : 'Update Password'
                                            "
                                            preset="neutral"
                                            size="small"
                                            :disabled="passwordForm.processing"
                                            :isLoading="passwordForm.processing"
                                            ariaLabel="Update password"
                                        />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Interests Section -->
                <section
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl rounded-lg p-4 sm:p-6 mb-8"
                    aria-label="Interests"
                >
                    <h2
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Interests
                    </h2>
                    <div class="space-y-4">
                        <p class="text-lg text-white mb-4">
                            Select your interests to help match you with pen
                            pals
                        </p>
                        <div
                            v-if="selectedInterestsNames.length > 0"
                            class="flex flex-wrap gap-2 mb-4"
                            role="list"
                            aria-label="Selected interests"
                        >
                            <span
                                v-for="(
                                    interestName, index
                                ) in selectedInterestsNames"
                                :key="index"
                                class="px-3 py-1 bg-white text-black rounded-full text-lg font-medium"
                                role="listitem"
                            >
                                {{ interestName }}
                            </span>
                        </div>
                        <div v-else class="mb-4">
                            <p
                                class="text-base text-white italic"
                                role="status"
                                aria-live="polite"
                                aria-atomic="true"
                            >
                                No interests selected yet
                            </p>
                        </div>
                        <CustomButton
                            text="Add/Remove Interests"
                            preset="neutral"
                            size="small"
                            @click="showInterestModal = true"
                            ariaLabel="Open interests selection modal"
                        />
                    </div>
                </section>

                <!-- Languages Section -->
                <section
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl rounded-lg p-4 sm:p-6 mb-8"
                    aria-label="Languages"
                >
                    <h2
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Languages
                    </h2>
                    <div class="space-y-4">
                        <p class="text-lg text-white mb-4">
                            Select languages you speak to help match you with
                            pen pals
                        </p>
                        <div
                            v-if="selectedLanguagesNames.length > 0"
                            class="flex flex-wrap gap-2 mb-4"
                            role="list"
                            aria-label="Selected languages"
                        >
                            <span
                                v-for="(
                                    languageName, index
                                ) in selectedLanguagesNames"
                                :key="index"
                                class="px-3 py-1 bg-white text-black rounded-full text-lg font-medium"
                                role="listitem"
                            >
                                {{ languageName }}
                            </span>
                        </div>
                        <div v-else class="mb-4">
                            <p
                                class="text-base text-white italic"
                                role="status"
                                aria-live="polite"
                                aria-atomic="true"
                            >
                                No languages selected yet
                            </p>
                        </div>
                        <CustomButton
                            text="Add/Remove Languages"
                            preset="neutral"
                            size="small"
                            @click="showLanguageModal = true"
                            ariaLabel="Open languages selection modal"
                        />
                    </div>
                </section>

                <!-- Anonymous Mode Section -->
                <section
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl rounded-lg p-4 sm:p-6"
                    aria-label="Privacy settings"
                >
                    <h2
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Privacy Settings
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-white">
                                    Anonymous Mode
                                </h3>
                                <p class="text-base text-white mt-1">
                                    Hide your real name in communications. Your
                                    name will be replaced with a random
                                    anonymous name.
                                </p>
                                <p
                                    v-if="
                                        anonymousForm.is_anonymous &&
                                        anonymousName
                                    "
                                    class="text-base text-white mt-2 italic"
                                    role="status"
                                    aria-live="polite"
                                    aria-atomic="true"
                                >
                                    Your anonymous name: {{ anonymousName }}
                                </p>
                            </div>
                            <label
                                class="relative inline-flex items-center cursor-pointer"
                                :for="
                                    anonymousForm.processing
                                        ? undefined
                                        : 'anonymousModeToggle'
                                "
                            >
                                <span class="sr-only">
                                    {{
                                        anonymousForm.is_anonymous
                                            ? "Disable anonymous mode"
                                            : "Enable anonymous mode"
                                    }}
                                </span>
                                <input
                                    id="anonymousModeToggle"
                                    type="checkbox"
                                    :checked="anonymousForm.is_anonymous"
                                    @change="updateAnonymousMode"
                                    class="sr-only peer"
                                    :disabled="anonymousForm.processing"
                                    :aria-label="
                                        anonymousForm.is_anonymous
                                            ? 'Disable anonymous mode'
                                            : 'Enable anonymous mode'
                                    "
                                    :aria-busy="anonymousForm.processing"
                                />
                                <div
                                    :class="[
                                        'w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pressed',
                                        anonymousForm.processing
                                            ? 'opacity-50 cursor-not-allowed'
                                            : 'cursor-pointer',
                                    ]"
                                ></div>
                            </label>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Interest Selection Modal -->
        <InterestSelectionModal
            :show="showInterestModal"
            :available-interests="availableInterests"
            :current-interests="userInterests"
            @close="showInterestModal = false"
        />

        <!-- Language Selection Modal -->
        <LanguageSelectionModal
            :show="showLanguageModal"
            :available-languages="availableLanguages"
            :current-languages="userLanguages"
            @close="showLanguageModal = false"
        />
    </AppLayout>
</template>
