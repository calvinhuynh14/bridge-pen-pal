<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import CustomButton from "@/Components/CustomButton.vue";
import AvatarSelector from "@/Components/AvatarSelector.vue";

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
        formData = { name: profileForm.name, email: profileForm.email };
    } else {
        return; // Residents cannot update
    }

    profileForm
        .transform(() => formData)
        .put(route("user-profile-information.update"), {
            preserveScroll: true,
            onSuccess: () => {
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
        },
    });
};

// Delete account (volunteers only)
const deleteAccount = () => {
    if (
        confirm(
            "Are you sure you want to delete your account? This action cannot be undone."
        )
    ) {
        // TODO: Implement account deletion
        console.log("Delete account - placeholder");
    }
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
            <h2 class="font-semibold text-xl text-primary leading-tight">
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
                    <h3 class="text-xl lg:text-2xl font-semibold text-white mb-4">
                        Profile Information
                    </h3>

                    <form @submit.prevent="updateProfile">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name Field (Editable for volunteers only, read-only for residents, hidden for admins) -->
                            <div v-if="!isAdmin">
                                <label
                                    for="name"
                                    class="block text-base lg:text-lg font-medium text-white mb-2"
                                >
                                    Name
                                </label>
                                <TextInput
                                    id="name"
                                    v-model="profileForm.name"
                                    type="text"
                                    class="mt-2 block w-full text-base"
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

                            <!-- Email Field (Editable for volunteers, read-only for admins) -->
                            <div v-if="isVolunteer || isAdmin">
                                <label
                                    for="email"
                                    class="block text-base lg:text-lg font-medium text-white mb-2"
                                >
                                    Email Address
                                </label>
                                <TextInput
                                    id="email"
                                    :value="user?.email || ''"
                                    type="email"
                                    class="mt-2 block w-full text-base"
                                    :class="isAdmin ? 'bg-gray-100 cursor-not-allowed opacity-75' : ''"
                                    :disabled="isAdmin"
                                    :readonly="isAdmin"
                                />
                                <p v-if="isAdmin" class="mt-1 text-sm text-white/80">
                                    Email address cannot be changed.
                                </p>
                                <InputError
                                    v-if="isVolunteer"
                                    class="mt-2"
                                    :message="profileForm.errors.email"
                                />
                            </div>

                            <!-- Organization Field (Editable for admins, read-only for volunteers and residents) -->
                            <div v-if="isAdmin">
                                <label
                                    for="organization_name"
                                    class="block text-base lg:text-lg font-medium text-white mb-2"
                                >
                                    Organization Name
                                </label>
                                <TextInput
                                    id="organization_name"
                                    v-model="profileForm.organization_name"
                                    type="text"
                                    class="mt-2 block w-full text-base"
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
                                    class="block text-base lg:text-lg font-medium text-white mb-2"
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
                    <h3 class="text-xl lg:text-2xl font-semibold text-white mb-4">
                        Account Settings
                    </h3>

                    <div class="space-y-6">
                        <!-- Change Password Section -->
                        <div>
                            <h4 class="text-base lg:text-lg font-medium text-white mb-2">
                                Change Password
                            </h4>
                            <p class="text-base text-white mb-4">
                                Update your account password. Your password must
                                include:
                            </p>
                            <ul class="text-base text-white/90 mb-4 list-disc list-inside space-y-1">
                                <li>At least 8 characters</li>
                                <li>At least one capital letter</li>
                                <li>At least one number</li>
                                <li>At least one special character</li>
                            </ul>
                            <form @submit.prevent="updatePassword">
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            for="current_password"
                                            class="block text-base lg:text-lg font-medium text-white mb-2"
                                        >
                                            Current Password
                                        </label>
                                        <TextInput
                                            id="current_password"
                                            v-model="
                                                passwordForm.current_password
                                            "
                                            type="password"
                                            class="mt-2 block w-full text-base"
                                            required
                                            autocomplete="current-password"
                                        />
                                        <InputError
                                            class="mt-2"
                                            :message="
                                                passwordForm.errors
                                                    .current_password
                                            "
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="password"
                                            class="block text-base lg:text-lg font-medium text-white mb-2"
                                        >
                                            New Password
                                        </label>
                                        <TextInput
                                            id="password"
                                            v-model="passwordForm.password"
                                            type="password"
                                            class="mt-2 block w-full text-base"
                                            required
                                            autocomplete="new-password"
                                        />
                                        <InputError
                                            class="mt-2"
                                            :message="
                                                passwordForm.errors.password
                                            "
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="password_confirmation"
                                            class="block text-base lg:text-lg font-medium text-white mb-2"
                                        >
                                            Confirm New Password
                                        </label>
                                        <TextInput
                                            id="password_confirmation"
                                            v-model="
                                                passwordForm.password_confirmation
                                            "
                                            type="password"
                                            class="mt-2 block w-full text-base"
                                            required
                                            autocomplete="new-password"
                                        />
                                        <InputError
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
                                        />
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Delete Account Section (Volunteers only - admins cannot delete their account) -->
                        <div
                            v-if="isVolunteer"
                            class="pt-6 border-t-2 border-white"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-white">
                                        Delete Account
                                    </h4>
                                    <p class="text-sm text-white mt-1">
                                        Permanently delete your account and all
                                        data
                                    </p>
                                </div>
                                <CustomButton
                                    text="Delete Account"
                                    preset="neutral"
                                    size="small"
                                    @click="deleteAccount"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interests Section (Placeholder) -->
                <div
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6 mb-8"
                >
                    <h3 class="text-lg font-semibold text-white mb-4">
                        Interests
                    </h3>
                    <div class="space-y-4">
                        <p class="text-sm text-white mb-4">
                            Interest management coming soon
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="px-3 py-1 bg-background text-primary rounded-full text-sm"
                            >
                                Interest 1
                            </span>
                            <span
                                class="px-3 py-1 bg-background text-primary rounded-full text-sm"
                            >
                                Interest 2
                            </span>
                            <span
                                class="px-3 py-1 bg-background text-primary rounded-full text-sm"
                            >
                                Interest 3
                            </span>
                        </div>
                        <CustomButton
                            text="Add/Remove Interests"
                            preset="neutral"
                            size="small"
                            disabled
                        />
                    </div>
                </div>

                <!-- Languages Section (Placeholder) -->
                <div
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6 mb-8"
                >
                    <h3 class="text-lg font-semibold text-white mb-4">
                        Languages
                    </h3>
                    <div class="space-y-4">
                        <p class="text-sm text-white mb-4">
                            Language management coming soon
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="px-3 py-1 bg-background text-primary rounded-full text-sm"
                            >
                                English
                            </span>
                            <span
                                class="px-3 py-1 bg-background text-primary rounded-full text-sm"
                            >
                                French
                            </span>
                        </div>
                        <CustomButton
                            text="Add/Remove Languages"
                            preset="neutral"
                            size="small"
                            disabled
                        />
                    </div>
                </div>

                <!-- Anonymous Mode Section (Placeholder) -->
                <div
                    v-if="isResident || isVolunteer"
                    class="bg-primary overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6"
                >
                    <h3 class="text-lg font-semibold text-white mb-4">
                        Privacy Settings
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-white">
                                    Anonymous Mode
                                </h4>
                                <p class="text-sm text-white mt-1">
                                    Hide your identity in communications (coming
                                    soon)
                                </p>
                            </div>
                            <label
                                class="relative inline-flex items-center cursor-not-allowed opacity-50"
                            >
                                <input
                                    type="checkbox"
                                    class="sr-only peer"
                                    disabled
                                />
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"
                                ></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
