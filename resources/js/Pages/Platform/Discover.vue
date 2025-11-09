<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

// Get props
const props = defineProps({
    openLetters: {
        type: Array,
        default: () => [],
    },
    letterCount: {
        type: Number,
        default: 0,
    },
});

// Get current user from page props
const page = usePage();
const user = computed(() => page.props.auth.user);

// Helper functions to check user type
const isVolunteer = computed(() => user.value?.user_type === "volunteer");
const isResident = computed(() => user.value?.user_type === "resident");

// Get user type display
const getUserType = () => {
    if (isVolunteer.value) return "Volunteer";
    if (isResident.value) return "Resident";
    return "User";
};

// Format date helper (date only, no time)
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

// Truncate content helper
const truncateContent = (content, maxLength = 150) => {
    if (!content) return "";
    if (content.length <= maxLength) return content;
    return content.substring(0, maxLength).trim() + "...";
};

// Claim letter function
const claimingLetterId = ref(null);
const claimLetter = (letterId) => {
    if (claimingLetterId.value) return; // Prevent double-click

    claimingLetterId.value = letterId;

    router.post(
        `/platform/letters/${letterId}/claim`,
        {},
        {
            onSuccess: () => {
                // Refresh the page to update the letter list
                router.reload({ only: ["openLetters", "letterCount"] });
                claimingLetterId.value = null;
            },
            onError: (errors) => {
                alert(errors.message || "Failed to claim letter");
                claimingLetterId.value = null;
            },
        }
    );
};

// Report letter function
const reportingLetterId = ref(null);
const showReportModal = ref(false);
const selectedLetter = ref(null);
const reportReason = ref("");

const openReportModal = (letter) => {
    selectedLetter.value = letter;
    reportReason.value = "";
    showReportModal.value = true;
};

const closeReportModal = () => {
    showReportModal.value = false;
    selectedLetter.value = null;
    reportReason.value = "";
    reportingLetterId.value = null;
};

const submitReport = () => {
    if (!reportReason.value.trim()) {
        alert("Please provide a reason for reporting this letter.");
        return;
    }

    if (reportingLetterId.value) return; // Prevent double-submit

    reportingLetterId.value = selectedLetter.value.id;

    router.post(
        `/platform/letters/${selectedLetter.value.id}/report`,
        {
            reason: reportReason.value,
        },
        {
            onSuccess: () => {
                alert(
                    "Letter reported successfully. Thank you for helping keep our community safe."
                );
                closeReportModal();
                // Optionally refresh the page to remove the reported letter
                router.reload({ only: ["openLetters", "letterCount"] });
            },
            onError: (errors) => {
                alert(errors.message || "Failed to report letter");
                reportingLetterId.value = null;
            },
        }
    );
};
</script>

<template>
    <Head :title="`${getUserType()} - Discover`" />

    <AppLayout :title="`${getUserType()} - Discover`">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Discover
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-pressed mb-2">
                        Open Letters
                    </h1>
                    <p v-if="letterCount > 0" class="text-sm text-gray-600">
                        {{ letterCount }} open
                        {{ letterCount === 1 ? "letter" : "letters" }} available
                    </p>
                </div>

                <!-- Open Letters Grid Container with Primary Background -->
                <div
                    v-if="openLetters.length > 0"
                    class="bg-primary rounded-lg"
                    style="padding: 2px"
                >
                    <div class="p-4 sm:p-6">
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 max-w-sm md:max-w-none mx-auto md:mx-0"
                        >
                            <!-- Letter Card (Paper-like design) -->
                            <div
                                v-for="letter in openLetters"
                                :key="letter.id"
                                class="border-2 border-gray-300 rounded-sm p-4 sm:p-6 shadow-md hover:shadow-lg transition-shadow relative flex flex-col"
                                style="
                                    background-color: #ffffff;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1),
                                        0 1px 2px rgba(0, 0, 0, 0.06);
                                "
                            >
                                <!-- Avatar and Name -->
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-10 h-10 sm:w-12 sm:h-12 bg-primary rounded-full flex items-center justify-center border-2 border-pressed flex-shrink-0"
                                    >
                                        <span
                                            class="text-black text-lg sm:text-xl font-medium"
                                        >
                                            {{
                                                letter.sender_name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="font-semibold text-pressed text-lg sm:text-xl truncate"
                                        >
                                            {{ letter.sender_name }}
                                        </p>
                                        <p
                                            class="text-sm sm:text-base text-gray-500"
                                        >
                                            {{ formatDate(letter.sent_at) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Letter Content Preview -->
                                <div
                                    class="flex-1 mb-4 min-h-[100px] sm:min-h-[120px]"
                                >
                                    <p
                                        class="text-gray-700 text-base sm:text-lg leading-relaxed"
                                        style="
                                            display: -webkit-box;
                                            -webkit-line-clamp: 4;
                                            -webkit-box-orient: vertical;
                                            overflow: hidden;
                                        "
                                    >
                                        {{
                                            truncateContent(letter.content, 150)
                                        }}
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div
                                    class="flex items-center justify-between gap-2 mt-auto pt-4 border-t border-gray-200"
                                >
                                    <button
                                        @click="claimLetter(letter.id)"
                                        :disabled="
                                            claimingLetterId === letter.id
                                        "
                                        class="px-4 py-2.5 sm:py-3 bg-primary hover:bg-pressed text-black rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-base sm:text-lg flex-1 flex items-center justify-center gap-2"
                                    >
                                        <span
                                            v-if="
                                                claimingLetterId === letter.id
                                            "
                                        >
                                            Replying...
                                        </span>
                                        <span
                                            v-else
                                            class="flex items-center gap-2"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                                class="size-5 sm:size-6"
                                            >
                                                <path
                                                    d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z"
                                                />
                                            </svg>
                                            Reply
                                        </span>
                                    </button>
                                    <button
                                        @click="openReportModal(letter)"
                                        class="px-3 py-2.5 sm:py-3 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg font-medium transition-colors text-base sm:text-lg"
                                        title="Report this letter"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                            class="size-5 sm:size-6"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M3 2.25a.75.75 0 0 1 .75.75v.54l1.838-.46a9.75 9.75 0 0 1 6.725.738l.108.054A8.25 8.25 0 0 0 18 4.524l3.11-.732a.75.75 0 0 1 .917.81 47.784 47.784 0 0 0 .005 10.337.75.75 0 0 1-.574.812l-3.114.733a9.75 9.75 0 0 1-6.594-.77l-.108-.054a8.25 8.25 0 0 0-5.69-.625l-2.202.55V21a.75.75 0 0 1-1.5 0V3A.75.75 0 0 1 3 2.25Z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="bg-white border-2 border-primary rounded-lg p-12 text-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-16 mx-auto text-gray-400 mb-4"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                        />
                    </svg>
                    <h3 class="text-lg font-semibold text-black mb-2">
                        No Open Letters Available
                    </h3>
                    <p class="text-gray-600">
                        There are currently no open letters to discover. Check
                        back later!
                    </p>
                </div>
            </div>
        </div>

        <!-- Report Modal -->
        <div
            v-if="showReportModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeReportModal"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-md w-full p-6"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-black">Report Letter</h3>
                    <button
                        @click="closeReportModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-6 h-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedLetter" class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">
                        Reporting letter from
                        <span class="font-semibold text-pressed">{{
                            selectedLetter.sender_name
                        }}</span>
                    </p>
                    <div
                        class="bg-gray-50 border-2 border-primary rounded-lg p-3 mb-4"
                    >
                        <p
                            class="text-sm text-gray-700"
                            style="
                                display: -webkit-box;
                                -webkit-line-clamp: 3;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                            "
                        >
                            {{ selectedLetter.content }}
                        </p>
                    </div>
                </div>

                <div class="mb-4">
                    <label
                        for="reportReason"
                        class="block text-sm font-medium text-black mb-2"
                    >
                        Reason for reporting
                    </label>
                    <textarea
                        id="reportReason"
                        v-model="reportReason"
                        rows="4"
                        class="w-full px-4 py-2 border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                        placeholder="Please describe why you are reporting this letter..."
                    ></textarea>
                </div>

                <div class="flex gap-3 justify-end">
                    <button
                        @click="closeReportModal"
                        class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitReport"
                        :disabled="
                            !reportReason.trim() ||
                            reportingLetterId === selectedLetter?.id
                        "
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="reportingLetterId === selectedLetter?.id">
                            Submitting...
                        </span>
                        <span v-else> Submit Report </span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
