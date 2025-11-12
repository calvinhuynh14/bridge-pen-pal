<script setup>
import { defineProps, defineEmits } from "vue";
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
});

const emit = defineEmits(["claim", "report", "view"]);

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

const handleCardClick = (event) => {
    // Don't emit view if clicking on buttons or their children
    const target = event.target;
    const isButton = target.closest("button");
    if (!isButton) {
        emit("view", props.letter);
    }
};

const handleClaim = (event) => {
    event.stopPropagation();
    emit("claim", props.letter.id);
};

const handleReport = (event) => {
    event.stopPropagation();
    emit("report", props.letter);
};
</script>

<template>
    <div
        @click="handleCardClick"
        class="border-2 border-gray-300 rounded-sm p-1.5 sm:p-3 md:p-4 shadow-md hover:shadow-lg transition-shadow relative flex flex-col cursor-pointer w-full max-h-[300px] sm:max-h-none mx-auto sm:mx-0"
        style="
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1),
                0 1px 2px rgba(0, 0, 0, 0.06);
            aspect-ratio: 3 / 4;
        "
    >
        <!-- Avatar and Name -->
        <div class="flex items-center gap-1.5 sm:gap-2 mb-1.5 sm:mb-3">
            <div class="flex-shrink-0">
                <Avatar
                    :src="letter.sender_avatar || letter.avatar"
                    :name="letter.sender_name"
                    size="custom"
                    custom-size="w-6 h-6 sm:w-9 sm:h-9 md:w-10 md:h-10"
                    custom-text-size="text-xs sm:text-base md:text-lg"
                    border-color="pressed"
                    bg-color="primary"
                    text-color="black"
                />
            </div>
            <div class="flex-1 min-w-0">
                <p
                    class="font-semibold text-pressed text-xs sm:text-base md:text-lg truncate"
                >
                    {{ letter.sender_name }}
                </p>
                <p class="text-xs sm:text-sm text-gray-500">
                    {{ formatDate(letter.sent_at) }}
                </p>
            </div>
        </div>

        <!-- Letter Content Preview -->
        <div
            class="flex-1 mb-1 sm:mb-2 md:mb-3 min-h-[30px] sm:min-h-[60px] md:min-h-[80px] overflow-hidden"
        >
            <p
                class="text-gray-700 text-xs sm:text-sm md:text-base leading-relaxed"
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
                @click="handleClaim"
                :disabled="isClaiming"
                class="px-1.5 py-1 sm:px-3 sm:py-2 bg-primary hover:bg-pressed text-black rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm md:text-base flex-1 flex items-center justify-center gap-0.5 sm:gap-1 min-h-[28px] sm:min-h-0 max-w-[100px] sm:max-w-none mx-auto"
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
                class="px-1.5 py-1 sm:px-2.5 sm:py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg font-medium transition-colors text-xs sm:text-sm md:text-base flex items-center justify-center min-h-[28px] sm:min-h-0"
                title="Report this letter"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="size-2.5 sm:size-4 md:size-5"
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
</template>
