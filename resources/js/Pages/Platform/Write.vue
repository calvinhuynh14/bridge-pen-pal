<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted, watch, nextTick } from "vue";
import { usePage } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import LetterCard from "@/Components/LetterCard.vue";
import LetterViewModal from "@/Components/LetterViewModal.vue";
import ReportModal from "@/Components/ReportModal.vue";
import Modal from "@/Components/Modal.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterControls from "@/Components/FilterControls.vue";
import Select from "@/Components/Select.vue";
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

// Writing interface state
const showWritingInterface = ref(false);
const selectedRecipient = ref(null); // null = open letter, or pen pal ID
const letterContent = ref("");
const isSending = ref(false);
const sendError = ref(null);
const originalLetter = ref(null); // Store the letter being replied to

// Pagination state
const currentPage = ref(1);
const hasMorePages = ref(false);
const isLoading = ref(false);
const isLoadingMore = ref(false);
const loadError = ref(null);

// View letter modal
const showViewModal = ref(false);
const viewingLetter = ref(null);

// Preview original letter modal (for reply context)
const showPreviewModal = ref(false);

// Report modal
const showReportModal = ref(false);
const selectedLetter = ref(null);

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
                    id: Number(pal.id), // Ensure ID is a number
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
                id: Number(pal.id), // Ensure ID is a number
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

// Handle reply - fetch letter and open writing interface
const handleReply = async (letterId) => {
    try {
        // Fetch letter details
        const response = await axios.get(`/api/letters/${letterId}`);
        const letter = response.data.letter;

        if (!letter || !letter.sender_id) {
            console.error("Invalid letter data:", letter);
            alert("Failed to load letter. Invalid data received.");
            return;
        }

        // Store the original letter for preview
        originalLetter.value = letter;

        // Ensure pen pals are loaded first
        if (penPals.value.length === 0) {
            await loadPenPals(1, false);
        }

        // Convert sender_id to number for consistent comparison
        const senderId = Number(letter.sender_id);

        // Check if sender is already in pen pals list
        let senderPenPal = penPals.value.find(
            (pal) => Number(pal.id) === senderId
        );

        // If not found, add sender to pen pals list
        if (!senderPenPal) {
            senderPenPal = {
                id: senderId,
                name: letter.sender_name || "Unknown",
                avatar: null,
                has_messages: false,
                unread_count: 0,
            };
            penPals.value.push(senderPenPal);
        }

        // Wait for Vue to update the reactive state (recipientOptions computed)
        await nextTick();
        // Small additional delay to ensure Select component updates
        await new Promise((resolve) => setTimeout(resolve, 50));

        // Pre-select the sender as recipient (ensure it's a number)
        selectedRecipient.value = senderId;

        // Open writing interface
        showWritingInterface.value = true;
    } catch (error) {
        console.error("Error loading letter for reply:", error);
        alert("Failed to load letter. Please try again.");
    }
};

// Handle write new letter
const handleWriteNew = () => {
    // Clear original letter when writing new (not replying)
    originalLetter.value = null;
    
    // If viewing correspondence, pre-fill the recipient
    if (selectedPenPal.value) {
        selectedRecipient.value = selectedPenPal.value.id;
    } else {
        // Default to open letter if no pen pal selected
        selectedRecipient.value = null;
    }
    showWritingInterface.value = true;
};

// Handle cancel/back from writing interface
const handleCancelWrite = () => {
    // Clear original letter when canceling
    originalLetter.value = null;
    showWritingInterface.value = false;
    letterContent.value = "";
    selectedRecipient.value = null;
    sendError.value = null;
};

// Character count computed
const characterCount = computed(() => letterContent.value.length);
const maxCharacters = 1000;
const isOverLimit = computed(() => characterCount.value > maxCharacters);

// Recipient options for dropdown (Open Letter + pen pals)
const recipientOptions = computed(() => {
    const options = [{ value: null, label: "Open Letter", isOpenLetter: true }];

    // Add pen pals
    penPals.value.forEach((pal) => {
        options.push({
            value: pal.id,
            label: pal.name,
            isOpenLetter: false,
        });
    });

    return options;
});

// Handle content input with character limit
const handleContentInput = (event) => {
    const value = event.target.value;
    if (value.length <= maxCharacters) {
        letterContent.value = value;
    } else {
        // Prevent typing past limit
        letterContent.value = value.substring(0, maxCharacters);
        event.target.value = letterContent.value;
    }
    sendError.value = null; // Clear error on input
};

// Handle send letter
const handleSendLetter = async () => {
    // Validation
    if (!letterContent.value.trim()) {
        sendError.value = "Message is required";
        return;
    }

    if (isOverLimit.value) {
        sendError.value = `Message cannot exceed ${maxCharacters} characters`;
        return;
    }

    isSending.value = true;
    sendError.value = null;

    try {
        const isOpenLetter = selectedRecipient.value === null;
        const payload = {
            content: letterContent.value.trim(),
            is_open_letter: isOpenLetter,
        };

        // Add receiver_id only if it's not an open letter
        if (!isOpenLetter && selectedRecipient.value) {
            payload.receiver_id = selectedRecipient.value;
        }

        const response = await axios.post("/api/letters", payload);

        // Success - handle based on recipient type
        if (isOpenLetter) {
            // Open letter - just close the interface
            handleCancelWrite();
            // Optionally show a success message or refresh the discover page
        } else {
            // Pen pal letter - select the pen pal and show correspondence
            const recipientPal = penPals.value.find(
                (pal) => pal.id === selectedRecipient.value
            );

            if (recipientPal) {
                // Close the writing interface first
                showWritingInterface.value = false;
                letterContent.value = "";
                selectedRecipient.value = null;
                sendError.value = null;

                // Select the pen pal (this will load correspondence and show the new message)
                selectPenPal(recipientPal);
            } else {
                // Pen pal not found in list, just close
                handleCancelWrite();
            }
        }
    } catch (error) {
        console.error("Error sending letter:", error);
        if (error.response?.data?.errors) {
            // Validation errors from API
            const errors = error.response.data.errors;
            if (errors.content) {
                sendError.value = errors.content[0];
            } else if (errors.receiver_id) {
                sendError.value = errors.receiver_id[0];
            } else {
                sendError.value = "Failed to send letter. Please try again.";
            }
        } else {
            sendError.value =
                error.response?.data?.message ||
                "Failed to send letter. Please try again.";
        }
    } finally {
        isSending.value = false;
    }
};

// Report letter functions
const openReportModal = (letter) => {
    selectedLetter.value = letter;
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
};

// Track window width for responsive behavior
const windowWidth = ref(
    typeof window !== "undefined" ? window.innerWidth : 1024
);

const updateWindowWidth = () => {
    windowWidth.value = window.innerWidth;
};

onMounted(async () => {
    if (typeof window !== "undefined") {
        window.addEventListener("resize", updateWindowWidth);
    }
    // Load data when component mounts
    loadIncomingLetters();
    await loadPenPals(1, false);

    // Check for letterId query parameter for reply functionality
    const urlParams = new URLSearchParams(window.location.search);
    const letterId = urlParams.get("letterId");
    if (letterId) {
        // Remove the query parameter from URL
        const url = new URL(window.location.href);
        url.searchParams.delete("letterId");
        window.history.replaceState({}, "", url);

        // Handle reply
        await handleReply(letterId);
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
                    <!-- Correspondence Sidebar/List (Search, Incoming, Pen Pals) - Mobile: hidden when pen pal selected or writing, Desktop: always visible unless writing -->
                    <div
                        v-if="
                            (!selectedPenPal && !showWritingInterface) ||
                            (windowWidth >= 1024 && !showWritingInterface)
                        "
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

                    <!-- Writing Interface - Mobile: replaces main content, Desktop: replaces search/filters/messages -->
                    <div
                        v-if="showWritingInterface"
                        class="flex-1 transition-all duration-300 w-full"
                    >
                        <!-- Mobile Back Button -->
                        <div
                            class="lg:hidden flex items-center gap-2 pb-2 px-2 border-b border-gray-200"
                        >
                            <button
                                @click="handleCancelWrite"
                                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                aria-label="Cancel writing"
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
                            <h3 class="text-lg font-bold text-pressed">
                                Write Letter
                            </h3>
                        </div>

                        <!-- Desktop Header -->
                        <div
                            class="hidden lg:block mb-3 pb-2 border-b border-gray-200"
                        >
                            <div class="flex items-center gap-2">
                                <button
                                    @click="handleCancelWrite"
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                    aria-label="Cancel writing"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-5 text-hover"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M9.53 2.47a.75.75 0 0 1 0 1.06L4.81 8.25H15a6.75 6.75 0 0 1 0 13.5h-3a.75.75 0 0 1 0-1.5h3a5.25 5.25 0 1 0 0-10.5H4.81l4.72 4.72a.75.75 0 1 1-1.06 1.06l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 0 1 1.06 0Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                                <h3 class="text-lg font-bold text-pressed">
                                    Write Letter
                                </h3>
                            </div>
                        </div>

                        <!-- Writing Form -->
                        <div class="space-y-4">
                            <!-- To Field (Recipient Dropdown) -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label
                                        for="recipient"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        To:
                                    </label>
                                    <!-- Preview Original Letter Button (only when replying) -->
                                    <button
                                        v-if="originalLetter"
                                        @click="showPreviewModal = true"
                                        type="button"
                                        class="inline-flex items-center gap-2 bg-primary hover:bg-pressed text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors shadow-sm"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                            class="size-4"
                                        >
                                            <path
                                                d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                            />
                                            <path
                                                fill-rule="evenodd"
                                                d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        Preview original letter
                                    </button>
                                </div>
                                <Select
                                    id="recipient"
                                    v-model="selectedRecipient"
                                    :options="recipientOptions"
                                />
                            </div>

                            <!-- Message Content -->
                            <div>
                                <label
                                    for="letterContent"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Message:
                                </label>
                                <textarea
                                    id="letterContent"
                                    :value="letterContent"
                                    @input="handleContentInput"
                                    rows="12"
                                    class="w-full px-4 py-3 border-2 border-gray-300 bg-white text-black rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary resize-none placeholder:text-gray-500"
                                    placeholder="Write your message here..."
                                    :maxlength="maxCharacters"
                                ></textarea>

                                <!-- Character Counter -->
                                <div
                                    class="mt-1 flex justify-between items-center"
                                >
                                    <p
                                        v-if="sendError"
                                        class="text-sm text-red-600"
                                    >
                                        {{ sendError }}
                                    </p>
                                    <p v-else class="text-sm text-gray-500">
                                        {{ characterCount }} /
                                        {{ maxCharacters }} characters
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Section (Correspondence View) - Mobile: replaces main content, Desktop: takes remaining space -->
                    <div
                        v-else-if="selectedPenPal"
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

            <!-- Floating Write Button - Hidden when writing interface is open -->
            <button
                v-if="!showWritingInterface"
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

            <!-- Floating Send Button - Only visible when writing interface is open -->
            <button
                v-if="showWritingInterface"
                @click="handleSendLetter"
                :disabled="isSending || !letterContent.trim() || isOverLimit"
                class="fixed bottom-20 right-4 lg:bottom-4 bg-accent hover:bg-pressed text-hover rounded-full p-4 lg:p-6 transition-colors flex items-center justify-center z-50 w-fit shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                aria-label="Send letter"
            >
                <svg
                    v-if="isSending"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="size-6 lg:size-8 animate-spin"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"
                    />
                </svg>
                <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="size-6 lg:size-8"
                >
                    <path
                        d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z"
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
            @report="handleReport"
        />

        <!-- Preview Original Letter Modal (for reply context) -->
        <LetterViewModal
            :show="showPreviewModal"
            :letter="originalLetter"
            :hide-reply-button="true"
            @close="showPreviewModal = false"
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
