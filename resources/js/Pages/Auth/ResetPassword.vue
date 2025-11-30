<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import CustomButton from '@/Components/CustomButton.vue';
import SimpleHeader from '@/Components/SimpleHeader.vue';

const props = defineProps({
    email: String,
    token: String,
    status: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});


// Helper function to get error messages as an array
const getErrorMessages = () => {
    const errorMessages = [];
    
    // Check for password errors - always show requirements
    if (form.errors.password) {
        const passwordErrors = Array.isArray(form.errors.password)
            ? form.errors.password
            : [form.errors.password];
        
        // Check for special cases first
        let hasSpecialError = false;
        passwordErrors.forEach((passwordError) => {
            const errorLower = passwordError.toLowerCase();
            
            // Check for uncompromised password error (this requires server-side check)
            // Laravel's Password rule uses various error messages for uncompromised
            if (errorLower.includes('uncompromised') || 
                errorLower.includes('compromised') || 
                errorLower.includes('data breach') ||
                errorLower.includes('been found in a data leak') ||
                errorLower.includes('appeared in a data breach')) {
                errorMessages.push('This password has been found in a data breach. Please choose a different password.');
                hasSpecialError = true;
            } else if (errorLower.includes('required')) {
                errorMessages.push('Password is required.');
                hasSpecialError = true;
            } else {
                // If we don't recognize the error, show it along with requirements
                // This helps debug what Laravel is actually returning
                console.log('Password error:', passwordError);
            }
        });
        
        // If it's not a special error, show password requirements
        if (!hasSpecialError) {
            errorMessages.push('Password must contain:');
            errorMessages.push('• At least 8 characters');
            errorMessages.push('• At least one capital letter');
            errorMessages.push('• At least one lowercase letter');
            errorMessages.push('• At least one number');
            errorMessages.push('• At least one special character');
        }
    }
    
    // Check for password confirmation errors
    if (form.errors.password_confirmation) {
        const confirmErrors = Array.isArray(form.errors.password_confirmation)
            ? form.errors.password_confirmation
            : [form.errors.password_confirmation];
        
        confirmErrors.forEach((confirmError) => {
            if (confirmError.includes('match') || confirmError.includes('same')) {
                errorMessages.push('Password confirmation does not match.');
            } else {
                errorMessages.push(confirmError);
            }
        });
    }
    
    // Check for other errors
    if (form.errors.email) {
        const emailErrors = Array.isArray(form.errors.email) ? form.errors.email : [form.errors.email];
        emailErrors.forEach((error) => errorMessages.push(error));
    }
    
    if (form.errors.token) {
        const tokenErrors = Array.isArray(form.errors.token) ? form.errors.token : [form.errors.token];
        tokenErrors.forEach((error) => errorMessages.push(error));
    }
    
    return errorMessages;
};

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password" />

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
            aria-label="Reset password hero section"
        >
            <div class="flex-1">
                <h1
                    class="text-primary text-2xl lg:text-4xl xl:text-6xl font-bold"
                >
                    Reset Password
                </h1>
                <h2
                    class="text-hover text-lg lg:text-2xl xl:text-4xl mt-2"
                >
                    Choose a new password
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

                <!-- Password Requirements Description -->
                <div class="mb-4 text-center">
                    <p class="text-primary lg:text-white text-base lg:text-lg font-medium">
                        Your password must include at least one capital letter,
                        one number, and one special symbol.
                    </p>
                </div>

                <!-- Error Message -->
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

                <form 
                    @submit.prevent="submit"
                    aria-label="Reset password form"
                >
                    <div class="space-y-4">
                        <!-- Email Field (read-only) -->
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
                                class="mt-2 block w-full max-w-md mx-auto border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed opacity-75"
                                :required="false"
                                readonly
                                disabled
                                autocomplete="email"
                                aria-readonly="true"
                            />
                        </div>

                        <!-- Password Field -->
                        <div>
                            <InputLabel
                                for="password"
                                value="New Password"
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-2 block w-full max-w-md mx-auto border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="form.errors.password ? `password-error` : null"
                                autofocus
                                autocomplete="new-password"
                                placeholder="Enter your new password"
                            />
                            <p 
                                id="password-requirements"
                                class="mt-1 text-sm font-medium text-primary lg:text-white"
                            >
                                Password must be at least 8 characters and include uppercase, lowercase, number, and special character.
                            </p>
                            <InputError
                                id="password-error"
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <!-- Confirm Password Field -->
                        <div>
                            <InputLabel
                                for="password_confirmation"
                                value="Confirm New Password"
                                size="base"
                                class="text-primary lg:text-white"
                            />
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-2 block w-full max-w-md mx-auto border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                :required="true"
                                :errorId="form.errors.password_confirmation ? `password_confirmation-error` : null"
                                autocomplete="new-password"
                                placeholder="Confirm your new password"
                            />
                            <InputError
                                id="password_confirmation-error"
                                class="mt-2"
                                :message="form.errors.password_confirmation"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <CustomButton
                            text="Reset Password"
                            preset="neutral"
                            size="medium"
                            :isLoading="form.processing"
                            ariaLabel="Reset password"
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
