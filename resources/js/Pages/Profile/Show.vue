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

// Debug: Log initial props
console.log("Profile Show - Initial props:", {
    isAnonymous: props.isAnonymous,
    anonymousName: props.anonymousName,
    isAnonymousType: typeof props.isAnonymous,
});

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

// Debug: Log initial form state
console.log("Profile Show - Initial form state:", {
    is_anonymous: anonymousForm.is_anonymous,
    is_anonymousType: typeof anonymousForm.is_anonymous,
});

// Watch for prop changes and update form
watch(
    () => props.isAnonymous,
    (newValue, oldValue) => {
        console.log("Profile Show - Watcher fired:", {
            oldValue,
            newValue,
            newValueType: typeof newValue,
            booleanValue: Boolean(newValue),
        });
        anonymousForm.is_anonymous = Boolean(newValue);
        console.log("Profile Show - After watcher update:", {
            formValue: anonymousForm.is_anonymous,
        });
    },
    { immediate: true }
);

// Update anonymous mode
const updateAnonymousMode = (event) => {
    const newValue = event.target.checked;

    console.log("Profile Show - updateAnonymousMode called:", {
        newValue,
        currentFormValue: anonymousForm.is_anonymous,
        currentPropValue: props.isAnonymous,
    });

    // Update form value
    anonymousForm.is_anonymous = newValue;

    console.log("Profile Show - Form updated to:", anonymousForm.is_anonymous);

    anonymousForm.put(route("profile.anonymous.update"), {
        preserveScroll: true,
        onSuccess: () => {
            console.log("Profile Show - Update successful, reloading props...");
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Avatar Selection Section -->
                <div
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6 mb-8"
                >
                    <AvatarSelector
                        :available-avatars="availableAvatars"
                        :current-avatar="currentAvatar"
                        :user-name="user?.name || ''"
                    />
                </div>

                <!-- Profile Information Card -->
                <div
                    class="bg-primary overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6 mb-8"
                >
                    <h3
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Profile Information
                    </h3>

                    <form @submit.prevent="updateProfile">
                        <!-- Success Message -->
                        <div
                            v-if="profileUpdateSuccess"
                            class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg"
                        >
                            <p class="text-base text-green-600 font-medium">
                                Profile updated successfully!
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name Field (Editable for volunteers only, read-only for residents, hidden for admins) -->
                            <div v-if="!isAdmin">
                                <label
                                    for="name"
                                    class="block text-lg lg:text-xl font-medium text-white mb-2"
                                >
                                    Name
                                </label>
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
                                />
                                <InputError
                                    class="mt-2"
                                    :message="profileForm.errors.name"
                                />
                            </div>

                            <!-- Email Field (Read-only for volunteers and admins) -->
                            <div v-if="isVolunteer || isAdmin">
                                <label
                                    for="email"
                                    class="block text-lg lg:text-xl font-medium text-white mb-2"
                                >
                                    Email Address
                                </label>
                                <TextInput
                                    id="email"
                                    :value="user?.email || ''"
                                    type="email"
                                    class="mt-2 block w-full text-base bg-gray-100 cursor-not-allowed opacity-75"
                                    disabled
                                    readonly
                                />
                                <p class="mt-1 text-base text-white/80">
                                    Email address cannot be changed.
                                </p>
                            </div>

                            <!-- Organization Field (Editable for admins, read-only for volunteers and residents) -->
                            <div v-if="isAdmin">
                                <label
                                    for="organization_name"
                                    class="block text-lg lg:text-xl font-medium text-white mb-2"
                                >
                                    Organization Name
                                </label>
                                <TextInput
                                    id="organization_name"
                                    v-model="profileForm.organization_name"
                                    type="text"
                                    class="mt-2 block w-full text-lg"
                                    required
                                />
                                <InputError
                                    class="mt-2"
                                    :message="
                                        profileForm.errors.organization_name
                                    "
                                />
                            </div>

                            <!-- Organization Field (Read-only for volunteers and residents) -->
                            <div v-if="isVolunteer || isResident">
                                <label
                                    for="organization"
                                    class="block text-lg lg:text-xl font-medium text-white mb-2"
                                >
                                    Organization
                                </label>
                                <TextInput
                                    id="organization"
                                    :value="organizationName"
                                    type="text"
                                    class="mt-2 block w-full text-base bg-gray-100 cursor-not-allowed opacity-75"
                                    disabled
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
                            />
                        </div>
                    </form>
                </div>

                <!-- Account Settings Card (Volunteers and Admins) -->
                <div
                    v-if="isVolunteer || isAdmin"
                    class="bg-primary overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6 mb-8"
                >
                    <h3
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Account Settings
                    </h3>

                    <div class="space-y-6">
                        <!-- Change Password Section -->
                        <div>
                            <h4
                                class="text-base lg:text-lg font-medium text-white mb-2"
                            >
                                Change Password
                            </h4>
                            <p class="text-lg text-white mb-4">
                                Update your account password. Your password must
                                include:
                            </p>
                            <ul
                                class="text-lg text-white/90 mb-4 list-disc list-inside space-y-1"
                            >
                                <li>At least 8 characters</li>
                                <li>At least one capital letter</li>
                                <li>At least one number</li>
                                <li>At least one special character</li>
                            </ul>
                            <form @submit.prevent="updatePassword">
                                <!-- Success Message -->
                                <div
                                    v-if="passwordUpdateSuccess"
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
                                        <label
                                            for="current_password"
                                            class="block text-lg lg:text-xl font-medium text-white mb-2"
                                        >
                                            Current Password
                                        </label>
                                        <TextInput
                                            id="current_password"
                                            v-model="
                                                passwordForm.current_password
                                            "
                                            type="password"
                                            class="mt-2 block w-full text-lg"
                                            required
                                            autocomplete="current-password"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="password"
                                            class="block text-lg lg:text-xl font-medium text-white mb-2"
                                        >
                                            New Password
                                        </label>
                                        <TextInput
                                            id="password"
                                            v-model="passwordForm.password"
                                            type="password"
                                            class="mt-2 block w-full text-lg"
                                            required
                                            autocomplete="new-password"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="password_confirmation"
                                            class="block text-lg lg:text-xl font-medium text-white mb-2"
                                        >
                                            Confirm New Password
                                        </label>
                                        <TextInput
                                            id="password_confirmation"
                                            v-model="
                                                passwordForm.password_confirmation
                                            "
                                            type="password"
                                            class="mt-2 block w-full text-lg"
                                            required
                                            autocomplete="new-password"
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
                                        />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Interests Section -->
                <div
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6 mb-8"
                >
                    <h3
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Interests
                    </h3>
                    <div class="space-y-4">
                        <p class="text-lg text-white mb-4">
                            Select your interests to help match you with pen
                            pals
                        </p>
                        <div
                            v-if="selectedInterestsNames.length > 0"
                            class="flex flex-wrap gap-2 mb-4"
                        >
                            <span
                                v-for="(
                                    interestName, index
                                ) in selectedInterestsNames"
                                :key="index"
                                class="px-3 py-1 bg-white text-black rounded-full text-lg font-medium"
                            >
                                {{ interestName }}
                            </span>
                        </div>
                        <div v-else class="mb-4">
                            <p class="text-lg text-white/80 italic">
                                No interests selected yet
                            </p>
                        </div>
                        <CustomButton
                            text="Add/Remove Interests"
                            preset="neutral"
                            size="small"
                            @click="showInterestModal = true"
                        />
                    </div>
                </div>

                <!-- Languages Section -->
                <div
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6 mb-8"
                >
                    <h3
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Languages
                    </h3>
                    <div class="space-y-4">
                        <p class="text-lg text-white mb-4">
                            Select languages you speak to help match you with
                            pen pals
                        </p>
                        <div
                            v-if="selectedLanguagesNames.length > 0"
                            class="flex flex-wrap gap-2 mb-4"
                        >
                            <span
                                v-for="(
                                    languageName, index
                                ) in selectedLanguagesNames"
                                :key="index"
                                class="px-3 py-1 bg-white text-black rounded-full text-lg font-medium"
                            >
                                {{ languageName }}
                            </span>
                        </div>
                        <div v-else class="mb-4">
                            <p class="text-lg text-white/80 italic">
                                No languages selected yet
                            </p>
                        </div>
                        <CustomButton
                            text="Add/Remove Languages"
                            preset="neutral"
                            size="small"
                            @click="showLanguageModal = true"
                        />
                    </div>
                </div>

                <!-- Anonymous Mode Section -->
                <div
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6"
                >
                    <h3
                        class="text-2xl lg:text-3xl font-semibold text-white mb-4"
                    >
                        Privacy Settings
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-medium text-white">
                                    Anonymous Mode
                                </h4>
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
                                    class="text-base text-white/80 mt-2 italic"
                                >
                                    Your anonymous name: {{ anonymousName }}
                                </p>
                            </div>
                            <label
                                class="relative inline-flex items-center cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    :checked="anonymousForm.is_anonymous"
                                    @change="updateAnonymousMode"
                                    class="sr-only peer"
                                    :disabled="anonymousForm.processing"
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
                </div>
            </div>
        </div>

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
