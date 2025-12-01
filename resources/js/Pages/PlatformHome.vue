<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import LetterCard from "@/Components/LetterCard.vue";
import LetterViewModal from "@/Components/LetterViewModal.vue";
import ReportModal from "@/Components/ReportModal.vue";
import Avatar from "@/Components/Avatar.vue";
import OnboardingModal from "@/Components/OnboardingModal.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref, onMounted, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
    storyOfTheWeek: {
        type: Object,
        default: () => null,
    },
    unreadLetters: {
        type: Array,
        default: () => [],
    },
    needsOnboarding: {
        type: Boolean,
        default: false,
    },
    availableInterests: {
        type: Array,
        default: () => [],
    },
    availableLanguages: {
        type: Array,
        default: () => [],
    },
    userInterests: {
        type: Array,
        default: () => [],
    },
    userLanguages: {
        type: Array,
        default: () => [],
    },
});

// Get current user from page props
const page = usePage();
const user = computed(() => page.props.auth.user);

// Helper functions to check user type
const isVolunteer = computed(() => user.value?.user_type === "volunteer");
const isResident = computed(() => user.value?.user_type === "resident");

// Get welcome message based on user type
const getWelcomeMessage = () => {
    if (isVolunteer.value)
        return `Welcome back, ${user.value.name}! Ready to help your community?`;
    if (isResident.value)
        return `Welcome back, ${user.value.name}! Stay connected with your community.`;
    return "Welcome to Bridge!";
};

const getWelcomeDescription = () => {
    if (isVolunteer.value) {
        return "Jump into unread letters, connect with residents, and track your volunteer journey.";
    }
    if (isResident.value) {
        return "Read new letters, share your story, and keep in touch with the Bridge community.";
    }
    return "This is your main hub to access everything on Bridge.";
};

// Get user type display
const getUserType = () => {
    if (isVolunteer.value) return "Volunteer";
    if (isResident.value) return "Resident";
    return "User";
};

// Unread letters state
const unreadLetters = ref([]);
const isLoadingLetters = ref(false);
const currentPage = ref(1);
const itemsPerPage = ref(4);
const totalPages = ref(1);
const totalLetters = ref(0);

// Modal state
const showViewModal = ref(false);
const viewingLetter = ref(null);
const showReportModal = ref(false);
const selectedLetter = ref(null);
const showOnboardingModal = ref(props.needsOnboarding);

// Handle onboarding completion
const handleOnboardingCompleted = () => {
    showOnboardingModal.value = false;
    // Reload page to refresh props
    router.reload({
        only: ["needsOnboarding", "userInterests", "userLanguages"],
    });
};

// Watch for needsOnboarding prop changes
watch(
    () => props.needsOnboarding,
    (newValue) => {
        showOnboardingModal.value = newValue;
    }
);

// Fetch unread letters from API
const loadUnreadLetters = async (page = 1) => {
    isLoadingLetters.value = true;
    try {
        const response = await axios.get("/api/letters/unread", {
            params: {
                page: page,
            },
        });

        const { letters, pagination } = response.data;
        unreadLetters.value = letters || [];
        currentPage.value = pagination?.current_page || 1;
        totalPages.value = pagination?.last_page || 1;
        totalLetters.value = pagination?.total || 0;
    } catch (error) {
        console.error("Error loading unread letters:", error);
        console.error("Error response:", error.response?.data); // More detailed error
        console.error("Error status:", error.response?.status); // Status code
        unreadLetters.value = [];
        totalLetters.value = 0;
    } finally {
        isLoadingLetters.value = false;
    }
};

// Pagination methods
const goToPage = (pageNum) => {
    if (pageNum >= 1 && pageNum <= totalPages.value) {
        loadUnreadLetters(pageNum);
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

// Handle view letter - mark as read when opening modal
const handleView = async (letter) => {
    viewingLetter.value = letter;
    showViewModal.value = true;

    // Mark as read by fetching the letter (the API automatically marks it as read)
    if (letter.read_at === null) {
        try {
            await axios.get(`/api/letters/${letter.id}`);
            // Reload unread letters to update the list
            loadUnreadLetters(currentPage.value);
        } catch (error) {
            console.error("Error marking letter as read:", error);
        }
    }
};

const closeViewModal = () => {
    showViewModal.value = false;
    viewingLetter.value = null;
};

// Handle reply - redirect to write page with letter ID
const handleReply = (letterId) => {
    router.visit(`/platform/write?letterId=${letterId}`);
};

// Handle report
const handleReport = (letter) => {
    selectedLetter.value = letter;
    showReportModal.value = true;
    // Close view modal if it's open
    if (showViewModal.value) {
        showViewModal.value = false;
    }
};

const closeReportModal = () => {
    showReportModal.value = false;
    selectedLetter.value = null;
};

// Load unread letters on mount
onMounted(() => {
    loadUnreadLetters(1);
});
</script>

<template>
    <Head :title="`${getUserType()} Home`" />

    <AppLayout :title="`${getUserType()} Home`">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                {{ getUserType() }} Home
            </h2>
        </template>

        <div class="py-10 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Welcome Section -->
                <section
                    class="bg-primary text-white overflow-hidden shadow-xl rounded-lg p-6 sm:p-8"
                    aria-label="Welcome section"
                >
                    <h1 class="text-2xl sm:text-3xl font-bold mb-2">
                        {{ getWelcomeMessage() }}
                    </h1>
                    <p class="text-white text-sm sm:text-base max-w-3xl">
                        {{ getWelcomeDescription() }}
                    </p>
                </section>

                <!-- Featured Story -->
                <section
                    v-if="props.storyOfTheWeek"
                    aria-label="Featured story"
                >
                    <div
                        class="bg-primary text-white overflow-hidden shadow-xl rounded-lg p-6 sm:p-8"
                    >
                        <h2
                            class="text-xl sm:text-2xl lg:text-3xl font-bold text-white mb-4"
                        >
                            Featured Story
                        </h2>
                        <div
                            class="flex flex-col md:flex-row items-center md:items-start justify-center md:justify-start gap-4 sm:gap-6 md:gap-8"
                        >
                            <div
                                class="flex-shrink-0 flex flex-col items-center gap-3 sm:gap-4"
                            >
                                <Avatar
                                    :src="
                                        props.storyOfTheWeek.profile_photo_url
                                    "
                                    :name="props.storyOfTheWeek.name"
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
                                    {{ props.storyOfTheWeek.name }}
                                </h3>
                            </div>

                            <div
                                class="flex-1 min-w-0 flex flex-col items-center md:items-start"
                            >
                                <div
                                    class="text-sm sm:text-base md:text-lg text-white leading-relaxed space-y-3 text-center md:text-left"
                                >
                                    <p
                                        v-for="(
                                            paragraph, index
                                        ) in props.storyOfTheWeek.bio?.split(
                                            '\n'
                                        ) || []"
                                        :key="index"
                                    >
                                        {{ paragraph }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Unread Letters -->
                <section aria-label="Unread messages">
                    <h2
                        class="text-xl sm:text-2xl lg:text-3xl font-bold text-primary mb-4"
                    >
                        Unread Messages
                    </h2>
                    <div
                        class="bg-primary border border-primary/20 rounded-lg shadow-sm"
                        style="padding: 2px"
                    >
                        <div
                            v-if="isLoadingLetters"
                            class="p-10 text-center text-white/90"
                            role="status"
                            aria-live="polite"
                            aria-atomic="true"
                        >
                            Loading unread messages...
                        </div>
                        <div
                            v-else-if="unreadLetters.length > 0"
                            class="p-2 sm:p-4 md:p-6"
                        >
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 items-start justify-items-center"
                            >
                                <LetterCard
                                    v-for="letter in unreadLetters"
                                    :key="letter.id"
                                    :letter="letter"
                                    :show-reply-button="true"
                                    :show-status="false"
                                    @view="handleView"
                                    @reply="handleReply"
                                    @report="handleReport"
                                />
                            </div>

                            <!-- Pagination Controls -->
                            <nav
                                v-if="totalPages > 1"
                                class="mt-6 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg"
                                aria-label="Unread messages pagination"
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
                                        Page {{ currentPage }} of
                                        {{ totalPages }}
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
                                                (currentPage - 1) *
                                                    itemsPerPage +
                                                1
                                            }}</span>
                                            to
                                            <span class="font-medium">{{
                                                Math.min(
                                                    currentPage * itemsPerPage,
                                                    totalLetters
                                                )
                                            }}</span>
                                            of
                                            <span class="font-medium">{{
                                                totalLetters
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
                                                <span class="sr-only"
                                                    >Previous</span
                                                >
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
                                                <span class="sr-only"
                                                    >Next</span
                                                >
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
                        <div
                            v-else
                            class="p-10 text-center text-white"
                            role="status"
                            aria-live="polite"
                            aria-atomic="true"
                        >
                            No unread messages right now. Check back soon!
                        </div>
                    </div>
                </section>

                <!-- Main Platform Features -->
                <section
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                    aria-label="Platform features"
                >
                    <!-- Profile Settings Card -->
                    <article
                        class="bg-primary border border-primary/10 rounded-lg p-6 flex flex-col justify-between shadow-sm"
                    >
                        <h3 class="text-lg font-semibold text-white mb-4">
                            Profile Settings
                        </h3>
                        <p class="text-white mb-4">
                            Update your profile information and preferences
                        </p>
                        <Link
                            :href="route('profile.show')"
                            class="inline-flex items-center justify-center rounded-full bg-white text-primary px-4 py-2 font-semibold shadow hover:bg-hover hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                            aria-label="Go to profile settings"
                        >
                            View Profile
                        </Link>
                    </article>

                    <!-- Volunteer-specific features -->
                    <article
                        v-if="isVolunteer"
                        class="bg-primary border border-primary/10 rounded-lg p-6 flex flex-col justify-between shadow-sm"
                    >
                        <h3 class="text-lg font-semibold text-white mb-4">
                            Volunteer Opportunities
                        </h3>
                        <p class="text-white mb-4">
                            Find and join volunteer opportunities in your
                            community
                        </p>
                        <button
                            class="inline-flex items-center justify-center rounded-full bg-white text-primary px-4 py-2 font-semibold shadow hover:bg-hover hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                            aria-label="Browse volunteer opportunities"
                        >
                            Browse Opportunities
                        </button>
                    </article>

                    <!-- Resident-specific features -->
                    <article
                        v-if="isResident"
                        class="bg-primary border border-primary/10 rounded-lg p-6 flex flex-col justify-between shadow-sm"
                    >
                        <h3 class="text-lg font-semibold text-white mb-4">
                            Community Updates
                        </h3>
                        <p class="text-white mb-4">
                            Stay updated with your community news and events
                        </p>
                        <Link
                            :href="route('platform.discover')"
                            class="inline-flex items-center justify-center rounded-full bg-white text-primary px-4 py-2 font-semibold shadow hover:bg-hover hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                            aria-label="View community updates"
                        >
                            View Updates
                        </Link>
                    </article>

                    <!-- Messages/Communication -->
                    <article
                        class="bg-primary border border-primary/10 rounded-lg p-6 flex flex-col justify-between shadow-sm"
                    >
                        <h3 class="text-lg font-semibold text-white mb-4">
                            Messages
                        </h3>
                        <p class="text-white mb-4">
                            Connect with other community members
                        </p>
                        <Link
                            :href="route('platform.write')"
                            class="inline-flex items-center justify-center rounded-full bg-white text-primary px-4 py-2 font-semibold shadow hover:bg-hover hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                            aria-label="View messages and write new messages"
                        >
                            View Messages
                        </Link>
                    </article>
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

        <!-- Onboarding Modal -->
        <OnboardingModal
            :show="showOnboardingModal"
            :available-interests="props.availableInterests"
            :available-languages="props.availableLanguages"
            :user-interests="props.userInterests"
            :user-languages="props.userLanguages"
            @completed="handleOnboardingCompleted"
            @close="handleOnboardingCompleted"
        />
    </AppLayout>
</template>
