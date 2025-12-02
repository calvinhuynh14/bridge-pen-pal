<script setup>
import { defineProps, defineEmits, computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import Avatar from "@/Components/Avatar.vue";

const props = defineProps({
    letter: {
        type: Object,
        required: true,
    },
    isClaiming: {
        type: Boolean,
        default: false,
    },
    showReplyButton: {
        type: Boolean,
        default: true,
    },
    showStatus: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["claim", "report", "view", "reply"]);

// Get current user
const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

// Check if letter is undelivered
// For senders: letter is undelivered if delivered_at > now
// For recipients: letter is undelivered if delivered_at > now (shouldn't appear, but check anyway)
const isUndelivered = computed(() => {
    if (!props.letter.delivered_at) return false;
    const deliveredAt = new Date(props.letter.delivered_at);
    const now = new Date();
    // Use getTime() for accurate comparison to avoid timezone issues
    return deliveredAt.getTime() > now.getTime();
});

// Check if current user is the recipient and letter is undelivered
const isRecipientUndelivered = computed(() => {
    return props.letter.receiver_id === currentUserId.value && isUndelivered.value;
});

// Hover state for common interests tooltip
const showCommonInterests = ref(false);

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

// Calculate hours remaining consistently
const getHoursRemaining = () => {
    if (!props.letter.delivered_at) return 0;
    const deliveredAt = new Date(props.letter.delivered_at);
    const now = new Date();
    const timeRemaining = deliveredAt.getTime() - now.getTime();
    // Only return hours if letter is actually in transit (not already delivered)
    if (timeRemaining <= 0) return 0;
    return Math.max(0, Math.ceil(timeRemaining / (1000 * 60 * 60)));
};

// Status icon configuration - perspective-aware
const statusConfig = computed(() => {
    const status = props.letter.status || "draft";
    const isSender = props.letter.sender_id === currentUserId.value;
    const isReceiver = props.letter.receiver_id === currentUserId.value;


    // Determine what status to show based on perspective
    let displayStatus = status;

    if (isReceiver) {
        // Receiver perspective:
        // - If status is "sent" but they can see it, it's been delivered → show "delivered"
        // - If status is "delivered" → show "delivered" (unread)
        // - If status is "read" → show "read"
        if (status === "sent") {
            // If receiver can see the letter, it's been delivered to them
            displayStatus = "delivered";
        }
        // Otherwise, use the actual status (delivered or read)
    } else if (isSender) {
        // Sender perspective: sent → delivered → read
        // Show the actual status as it reflects the progression
        // Status already reflects: sent → delivered → read
    }


    switch (displayStatus) {
        case "sent":
            return {
                icon: "M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z",
                color: "text-blue-500",
                bgColor: "bg-blue-100",
                title: "Sent",
            };
        case "delivered":
            return {
                icon: "M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z",
                color: "text-yellow-500",
                bgColor: "bg-yellow-100",
                title: "Delivered",
            };
        case "read":
            return {
                icon: "M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z",
                icon2: "M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z",
                color: "text-green-500",
                bgColor: "bg-green-100",
                title: "Read",
            };
        default:
            return null; // Don't show icon for draft
    }
});

const handleCardClick = (event) => {
    // Don't emit view if clicking on buttons or their children
    const target = event.target;
    const isButton = target.closest("button");
    
    // Prevent viewing if recipient and letter is undelivered
    if (isRecipientUndelivered.value) {
        return;
    }
    
    if (!isButton) {
        emit("view", props.letter);
    }
};

const handleClaim = (event) => {
    event.stopPropagation();
    emit("claim", props.letter.id);
};

const handleReply = (event) => {
    event.stopPropagation();
    emit("reply", props.letter.id);
};

const handleReport = (event) => {
    event.stopPropagation();
    emit("report", props.letter);
};
</script>

<template>
    <article
        @click="handleCardClick"
        class="border-2 rounded-sm p-1.5 sm:p-3 md:p-4 shadow-md hover:shadow-lg transition-shadow relative flex flex-col cursor-pointer w-full max-h-[300px] sm:max-h-none mx-auto sm:mx-0"
        :class="{
            'border-gray-300': !isUndelivered,
            'border-gray-400': isUndelivered,
            'opacity-60': isUndelivered,
        }"
        style="
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1),
                0 1px 2px rgba(0, 0, 0, 0.06);
            aspect-ratio: 3 / 4;
        "
        :aria-label="`Letter from ${letter.sender_name} dated ${formatDate(letter.sent_at)}${isUndelivered ? ' (in transit)' : ''}`"
        role="article"
    >
        <!-- Status Icon (Bottom Right) -->
        <div
            v-if="showStatus && statusConfig"
            class="absolute bottom-1.5 right-1.5 sm:bottom-2 sm:right-2 z-10"
            :title="statusConfig.title"
            role="img"
            :aria-label="`Letter status: ${statusConfig.title}`"
        >
            <div
                class="rounded-full p-1 sm:p-1.5"
                :class="statusConfig.bgColor"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="w-3 h-3 sm:w-4 sm:h-4"
                    :class="statusConfig.color"
                    aria-hidden="true"
                >
                    <path :d="statusConfig.icon" />
                    <path
                        v-if="statusConfig.icon2"
                        fill-rule="evenodd"
                        :d="statusConfig.icon2"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
        </div>

        <!-- Avatar and Name -->
        <header 
            class="flex items-center gap-1.5 sm:gap-2 mb-1.5 sm:mb-3 relative"
            @mouseenter="showCommonInterests = true"
            @mouseleave="showCommonInterests = false"
        >
            <div class="flex-shrink-0">
                <Avatar
                    :src="letter.sender_avatar ? `/images/avatars/${letter.sender_avatar}` : null"
                    :name="letter.sender_name"
                    size="custom"
                    custom-size="w-6 h-6 sm:w-9 sm:h-9 md:w-10 md:h-10"
                    custom-text-size="text-xs sm:text-base md:text-lg"
                    border-color="pressed"
                    bg-color="primary"
                    text-color="white"
                />
            </div>
            <div class="flex-1 min-w-0">
                <p
                    class="font-semibold text-primary text-xs sm:text-base md:text-lg truncate"
                >
                    {{ letter.sender_name }}
                </p>
                <time 
                    class="text-xs sm:text-sm text-gray-500"
                    :datetime="letter.sent_at"
                >
                    {{ formatDate(letter.sent_at) }}
                </time>
            </div>
            
            <!-- Common Interests/Languages Tooltip -->
            <div
                v-if="showCommonInterests"
                class="absolute left-0 top-full mt-2 z-50 bg-white border-2 border-primary rounded-lg shadow-xl p-3 min-w-[200px] max-w-[300px]"
                role="tooltip"
                aria-label="Common interests and languages"
            >
                <!-- Common Interests -->
                <div v-if="letter.common_interests && letter.common_interests.length > 0" class="mb-3">
                    <div class="text-xs font-semibold text-primary mb-2">
                        Common Interests:
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            v-for="(interest, index) in letter.common_interests"
                            :key="`interest-${index}`"
                            class="inline-block px-2 py-1 bg-primary text-white text-xs rounded-md whitespace-nowrap"
                        >
                            {{ interest }}
                        </span>
                    </div>
                </div>
                
                <!-- Common Languages -->
                <div v-if="letter.common_languages && letter.common_languages.length > 0" :class="{'mb-3': (!letter.common_interests || letter.common_interests.length === 0)}">
                    <div class="text-xs font-semibold text-primary mb-2">
                        Common Languages:
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            v-for="(language, index) in letter.common_languages"
                            :key="`language-${index}`"
                            class="inline-block px-2 py-1 bg-accent text-black text-xs rounded-md whitespace-nowrap"
                        >
                            {{ language }}
                        </span>
                    </div>
                </div>
                
                <!-- No Common Interests or Languages -->
                <div v-if="(!letter.common_interests || letter.common_interests.length === 0) && (!letter.common_languages || letter.common_languages.length === 0)" class="text-xs text-gray-600 italic">
                    No common interests or languages
                </div>
            </div>
        </header>

        <!-- Letter Content Preview -->
        <div
            class="flex-1 mb-1 sm:mb-2 md:mb-3 min-h-[30px] sm:min-h-[60px] md:min-h-[80px] overflow-hidden"
        >
            <p
                class="text-xs sm:text-sm md:text-base leading-relaxed"
                :class="{
                    'text-gray-700': !isUndelivered,
                    'text-gray-500': isUndelivered,
                }"
                style="
                    display: -webkit-box;
                    -webkit-line-clamp: 4;
                    line-clamp: 4;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                "
            >
                {{ letter.content }}
            </p>
            <!-- Undelivered indicator -->
            <div
                v-if="isUndelivered"
                class="mt-2 text-xs text-gray-500 italic flex items-center gap-1"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="w-3 h-3"
                    aria-hidden="true"
                >
                    <path
                        d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z"
                    />
                </svg>
                <span>In transit - will arrive in {{ getHoursRemaining() }} hour{{ getHoursRemaining() !== 1 ? 's' : '' }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div
            class="flex items-center justify-between gap-1 sm:gap-2 mt-auto pt-1.5 sm:pt-3 border-t border-gray-200"
            :class="{
                'justify-end': !showReplyButton,
            }"
        >
            <button
                v-if="showReplyButton"
                @click="handleReply"
                :disabled="isClaiming"
                class="px-1.5 py-1 sm:px-3 sm:py-2 bg-primary hover:bg-pressed text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm md:text-base flex-1 flex items-center justify-center gap-0.5 sm:gap-1 min-h-[28px] sm:min-h-0 max-w-[100px] sm:max-w-none mx-auto focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                aria-label="Reply to this letter"
            >
                <span v-if="isClaiming" class="text-xs sm:text-sm">
                    Replying...
                </span>
                <span v-else class="flex items-center gap-0.5 sm:gap-1">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="size-3 sm:size-4 md:size-5"
                        aria-hidden="true"
                    >
                        <path
                            d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"
                        />
                        <path
                            d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"
                        />
                    </svg>
                    <span class="hidden sm:inline">Reply</span>
                </span>
            </button>
            <button
                @click="handleReport"
                class="px-1.5 py-1 sm:px-2.5 sm:py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg font-medium transition-colors text-xs sm:text-sm md:text-base flex items-center justify-center min-h-[28px] sm:min-h-0 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                aria-label="Report this letter"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="size-2.5 sm:size-4 md:size-5"
                    aria-hidden="true"
                >
                    <path
                        fill-rule="evenodd"
                        d="M3 2.25a.75.75 0 0 1 .75.75v.54l1.838-.46a9.75 9.75 0 0 1 6.725.738l.108.054A8.25 8.25 0 0 0 18 4.524l3.11-.732a.75.75 0 0 1 .917.81 47.784 47.784 0 0 0 .005 10.337.75.75 0 0 1-.574.812l-3.114.733a9.75 9.75 0 0 1-6.594-.77l-.108-.054a8.25 8.25 0 0 0-5.69-.625l-2.202.55V21a.75.75 0 0 1-1.5 0V3A.75.75 0 0 1 3 2.25Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>
    </article>
</template>
