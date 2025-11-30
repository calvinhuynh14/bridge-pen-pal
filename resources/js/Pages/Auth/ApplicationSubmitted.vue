<script setup>
import { computed, onMounted } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import CustomButton from "@/Components/CustomButton.vue";

const props = defineProps({
    isEmailVerified: {
        type: Boolean,
        default: false,
    },
    volunteerStatus: {
        type: String,
        default: null,
    },
    rejectionReason: {
        type: String,
        default: null,
    },
    isVolunteer: {
        type: Boolean,
        default: false,
    },
});

const logoutForm = useForm({});

const logoutAndRedirect = () => {
    logoutForm.post("/logout-to-login");
};

// Check if user just verified email (from query param)
const justVerified = computed(() => {
    if (typeof window !== 'undefined') {
        return new URLSearchParams(window.location.search).get("verified") === "1";
    }
    return false;
});

// Flow indicator steps
const steps = computed(() => {
    return [
        {
            label: "Email Verified",
            completed: props.isEmailVerified,
            icon: props.isEmailVerified ? "check" : "pending",
        },
        {
            label: "Application Approved",
            completed: props.volunteerStatus === "approved",
            icon:
                props.volunteerStatus === "approved"
                    ? "check"
                    : props.volunteerStatus === "rejected"
                      ? "x"
                      : "pending",
        },
    ];
});

// Show success message if just verified
onMounted(() => {
    if (justVerified.value) {
        // Remove query param after showing message
        setTimeout(() => {
            router.get(route("application.submitted"), {}, {
                preserveState: true,
                preserveScroll: true,
            });
        }, 5000);
    }
});
</script>

<template>
    <Head title="Application Submitted" />

    <!-- Main Container -->
    <main
        class="flex flex-col lg:flex-row min-h-screen bg-background items-center justify-center p-2 lg:p-4 gap-4"
        role="main"
    >
        <!-- Hero Section -->
        <section
            class="flex flex-col max-w-7xl bg-background rounded-lg lg:m-8 lg:p-8 lg:gap-8 text-center items-center justify-center"
            aria-label="Application submitted hero section"
        >
            <div class="flex-1">
                <h1
                    class="text-primary text-2xl lg:text-4xl xl:text-6xl"
                >
                    Application Submitted!
                </h1>
                <h2
                    class="text-hover text-lg lg:text-2xl xl:text-4xl mt-2"
                >
                    Thank you for your interest
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
        </section>

        <!-- Application Status Section -->
        <section
            class="flex flex-col mx-2 lg:mx-8 lg:bg-primary lg:w-full justify-center lg:items-center lg:rounded-lg lg:py-16 lg:px-8"
            aria-label="Application status section"
        >
            <!-- Application Status Content -->
            <div class="rounded-lg px-4 lg:px-8 max-w-md mx-auto space-y-6">
                <!-- Success Message (Email Verified) -->
                <div
                    v-if="justVerified"
                    role="status"
                    aria-live="polite"
                    aria-atomic="true"
                    class="bg-green-50 lg:bg-green-100 border border-green-200 rounded-lg p-4 mb-4"
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
                        <span class="text-green-800 font-medium text-sm"
                            >Email verified successfully!</span
                        >
                    </div>
            </div>

                <!-- Success Message -->
                <div class="text-center">
                    <h2
                        class="text-3xl lg:text-4xl xl:text-6xl font-bold text-primary lg:text-white mb-4"
                    >
                        Application Submitted Successfully!
                    </h2>

                    <p class="text-primary lg:text-white text-base lg:text-lg font-medium mb-6">
                        Thank you for your interest in becoming a pen pal
                        volunteer. Your application has been submitted and is
                        currently under review.
                    </p>
                </div>

                <!-- Flow Indicator -->
                <div
                    v-if="isVolunteer"
                    class="bg-light border border-primary/30 rounded-lg p-4 mb-4"
                    role="region"
                    aria-label="Application progress"
                >
                    <h3 class="text-black font-semibold mb-3 text-center">
                        Application Progress
                    </h3>
                    <div class="space-y-3" role="list">
                        <div
                            v-for="(step, index) in steps"
                            :key="index"
                            class="flex items-center"
                            role="listitem"
                            :aria-label="`${step.label}: ${step.completed ? 'Completed' : step.icon === 'x' ? 'Rejected' : 'Pending'}`"
                        >
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                :class="{
                                    'bg-green-500': step.completed,
                                    'bg-yellow-500': !step.completed && step.icon === 'pending',
                                    'bg-red-500': step.icon === 'x',
                                }"
                                role="img"
                                :aria-label="step.completed ? 'Completed' : step.icon === 'x' ? 'Rejected' : 'Pending'"
                            >
                                <svg
                                    v-if="step.icon === 'check'"
                                    class="h-5 w-5 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg
                                    v-else-if="step.icon === 'x'"
                                    class="h-5 w-5 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg
                                    v-else-if="step.label === 'Application Approved'"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="h-5 w-5 text-white"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="h-5 w-5 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <span
                                class="text-black text-sm"
                                :class="{
                                    'font-medium': step.completed,
                                    'opacity-75': !step.completed,
                                }"
                            >
                                {{ step.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Email Verification Notice -->
                <div
                    v-if="isVolunteer && !isEmailVerified"
                    role="alert"
                    aria-live="polite"
                    aria-atomic="true"
                    class="bg-yellow-50 lg:bg-yellow-100 border border-yellow-200 rounded-lg p-4 mb-4"
                >
                    <div class="flex items-center mb-2">
                        <svg
                            class="h-5 w-5 text-yellow-600 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <p class="text-yellow-800 font-medium">
                            Email Verification Required
                        </p>
                    </div>
                    <p class="text-yellow-700 text-sm mb-3">
                        Please verify your email address to continue.
                        Check your inbox for a verification link.
                    </p>
                    <Link 
                        :href="route('verification.notice')"
                        aria-label="Go to email verification page"
                    >
                        <CustomButton
                            text="Verify Email"
                            preset="primary"
                            size="small"
                            class="w-full"
                            ariaLabel="Verify your email address"
                        />
                    </Link>
                </div>

                <!-- Application Status -->
                <div
                    v-if="isVolunteer && isEmailVerified"
                    :role="volunteerStatus === 'rejected' ? 'alert' : 'status'"
                    :aria-live="volunteerStatus === 'rejected' ? 'assertive' : 'polite'"
                    aria-atomic="true"
                    :class="[
                        'border rounded-lg p-4',
                        volunteerStatus === 'rejected'
                            ? 'bg-red-50 lg:bg-red-100 border-red-200'
                            : volunteerStatus === 'approved'
                              ? 'bg-green-50 lg:bg-green-100 border-green-200'
                              : 'bg-yellow-50 lg:bg-yellow-100 border-yellow-200'
                    ]"
                >
                    <div class="flex items-center">
                        <svg
                            v-if="volunteerStatus === 'rejected'"
                            class="h-5 w-5 text-red-600 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <svg
                            v-else-if="volunteerStatus === 'approved'"
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
                        <svg
                            v-else
                            class="h-5 w-5 text-yellow-600 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <span
                            :class="[
                                'font-medium',
                                volunteerStatus === 'rejected'
                                    ? 'text-red-800'
                                    : volunteerStatus === 'approved'
                                      ? 'text-green-800'
                                      : 'text-yellow-800'
                            ]"
                        >
                            {{
                                volunteerStatus === "approved"
                                    ? "Status: Approved"
                                    : volunteerStatus === "rejected"
                                      ? "Status: Rejected"
                                      : "Status: Pending Review"
                            }}
                        </span>
                    </div>
                    <p
                        v-if="isEmailVerified && volunteerStatus === 'rejected' && rejectionReason"
                        class="text-red-700 text-sm mt-3 font-medium"
                    >
                        Reason for rejection:
                    </p>
                    <p
                        v-if="isEmailVerified && volunteerStatus === 'rejected' && rejectionReason"
                        class="text-red-600 text-sm mt-1"
                    >
                        {{ rejectionReason }}
                    </p>
                    <p
                        v-else-if="isEmailVerified && volunteerStatus !== 'approved' && volunteerStatus !== 'rejected'"
                        class="text-yellow-700 text-sm mt-2"
                    >
                        Your email is verified. Your application is awaiting
                        approval from the organization.
                    </p>
                </div>

                <!-- What's Next Section -->
                <div
                    class="bg-blue-50 lg:bg-blue-100 border border-blue-200 rounded-lg p-4 text-left"
                >
                    <h3 class="font-semibold text-blue-900 mb-2">
                        What happens next?
                    </h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>
                            • Your application will be reviewed by the
                            organization
                        </li>
                        <li>
                            • You'll receive an email notification once a
                            decision is made
                        </li>
                        <li>
                            • If approved, you'll be able to log in and start
                            your pen pal journey
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div>
                    <Link 
                        href="/" 
                        class="block mb-6"
                        aria-label="Return to home page"
                    >
                        <CustomButton
                            text="Return to Home"
                            preset="secondary"
                            size="medium"
                            class="w-full"
                            ariaLabel="Return to home page"
                        />
                    </Link>

                    <div class="block">
                        <CustomButton
                            text="Already have an account? Sign in"
                            preset="neutral"
                            size="small"
                            class="w-full"
                            @click="logoutAndRedirect"
                            :isLoading="logoutForm.processing"
                            ariaLabel="Sign in to your account"
                        />
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>
