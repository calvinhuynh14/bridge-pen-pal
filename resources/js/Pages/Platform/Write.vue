<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import OpenLetterCard from "@/Components/OpenLetterCard.vue";
import LetterViewModal from "@/Components/LetterViewModal.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterControls from "@/Components/FilterControls.vue";
import Avatar from "@/Components/Avatar.vue";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";

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

const incomingLetters = ref([]);
const isLoadingIncomingLetters = ref(false);

const penPals = ref([]);
const isLoadingPenPals = ref(false);
const penPalLoadError = ref(null);

const penPalSearchQuery = ref("");

// Selected pen pal and correspondence
const selectedPenPal = ref(null);
const correspondence = ref([]);
const correspondenceSearchQuery = ref("");
const sortOrder = ref("newest"); // "newest" or "oldest"
const filterBySender = ref("all"); // "all", "me", or "them"

// Pagination state
const currentPage = ref(1);
const hasMorePages = ref(false);
const isLoading = ref(false);
const isLoadingMore = ref(false);
const loadError = ref(null);

// View letter modal
const showViewModal = ref(false);
const viewingLetter = ref(null);

// Load incoming letters from API
const loadIncomingLetters = async () => {
    isLoadingIncomingLetters.value = true;

    try {
        const response = await axios.get("/api/letters/received");
        // Get unique senders from received letters
        const uniqueSenders = new Map();
        response.data.letters.forEach((letter) => {
            if (!uniqueSenders.has(letter.sender_id)) {
                uniqueSenders.set(letter.sender_id, {
                    id: letter.sender_id,
                    name: letter.sender_name,
                    avatar: null, // Can be added later if avatars are stored
                });
            }
        });
        incomingLetters.value = Array.from(uniqueSenders.values());
    } catch (error) {
        console.error("Error loading incoming letters:", error);
        // Don't show error for incoming letters, just leave empty
        incomingLetters.value = [];
    } finally {
        isLoadingIncomingLetters.value = false;
    }
};

// Load pen pals from API
const loadPenPals = async () => {
    isLoadingPenPals.value = true;
    penPalLoadError.value = null;

    try {
        const response = await axios.get("/api/pen-pals");
        penPals.value = response.data.pen_pals.map((pal) => ({
            id: pal.id,
            name: pal.name,
            avatar: null, // Can be added later if avatars are stored
        }));
    } catch (error) {
        console.error("Error loading pen pals:", error);
        penPalLoadError.value =
            error.response?.data?.error || "Failed to load pen pals";
    } finally {
        isLoadingPenPals.value = false;
    }
};

// Load correspondence from API
const loadCorrespondence = async (page = 1, append = false) => {
    if (!selectedPenPal.value) return;

    // Store scroll position before loading (for maintaining position when appending)
    let scrollPosition = 0;
    if (append && correspondenceContainer.value) {
        scrollPosition = correspondenceContainer.value.scrollTop;
    }

    // Set loading state
    if (page === 1) {
        isLoading.value = true;
        loadError.value = null;
    } else {
        isLoadingMore.value = true;
    }

    try {
        const response = await axios.get(
            `/api/correspondence/${selectedPenPal.value.id}`,
            {
                params: {
                    page: page,
                    per_page: 10,
                    search: correspondenceSearchQuery.value.trim(),
                    filter: filterBySender.value,
                    sort: sortOrder.value,
                },
            }
        );

        const { messages, pagination } = response.data;

        if (append) {
            // Append to existing messages
            correspondence.value = [...correspondence.value, ...messages];

            // Restore scroll position after DOM update
            if (correspondenceContainer.value) {
                // Use nextTick to ensure DOM is updated
                setTimeout(() => {
                    if (correspondenceContainer.value) {
                        correspondenceContainer.value.scrollTop =
                            scrollPosition;
                    }
                }, 0);
            }
        } else {
            // Replace messages
            correspondence.value = messages;
            // Scroll to top when loading new set
            if (correspondenceContainer.value) {
                correspondenceContainer.value.scrollTop = 0;
            }
        }

        currentPage.value = pagination.current_page;
        hasMorePages.value = pagination.has_more;
    } catch (error) {
        console.error("Error loading correspondence:", error);
        loadError.value =
            error.response?.data?.error || "Failed to load messages";
        if (!append) {
            correspondence.value = [];
        }
    } finally {
        isLoading.value = false;
        isLoadingMore.value = false;
    }
};

// Handle pen pal click
const selectPenPal = (penPal) => {
    selectedPenPal.value = penPal;
    currentPage.value = 1;
    hasMorePages.value = false;
    correspondence.value = [];
    loadError.value = null;
    // Load first page
    loadCorrespondence(1, false);
};

// Handle back button (mobile)
const goBack = () => {
    selectedPenPal.value = null;
    correspondence.value = [];
    correspondenceSearchQuery.value = "";
    sortOrder.value = "newest";
    filterBySender.value = "all";
    currentPage.value = 1;
    hasMorePages.value = false;
    loadError.value = null;
};

// Filter pen pals based on search query
const filteredPenPals = computed(() => {
    if (!penPalSearchQuery.value.trim()) {
        return penPals.value;
    }

    const query = penPalSearchQuery.value.toLowerCase().trim();

    return penPals.value.filter((penPal) => {
        return penPal.name?.toLowerCase().includes(query);
    });
});

// Filtered correspondence - now handled server-side, so just return the loaded messages
const filteredCorrespondence = computed(() => {
    return correspondence.value;
});

// Watch for search/filter/sort changes and reload
const reloadCorrespondence = () => {
    if (!selectedPenPal.value) return;
    currentPage.value = 1;
    hasMorePages.value = false;
    loadCorrespondence(1, false);
};

// Load more messages (for infinite scroll)
const loadMoreMessages = () => {
    if (!isLoadingMore.value && hasMorePages.value && selectedPenPal.value) {
        loadCorrespondence(currentPage.value + 1, true);
    }
};

// Watch for changes in search, filter, or sort
let searchTimeout = null;
watch([correspondenceSearchQuery, filterBySender, sortOrder], () => {
    if (selectedPenPal.value) {
        // Debounce search to avoid too many API calls
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            reloadCorrespondence();
        }, 500);
    }
});

// Infinite scroll setup
const correspondenceContainer = ref(null);
const infiniteScrollTrigger = ref(null);
let observer = null;

const setupInfiniteScroll = () => {
    if (!infiniteScrollTrigger.value || !selectedPenPal.value) return;

    // Clean up existing observer
    if (observer) {
        observer.disconnect();
    }

    // Set up Intersection Observer for infinite scroll
    if (typeof window !== "undefined" && "IntersectionObserver" in window) {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (
                        entry.isIntersecting &&
                        hasMorePages.value &&
                        !isLoadingMore.value &&
                        !isLoading.value
                    ) {
                        loadMoreMessages();
                    }
                });
            },
            {
                root: correspondenceContainer.value,
                rootMargin: "100px", // Start loading 100px before reaching the bottom
                threshold: 0.1,
            }
        );

        observer.observe(infiniteScrollTrigger.value);
    }
};

// Watch for when infiniteScrollTrigger is available and observe it
watch(infiniteScrollTrigger, () => {
    setupInfiniteScroll();
});

// Watch for when pen pal is selected to set up observer
watch(selectedPenPal, () => {
    if (selectedPenPal.value) {
        // Wait for next tick to ensure DOM is updated
        setTimeout(() => {
            setupInfiniteScroll();
        }, 100);
    } else {
        if (observer) {
            observer.disconnect();
            observer = null;
        }
    }
});

onMounted(() => {
    if (typeof window !== "undefined") {
        window.addEventListener("resize", updateWindowWidth);
    }
});

onUnmounted(() => {
    if (typeof window !== "undefined") {
        window.removeEventListener("resize", updateWindowWidth);
    }
    if (observer) {
        observer.disconnect();
        observer = null;
    }
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
});

// Handle view letter
const handleView = (letter) => {
    viewingLetter.value = letter;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    viewingLetter.value = null;
};

// Handle reply
const handleReply = (letterId) => {
    // TODO: Navigate to compose letter page
    console.log("Reply to letter:", letterId);
};

// Handle write new letter
const handleWriteNew = () => {
    // TODO: Navigate to compose letter page for selected pen pal
    console.log("Write new letter to:", selectedPenPal.value);
};

// Track window width for responsive behavior
const windowWidth = ref(
    typeof window !== "undefined" ? window.innerWidth : 1024
);

const updateWindowWidth = () => {
    windowWidth.value = window.innerWidth;
};

onMounted(() => {
    if (typeof window !== "undefined") {
        window.addEventListener("resize", updateWindowWidth);
    }
    // Load data when component mounts
    loadIncomingLetters();
    loadPenPals();
});

onUnmounted(() => {
    if (typeof window !== "undefined") {
        window.removeEventListener("resize", updateWindowWidth);
    }
});
</script>

<template>
    <Head :title="`${getUserType()} - Write`" />

    <AppLayout :title="`${getUserType()} - Write`">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Write
            </h2>
        </template>

        <div class="py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6 justify-center lg:justify-start">
                    <!-- Correspondence Sidebar/List (Search, Incoming, Pen Pals) - Mobile: hidden when pen pal selected, Desktop: always visible -->
                    <div
                        v-if="!selectedPenPal || windowWidth >= 1024"
                        class="flex-shrink-0 transition-all duration-300 w-full max-w-md lg:max-w-[280px] mx-auto lg:mx-0"
                    >
                        <!-- Incoming Letters Section -->
                        <div class="mb-2">
                            <h3 class="text-lg font-bold text-pressed mb-1">
                                Incoming Letters
                            </h3>
                            <div
                                class="bg-primary rounded-lg p-4"
                                style="
                                    background-color: rgba(184, 184, 255, 0.7);
                                "
                            >
                                <!-- Loading State -->
                                <div
                                    v-if="isLoadingIncomingLetters"
                                    class="flex justify-center py-4"
                                >
                                    <LoadingSpinner />
                                </div>

                                <!-- Incoming Letters List -->
                                <div
                                    v-else-if="incomingLetters.length > 0"
                                    class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide"
                                    style="
                                        scrollbar-width: none;
                                        -ms-overflow-style: none;
                                    "
                                >
                                    <div
                                        v-for="letter in incomingLetters"
                                        :key="letter.id"
                                        class="flex-shrink-0 flex flex-col items-center gap-2"
                                    >
                                        <Avatar
                                            :src="letter.avatar"
                                            :name="letter.name"
                                            size="lg"
                                            border-color="white"
                                            opacity="70"
                                        />
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div
                                    v-else
                                    class="text-center py-4 text-white/60 text-sm"
                                >
                                    No incoming letters
                                </div>
                            </div>
                        </div>

                        <!-- Pen Pals Section -->
                        <div class="mb-2">
                            <h3 class="text-lg font-bold text-pressed mb-1">
                                Pen Pals
                            </h3>
                            <!-- Search Bar for Pen Pals -->
                            <div class="mb-2">
                                <SearchBar
                                    v-model="penPalSearchQuery"
                                    placeholder="Search pen pals..."
                                />
                            </div>
                            <div class="bg-primary rounded-lg overflow-hidden">
                                <!-- Loading State -->
                                <div
                                    v-if="isLoadingPenPals"
                                    class="text-center py-6"
                                >
                                    <LoadingSpinner />
                                </div>

                                <!-- Error State -->
                                <div
                                    v-else-if="penPalLoadError"
                                    class="text-center py-6 px-4"
                                >
                                    <p class="text-sm text-white mb-4">
                                        {{ penPalLoadError }}
                                    </p>
                                    <button
                                        @click="loadPenPals"
                                        class="px-4 py-2 bg-pressed hover:bg-hover text-white rounded-lg transition-colors text-sm"
                                    >
                                        Retry
                                    </button>
                                </div>

                                <!-- Pen Pals List -->
                                <div v-else-if="filteredPenPals.length > 0">
                                    <div
                                        v-for="(
                                            penPal, index
                                        ) in filteredPenPals"
                                        :key="penPal.id"
                                        @click="selectPenPal(penPal)"
                                        class="flex items-center gap-4 p-4 hover:bg-hover transition-colors cursor-pointer"
                                        :class="{
                                            'bg-pressed':
                                                selectedPenPal &&
                                                selectedPenPal.id === penPal.id,
                                            'border-b border-white/20':
                                                index <
                                                filteredPenPals.length - 1,
                                        }"
                                    >
                                        <!-- Avatar -->
                                        <div class="flex-shrink-0">
                                            <Avatar
                                                :src="penPal.avatar"
                                                :name="penPal.name"
                                                size="md"
                                                border-color="white"
                                            />
                                        </div>

                                        <!-- Name and Action Text -->
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="font-bold text-white text-sm mb-0.5 break-words"
                                            >
                                                {{ penPal.name }}
                                            </p>
                                            <p class="text-xs text-white/80">
                                                Send a letter!
                                            </p>
                                        </div>

                                        <!-- Write Icon Button -->
                                        <button
                                            @click.stop="selectPenPal(penPal)"
                                            class="flex-shrink-0 p-2 text-white hover:text-white hover:bg-hover rounded-lg transition-colors"
                                            :aria-label="`View correspondence with ${penPal.name}`"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                                class="size-6"
                                            >
                                                <path
                                                    d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"
                                                />
                                                <path
                                                    d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="text-center py-6 text-white/80"
                                >
                                    <p class="text-sm">
                                        {{
                                            penPalSearchQuery.trim()
                                                ? "No pen pals found matching your search."
                                                : "No pen pals yet."
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Section (Correspondence View) - Mobile: replaces main content, Desktop: takes remaining space -->
                    <div
                        v-if="selectedPenPal"
                        class="flex-1 transition-all duration-300 w-full"
                    >
                        <!-- Mobile Back Button -->
                        <div
                            class="lg:hidden flex items-center gap-2 p-2 border-b border-gray-200"
                        >
                            <button
                                @click="goBack"
                                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                aria-label="Back to pen pals"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="size-6 text-hover"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M9.53 2.47a.75.75 0 0 1 0 1.06L4.81 8.25H15a6.75 6.75 0 0 1 0 13.5h-3a.75.75 0 0 1 0-1.5h3a5.25 5.25 0 1 0 0-10.5H4.81l4.72 4.72a.75.75 0 1 1-1.06 1.06l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 0 1 1.06 0Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <Avatar
                                    :src="selectedPenPal.avatar"
                                    :name="selectedPenPal.name"
                                    size="sm"
                                    border-color="pressed"
                                />
                            </div>
                            <h3 class="text-lg font-bold text-pressed">
                                {{ selectedPenPal.name }}
                            </h3>
                        </div>

                        <!-- Desktop Header -->
                        <div
                            class="hidden lg:block mb-3 pb-2 border-b border-gray-200"
                        >
                            <div class="flex items-center gap-2">
                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    <Avatar
                                        :src="selectedPenPal.avatar"
                                        :name="selectedPenPal.name"
                                        size="sm"
                                        border-color="pressed"
                                    />
                                </div>
                                <h3 class="text-lg font-bold text-pressed">
                                    {{ selectedPenPal.name }}
                                </h3>
                            </div>
                        </div>

                        <!-- Search Bar and Filters for Correspondence -->
                        <div class="mb-3 space-y-2">
                            <!-- Search Bar -->
                            <SearchBar
                                v-model="correspondenceSearchQuery"
                                placeholder="Search messages or dates..."
                            />

                            <!-- Filter and Sort Controls -->
                            <FilterControls
                                v-model:sort-order="sortOrder"
                                v-model:filter-value="filterBySender"
                                :sort-options="[
                                    { value: 'newest', label: 'Newest First' },
                                    { value: 'oldest', label: 'Oldest First' },
                                ]"
                                :filter-options="[
                                    { value: 'all', label: 'All Messages' },
                                    { value: 'me', label: 'My Messages' },
                                    { value: 'them', label: 'Their Messages' },
                                ]"
                            />
                        </div>

                        <!-- Correspondence Letters -->
                        <div
                            class="p-2 pt-4 lg:p-4 overflow-y-auto bg-primary rounded-lg"
                            :class="{
                                'h-[calc(100vh-5rem)] lg:h-[calc(100vh-10rem)]':
                                    selectedPenPal,
                            }"
                            ref="correspondenceContainer"
                        >
                            <!-- Loading State (Initial Load) -->
                            <div
                                v-if="isLoading"
                                class="flex items-center justify-center py-12"
                            >
                                <LoadingSpinner size="lg" color="pressed" />
                            </div>

                            <!-- Error State -->
                            <div
                                v-else-if="
                                    loadError && correspondence.length === 0
                                "
                                class="text-center py-8"
                            >
                                <p class="text-sm text-black mb-4">
                                    {{ loadError }}
                                </p>
                                <button
                                    @click="reloadCorrespondence"
                                    class="px-4 py-2 bg-pressed hover:bg-hover text-white rounded-lg transition-colors"
                                >
                                    Retry
                                </button>
                            </div>

                            <!-- Messages Grid -->
                            <div
                                v-else-if="filteredCorrespondence.length > 0"
                                class="grid grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-3"
                            >
                                <div
                                    v-for="letter in filteredCorrespondence"
                                    :key="letter.id"
                                    @click="handleView(letter)"
                                    class="cursor-pointer"
                                >
                                    <OpenLetterCard
                                        :letter="letter"
                                        :is-claiming="false"
                                        :show-reply-button="false"
                                        @view="handleView"
                                    />
                                </div>
                            </div>

                            <!-- Empty State (No Messages) -->
                            <div
                                v-else-if="!isLoading"
                                class="text-center py-8 text-gray-500"
                            >
                                <p class="text-sm">
                                    {{
                                        correspondenceSearchQuery.trim()
                                            ? "No messages found matching your search."
                                            : "No letters yet. Start the conversation!"
                                    }}
                                </p>
                            </div>

                            <!-- Infinite Scroll Trigger & Loading More -->
                            <div
                                v-if="
                                    !isLoading &&
                                    filteredCorrespondence.length > 0
                                "
                                ref="infiniteScrollTrigger"
                                class="flex flex-col items-center justify-center py-4"
                            >
                                <!-- Loading More Indicator -->
                                <div v-if="isLoadingMore" class="py-4">
                                    <LoadingSpinner size="md" color="pressed" />
                                </div>

                                <!-- Error Loading More -->
                                <div
                                    v-else-if="
                                        loadError && correspondence.length > 0
                                    "
                                    class="text-center py-4"
                                >
                                    <p class="text-sm text-black mb-2">
                                        {{ loadError }}
                                    </p>
                                    <button
                                        @click="loadMoreMessages"
                                        class="px-4 py-2 bg-pressed hover:bg-hover text-white rounded-lg transition-colors text-sm"
                                    >
                                        Retry Loading
                                    </button>
                                </div>

                                <!-- No More Messages -->
                                <div
                                    v-else-if="!hasMorePages"
                                    class="text-center py-4"
                                >
                                    <p class="text-xs text-gray-500">
                                        No more messages
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Write Button -->
                        <button
                            @click="handleWriteNew"
                            class="fixed bottom-4 right-4 bg-accent hover:bg-pressed text-hover rounded-full p-4 lg:p-6 transition-colors flex items-center justify-center z-40 w-fit shadow-lg"
                            aria-label="Write new letter"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-6 lg:size-8"
                            >
                                <path
                                    d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"
                                />
                                <path
                                    d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Letter Modal -->
        <LetterViewModal
            :show="showViewModal"
            :letter="viewingLetter"
            @close="closeViewModal"
            @reply="handleReply"
        />
    </AppLayout>
</template>
