<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import LetterCard from "@/Components/LetterCard.vue";
import LetterViewModal from "@/Components/LetterViewModal.vue";
import Modal from "@/Components/Modal.vue";
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
const isLoadingMorePenPals = ref(false);
const penPalLoadError = ref(null);

// Pen pal pagination state
const penPalCurrentPage = ref(1);
const penPalHasMorePages = ref(false);

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

// Report modal
const showReportModal = ref(false);
const selectedLetter = ref(null);
const reportReason = ref("");
const reportingLetterId = ref(null);

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
const loadPenPals = async (page = 1, append = false) => {
    // Set loading state
    if (page === 1) {
        isLoadingPenPals.value = true;
        penPalLoadError.value = null;
    } else {
        isLoadingMorePenPals.value = true;
    }

    try {
        const response = await axios.get("/api/pen-pals", {
            params: {
                page: page,
                per_page: 10,
                search: penPalSearchQuery.value.trim(),
            },
        });

        const { pen_pals, pagination } = response.data;

        if (append) {
            // Append to existing pen pals
            penPals.value = [
                ...penPals.value,
                ...pen_pals.map((pal) => ({
                    id: pal.id,
                    name: pal.name,
                    avatar: null, // Can be added later if avatars are stored
                    has_messages:
                        pal.has_messages === 1 || pal.has_messages === true,
                    unread_count: parseInt(pal.unread_count) || 0,
                })),
            ];
        } else {
            // Replace pen pals
            penPals.value = pen_pals.map((pal) => ({
                id: pal.id,
                name: pal.name,
                avatar: null, // Can be added later if avatars are stored
                has_messages:
                    pal.has_messages === 1 || pal.has_messages === true,
                unread_count: parseInt(pal.unread_count) || 0,
            }));
        }

        penPalCurrentPage.value = pagination.current_page;
        penPalHasMorePages.value = pagination.has_more;
    } catch (error) {
        console.error("Error loading pen pals:", error);
        penPalLoadError.value =
            error.response?.data?.error || "Failed to load pen pals";
        if (!append) {
            penPals.value = [];
        }
    } finally {
        isLoadingPenPals.value = false;
        isLoadingMorePenPals.value = false;
    }
};

// Reload pen pals (reset pagination)
const reloadPenPals = () => {
    penPalCurrentPage.value = 1;
    penPalHasMorePages.value = false;
    loadPenPals(1, false);
};

// Load more pen pals (for infinite scroll)
const loadMorePenPals = () => {
    if (!isLoadingMorePenPals.value && penPalHasMorePages.value) {
        loadPenPals(penPalCurrentPage.value + 1, true);
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

// Filtered pen pals - now handled server-side, so just return the loaded pen pals
const filteredPenPals = computed(() => {
    return penPals.value;
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

// Watch for pen pal search query changes
let penPalSearchTimeout = null;
watch(penPalSearchQuery, () => {
    // Debounce search to avoid too many API calls
    clearTimeout(penPalSearchTimeout);
    penPalSearchTimeout = setTimeout(() => {
        reloadPenPals();
    }, 500);
});

// Infinite scroll setup for correspondence
const correspondenceContainer = ref(null);
const infiniteScrollTrigger = ref(null);
let observer = null;

// Infinite scroll setup for pen pals
const penPalContainer = ref(null);
const penPalInfiniteScrollTrigger = ref(null);
let penPalObserver = null;

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

// Set up infinite scroll for pen pals
const setupPenPalInfiniteScroll = () => {
    if (!penPalInfiniteScrollTrigger.value) return;

    // Clean up existing observer
    if (penPalObserver) {
        penPalObserver.disconnect();
    }

    // Set up Intersection Observer for infinite scroll
    if (typeof window !== "undefined" && "IntersectionObserver" in window) {
        penPalObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (
                        entry.isIntersecting &&
                        penPalHasMorePages.value &&
                        !isLoadingMorePenPals.value &&
                        !isLoadingPenPals.value
                    ) {
                        loadMorePenPals();
                    }
                });
            },
            {
                root: penPalContainer.value,
                rootMargin: "100px",
                threshold: 0.1,
            }
        );

        penPalObserver.observe(penPalInfiniteScrollTrigger.value);
    }
};

// Watch for when penPalInfiniteScrollTrigger is available
watch(penPalInfiniteScrollTrigger, () => {
    setupPenPalInfiniteScroll();
});

// Set up pen pal infinite scroll when component mounts or pen pals are loaded
watch(penPals, () => {
    if (penPals.value.length > 0) {
        setTimeout(() => {
            setupPenPalInfiniteScroll();
        }, 100);
    }
});

// Handle view letter
const handleView = async (letter) => {
    // Prevent duplicate calls
    if (viewingLetter.value?.id === letter.id && showViewModal.value) {
        return;
    }

    viewingLetter.value = letter;
    showViewModal.value = true;

    // Mark letter as read if user is the receiver
    const currentUserId = user.value?.id;

    console.log("=== VIEWING LETTER DEBUG ===");
    console.log("Viewing letter:", {
        letterId: letter.id,
        receiverId: letter.receiver_id,
        receiverIdType: typeof letter.receiver_id,
        currentUserId: currentUserId,
        currentUserIdType: typeof currentUserId,
        readAt: letter.read_at,
        status: letter.status,
        fullLetter: letter,
        shouldMarkAsRead:
            letter.receiver_id === currentUserId && !letter.read_at,
        receiverIdMatch: letter.receiver_id === currentUserId,
        receiverIdLooseMatch: letter.receiver_id == currentUserId,
        hasReadAt: !!letter.read_at,
    });

    if (letter.receiver_id === currentUserId && !letter.read_at) {
        console.log("✓ Conditions met - marking as read");
        try {
            console.log(`Calling API: GET /api/letters/${letter.id}`);
            // Call API to mark as read (the show endpoint marks it as read)
            const response = await axios.get(`/api/letters/${letter.id}`);

            console.log("API Response:", response);
            console.log("API Response Data:", response.data);
            const updatedLetter = response.data.letter;

            console.log("Updated letter from API:", {
                id: updatedLetter.id,
                status: updatedLetter.status,
                read_at: updatedLetter.read_at,
                fullLetter: updatedLetter,
            });

            // Update the letter in the correspondence list
            console.log(
                "Before update - correspondence list length:",
                correspondence.value.length
            );
            const letterIndex = correspondence.value.findIndex(
                (l) => l.id === letter.id
            );
            console.log("Letter index in list:", letterIndex);

            if (letterIndex !== -1) {
                console.log(
                    "Before update - letter in list:",
                    correspondence.value[letterIndex]
                );
                // Use Vue's reactive update by replacing the entire object
                correspondence.value[letterIndex] = {
                    ...correspondence.value[letterIndex],
                    ...updatedLetter,
                    status: updatedLetter.status || "read",
                    read_at: updatedLetter.read_at,
                };
                console.log(
                    "After update - letter in list:",
                    correspondence.value[letterIndex]
                );
                console.log(
                    "Letter status after update:",
                    correspondence.value[letterIndex].status
                );
            } else {
                console.warn("Letter not found in correspondence list!");
            }

            // Update viewing letter with latest data
            viewingLetter.value = {
                ...viewingLetter.value,
                ...updatedLetter,
                status: updatedLetter.status || "read",
                read_at: updatedLetter.read_at,
            };

            // Refresh pen pal list to update unread count
            reloadPenPals();
        } catch (error) {
            console.error("Error marking letter as read:", error);
            console.error("Error details:", error.response?.data);
        }
    } else {
        console.log("Not marking as read - conditions not met");
    }
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

// Report letter functions
const openReportModal = (letter) => {
    selectedLetter.value = letter;
    reportReason.value = "";
    showReportModal.value = true;
    // Close view modal if it's open
    if (showViewModal.value) {
        showViewModal.value = false;
    }
};

const handleReport = (letter) => {
    openReportModal(letter);
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
                alert("Report submitted successfully.");
                closeReportModal();
            },
            onError: (errors) => {
                alert(
                    errors.reason ||
                        "Failed to submit report. Please try again."
                );
                reportingLetterId.value = null;
            },
        }
    );
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
    loadPenPals(1, false);
});

onUnmounted(() => {
    if (typeof window !== "undefined") {
        window.removeEventListener("resize", updateWindowWidth);
    }
    if (observer) {
        observer.disconnect();
        observer = null;
    }
    if (penPalObserver) {
        penPalObserver.disconnect();
        penPalObserver = null;
    }
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    if (penPalSearchTimeout) {
        clearTimeout(penPalSearchTimeout);
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

        <div class="py-2">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6 justify-center lg:justify-start">
                    <!-- Correspondence Sidebar/List (Search, Incoming, Pen Pals) - Mobile: hidden when pen pal selected, Desktop: always visible -->
                    <div
                        v-if="!selectedPenPal || windowWidth >= 1024"
                        class="flex-shrink-0 transition-all duration-300 w-full max-w-md lg:max-w-[280px] mx-auto lg:mx-0"
                    >
                        <!-- Incoming Letters Section - Hidden on mobile -->
                        <div class="mb-2 hidden lg:block">
                            <h3 class="text-lg font-bold text-pressed mb-1">
                                Incoming Letters
                            </h3>
                            <div
                                class="bg-primary rounded-lg p-4 overflow-hidden"
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
                                    class="flex gap-3 justify-center overflow-x-auto w-full"
                                    style="
                                        scrollbar-width: thin;
                                        scrollbar-color: rgba(
                                                255,
                                                255,
                                                255,
                                                0.3
                                            )
                                            transparent;
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
                                            size="md"
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
                                        @click="reloadPenPals"
                                        class="px-4 py-2 bg-pressed hover:bg-hover text-white rounded-lg transition-colors text-sm"
                                    >
                                        Retry
                                    </button>
                                </div>

                                <!-- Pen Pals List -->
                                <div
                                    v-else-if="filteredPenPals.length > 0"
                                    ref="penPalContainer"
                                    class="max-h-[60vh] overflow-y-auto p-2"
                                    style="
                                        scrollbar-width: thin;
                                        scrollbar-color: rgba(
                                                255,
                                                255,
                                                255,
                                                0.3
                                            )
                                            transparent;
                                        -ms-overflow-style: none;
                                    "
                                >
                                    <div
                                        v-for="(
                                            penPal, index
                                        ) in filteredPenPals"
                                        :key="penPal.id"
                                        @click="selectPenPal(penPal)"
                                        class="flex items-center gap-4 p-4 hover:bg-hover transition-colors cursor-pointer bg-white/10 rounded-lg mb-2"
                                        :class="{
                                            'bg-pressed':
                                                selectedPenPal &&
                                                selectedPenPal.id === penPal.id,
                                        }"
                                    >
                                        <!-- Avatar -->
                                        <div class="flex-shrink-0 relative">
                                            <Avatar
                                                :src="penPal.avatar"
                                                :name="penPal.name"
                                                size="md"
                                                border-color="white"
                                            />
                                            <!-- Unread indicator -->
                                            <div
                                                v-if="penPal.unread_count > 0"
                                                class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"
                                                aria-label="Unread messages"
                                            ></div>
                                        </div>

                                        <!-- Name and Action Text -->
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="font-bold text-white text-sm mb-0.5 break-words"
                                            >
                                                {{ penPal.name }}
                                            </p>
                                            <p class="text-xs text-white/80">
                                                {{
                                                    penPal.unread_count > 0
                                                        ? "New message"
                                                        : penPal.has_messages
                                                        ? "View messages"
                                                        : "Send letter"
                                                }}
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

                                    <!-- Infinite Scroll Trigger & Loading More -->
                                    <div
                                        ref="penPalInfiniteScrollTrigger"
                                        class="py-2"
                                    >
                                        <!-- Loading More -->
                                        <div
                                            v-if="isLoadingMorePenPals"
                                            class="text-center py-4"
                                        >
                                            <LoadingSpinner />
                                        </div>

                                        <!-- Error Loading More -->
                                        <div
                                            v-else-if="
                                                penPalLoadError &&
                                                filteredPenPals.length > 0
                                            "
                                            class="text-center py-4 px-4"
                                        >
                                            <p class="text-sm text-black mb-2">
                                                {{ penPalLoadError }}
                                            </p>
                                            <button
                                                @click="loadMorePenPals"
                                                class="px-4 py-2 bg-pressed hover:bg-hover text-white rounded-lg transition-colors text-sm"
                                            >
                                                Retry Loading
                                            </button>
                                        </div>

                                        <!-- No More Pen Pals -->
                                        <div
                                            v-else-if="
                                                !penPalHasMorePages &&
                                                filteredPenPals.length > 0
                                            "
                                            class="text-center py-2 text-white/60 text-xs"
                                        >
                                            No more pen pals
                                        </div>
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
                            class="lg:hidden flex items-center gap-2 pb-2 px-2 border-b border-gray-200"
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
                            style="
                                scrollbar-width: thin;
                                scrollbar-color: rgba(255, 255, 255, 0.3)
                                    transparent;
                                -ms-overflow-style: none;
                            "
                            :class="{
                                'h-[calc(100vh-16rem)] lg:h-[calc(100vh-10rem)]':
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
                                    <LetterCard
                                        :letter="letter"
                                        :is-claiming="false"
                                        :show-reply-button="false"
                                        @view="handleView"
                                        @report="handleReport"
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
                    </div>
                </div>
            </div>

            <!-- Floating Write Button - Always visible, positioned outside containers -->
            <button
                @click="handleWriteNew"
                class="fixed bottom-20 right-4 lg:bottom-4 bg-accent hover:bg-pressed text-hover rounded-full p-4 lg:p-6 transition-colors flex items-center justify-center z-50 w-fit shadow-lg"
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

        <!-- View Letter Modal -->
        <LetterViewModal
            :show="showViewModal"
            :letter="viewingLetter"
            @close="closeViewModal"
            @reply="handleReply"
        />

        <!-- Report Modal -->
        <Modal :show="showReportModal" @close="closeReportModal" max-width="md">
            <div class="p-6 bg-pressed">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">Report Letter</h3>
                    <button
                        @click="closeReportModal"
                        class="text-white/80 hover:text-white transition-colors"
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
                    <p class="text-sm text-white/90 mb-2">
                        Reporting letter from
                        <span class="font-semibold text-white">{{
                            selectedLetter.sender_name
                        }}</span>
                    </p>
                    <div
                        class="bg-white border-2 border-white/30 rounded-lg p-3 mb-4"
                    >
                        <p
                            class="text-sm text-black"
                            style="
                                display: -webkit-box;
                                -webkit-line-clamp: 3;
                                line-clamp: 3;
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
                        class="block text-sm font-medium text-white mb-2"
                    >
                        Reason for reporting
                    </label>
                    <textarea
                        id="reportReason"
                        v-model="reportReason"
                        rows="4"
                        class="w-full px-4 py-2 border-2 border-white/30 bg-white text-black rounded-lg focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 resize-none placeholder:text-gray-500"
                        placeholder="Please describe why you are reporting this letter..."
                    ></textarea>
                </div>

                <div class="flex gap-3 justify-end">
                    <button
                        @click="closeReportModal"
                        class="px-4 py-2 bg-white border-2 border-primary text-primary rounded-lg font-medium hover:bg-primary hover:text-black transition-colors"
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
        </Modal>
    </AppLayout>
</template>
