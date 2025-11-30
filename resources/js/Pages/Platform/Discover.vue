<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import LetterCard from "@/Components/LetterCard.vue";
import LetterViewModal from "@/Components/LetterViewModal.vue";
import ReportModal from "@/Components/ReportModal.vue";
import Avatar from "@/Components/Avatar.vue";
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
    storyOfTheWeek: {
        type: Object,
        default: () => null,
    },
});

// Get current user from page props
const page = usePage();
const user = computed(() => page.props.auth.user);

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(4); // Show 4 letters per page

// Computed pagination properties
const totalPages = computed(() =>
    Math.ceil(props.openLetters.length / itemsPerPage.value)
);

const paginatedLetters = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.openLetters.slice(start, end);
});

// Pagination methods
const goToPage = (pageNum) => {
    if (pageNum >= 1 && pageNum <= totalPages.value) {
        currentPage.value = pageNum;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        goToPage(currentPage.value + 1);
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        goToPage(currentPage.value - 1);
    }
};

// Helper functions to check user type
const isVolunteer = computed(() => user.value?.user_type === "volunteer");
const isResident = computed(() => user.value?.user_type === "resident");

// Get user type display
const getUserType = () => {
    if (isVolunteer.value) return "Volunteer";
    if (isResident.value) return "Resident";
    return "User";
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

// View letter modal
const showViewModal = ref(false);
const viewingLetter = ref(null);

// Report letter function
const showReportModal = ref(false);
const selectedLetter = ref(null);

const openReportModal = (letter) => {
    selectedLetter.value = letter;
    showReportModal.value = true;
    // Close view modal if it's open
    if (showViewModal.value) {
        showViewModal.value = false;
    }
};

// Handle claim event from LetterCard component
const handleClaim = (letterId) => {
    claimLetter(letterId);
};

// Handle report event from LetterCard component
const handleReport = (letter) => {
    openReportModal(letter);
};

// Handle view event from LetterCard component
const handleView = (letter) => {
    viewingLetter.value = letter;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    viewingLetter.value = null;
};

// Handle reply redirect
const handleReply = (letterId) => {
    router.visit(`/platform/write?letterId=${letterId}`);
};

const closeReportModal = () => {
    showReportModal.value = false;
    selectedLetter.value = null;
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

        <div class="py-2">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Story of the Week Section -->
                <section 
                    v-if="storyOfTheWeek" 
                    class="mb-2"
                    aria-label="Story of the week"
                >
                    <h2
                        class="text-xl sm:text-2xl lg:text-3xl font-bold text-primary mb-1"
                    >
                        Story of the Week
                    </h2>
                    <div
                        class="bg-primary rounded-lg p-4 sm:p-6 md:p-8 lg:p-10"
                    >
                        <div
                            class="flex flex-col md:flex-row items-center md:items-start justify-center md:justify-start gap-4 sm:gap-6 md:gap-8"
                        >
                            <!-- Profile Picture and Name -->
                            <div
                                class="flex-shrink-0 flex flex-col items-center gap-3 sm:gap-4"
                            >
                                <Avatar
                                    :src="storyOfTheWeek.profile_photo_url"
                                    :name="storyOfTheWeek.name"
                                    size="custom"
                                    custom-size="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40"
                                    custom-text-size="text-3xl sm:text-4xl md:text-5xl"
                                    border-color="pressed"
                                    border-width="4"
                                    text-color="white"
                                />
                                <h3
                                    class="text-xl sm:text-2xl md:text-3xl font-bold text-white text-center"
                                >
                                    {{ storyOfTheWeek.name }}
                                </h3>
                            </div>

                            <!-- Bio -->
                            <div
                                class="flex-1 min-w-0 flex flex-col items-center md:items-start"
                            >
                                <div
                                    class="text-sm sm:text-base md:text-lg text-white leading-relaxed space-y-3 text-center md:text-left"
                                >
                                    <p
                                        v-for="(
                                            paragraph, index
                                        ) in storyOfTheWeek.bio?.split('\n') ||
                                        []"
                                        :key="index"
                                    >
                                        {{ paragraph }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Open Letters Section -->
                <section aria-label="Open letters">
                    <!-- Page Header -->
                    <div class="mb-2">
                        <h1
                            class="text-xl sm:text-2xl lg:text-3xl font-bold text-primary mb-1"
                        >
                            Open Letters
                        </h1>
                        <p 
                            v-if="letterCount > 0" 
                            class="text-sm text-gray-600"
                            role="status"
                            aria-live="polite"
                        >
                            {{ letterCount }} open
                            {{ letterCount === 1 ? "letter" : "letters" }}
                            available
                        </p>
                    </div>

                    <!-- Open Letters Grid Container -->
                    <div
                        v-if="openLetters.length > 0"
                        class="bg-primary rounded-lg"
                        style="padding: 2px"
                    >
                    <div
                        class="p-2 sm:p-4 md:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 items-start justify-items-center"
                    >
                        <LetterCard
                            v-for="letter in paginatedLetters"
                            :key="letter.id"
                            :letter="letter"
                            :is-claiming="claimingLetterId === letter.id"
                            @claim="handleClaim"
                            @report="handleReport"
                            @view="handleView"
                            @reply="handleReply"
                        />
                    </div>

                    <!-- Pagination Controls -->
                    <nav
                        v-if="totalPages > 1"
                        class="mt-6 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg"
                        aria-label="Open letters pagination"
                    >
                        <!-- Simple pagination for mobile -->
                        <div
                            class="flex justify-between items-center lg:hidden"
                        >
                            <button
                                v-if="currentPage > 1"
                                @click="prevPage"
                                class="bg-primary hover:bg-pressed text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                aria-label="Go to previous page"
                            >
                                Previous
                            </button>
                            <span 
                                class="text-sm text-black font-medium"
                                aria-current="page"
                            >
                                Page {{ currentPage }} of {{ totalPages }}
                            </span>
                            <button
                                v-if="currentPage < totalPages"
                                @click="nextPage"
                                class="bg-primary hover:bg-pressed text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                aria-label="Go to next page"
                            >
                                Next
                            </button>
                        </div>

                        <!-- Full pagination for desktop -->
                        <div
                            class="hidden lg:flex lg:items-center lg:justify-between"
                        >
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{
                                        (currentPage - 1) * itemsPerPage + 1
                                    }}</span>
                                    to
                                    <span class="font-medium">{{
                                        Math.min(
                                            currentPage * itemsPerPage,
                                            openLetters.length
                                        )
                                    }}</span>
                                    of
                                    <span class="font-medium">{{
                                        openLetters.length
                                    }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <div
                                    class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px"
                                    role="group"
                                    aria-label="Pagination"
                                >
                                    <!-- Previous button -->
                                    <button
                                        v-if="currentPage > 1"
                                        @click="prevPage"
                                        class="relative inline-flex items-center px-3 py-2 rounded-l-lg border-2 border-primary bg-white text-sm font-medium text-primary hover:bg-pressed hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                        aria-label="Go to previous page"
                                    >
                                        <span class="sr-only">Previous</span>
                                        <svg
                                            class="h-4 w-4"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>

                                    <!-- Page numbers -->
                                    <template
                                        v-for="page in totalPages"
                                        :key="page"
                                    >
                                        <button
                                            v-if="page === currentPage"
                                            @click="goToPage(page)"
                                            class="relative inline-flex items-center px-4 py-2 border-2 border-primary bg-primary text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                                            :aria-label="`Current page, page ${page}`"
                                            aria-current="page"
                                        >
                                            {{ page }}
                                        </button>
                                        <button
                                            v-else
                                            @click="goToPage(page)"
                                            class="relative inline-flex items-center px-4 py-2 border-2 border-primary bg-white text-sm font-medium text-primary hover:bg-pressed hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                            :aria-label="`Go to page ${page}`"
                                        >
                                            {{ page }}
                                        </button>
                                    </template>

                                    <!-- Next button -->
                                    <button
                                        v-if="currentPage < totalPages"
                                        @click="nextPage"
                                        class="relative inline-flex items-center px-3 py-2 rounded-r-lg border-2 border-primary bg-white text-sm font-medium text-primary hover:bg-pressed hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                        aria-label="Go to next page"
                                    >
                                        <span class="sr-only">Next</span>
                                        <svg
                                            class="h-4 w-4"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>

                <!-- Empty State -->
                <div
                    v-if="openLetters.length === 0"
                    class="bg-white border-2 border-primary rounded-lg p-12 text-center"
                    role="status"
                    aria-live="polite"
                    aria-atomic="true"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-16 mx-auto text-gray-400 mb-4"
                        aria-hidden="true"
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
                </section>
            </div>
        </div>

        <!-- View Letter Modal -->
        <LetterViewModal
            :show="showViewModal"
            :letter="viewingLetter"
            @close="closeViewModal"
            @reply="handleReply"
            @report="handleReport"
        />

        <!-- Report Modal -->
        <ReportModal
            :show="showReportModal"
            :letter="selectedLetter"
            @close="closeReportModal"
        />
    </AppLayout>
</template>
