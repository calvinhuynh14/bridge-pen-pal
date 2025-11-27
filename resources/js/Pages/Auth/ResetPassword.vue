<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import SimpleHeader from '@/Components/SimpleHeader.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

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
                    Reset Password
                </h1>
                <h2
                    class="hidden lg:block text-hover text-lg lg:text-2xl xl:text-4xl"
                >
                    Choose a new password
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
                    Set New Password
                </h1>
            </div>

            <!-- Password Reset Form -->
            <div class="rounded-lg px-4 lg:px-4 max-w-md mx-auto space-y-4">
                <!-- "Set New Password" heading for mobile -->
                <div class="flex lg:hidden mb-2 text-center">
                    <h1 class="text-primary text-2xl font-bold w-full">
                        Set New Password
                    </h1>
                </div>

                <!-- Password Requirements Description -->
                <div class="mb-4 text-center">
                    <p class="text-gray-600 lg:text-black text-base lg:text-lg">
                        Your password must include at least one capital letter,
                        one number, and one special symbol.
                    </p>
                </div>

                <!-- Error Message -->
                <div
                    v-if="Object.keys(form.errors).length > 0"
                    class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg"
                >
                    <p class="text-sm text-red-600 font-medium">
                        {{
                            form.errors.password ||
                            form.errors.email ||
                            form.errors.token ||
                            'Please check your information and try again.'
                        }}
                    </p>
                </div>

                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <!-- Email Field (read-only) -->
                        <div>
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-2 block w-full border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed opacity-75"
                                required
                                readonly
                                disabled
                                autocomplete="email"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Password Field -->
                        <div>
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                required
                                autofocus
                                autocomplete="new-password"
                                placeholder="Enter your new password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <!-- Confirm Password Field -->
                        <div>
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm your new password"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.password_confirmation"
                            />
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
                                    ? 'Resetting Password...'
                                    : 'Reset Password'
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
