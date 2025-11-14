<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    letter: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "reply"]);

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

const closeModal = () => {
    emit("close");
};

// Close on escape key
const handleEscape = (e) => {
    if (e.key === "Escape" && props.show) {
        closeModal();
    }
};

// Check if landscape
const isLandscape = ref(false);

const checkOrientation = () => {
    if (typeof window !== "undefined") {
        isLandscape.value = window.innerWidth > window.innerHeight;
    }
};

onMounted(() => {
    checkOrientation();
    window.addEventListener("resize", checkOrientation);
    window.addEventListener("orientationchange", checkOrientation);
});

onUnmounted(() => {
    window.removeEventListener("resize", checkOrientation);
    window.removeEventListener("orientationchange", checkOrientation);
});
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-2 sm:p-4"
        @click.self="closeModal"
        @keydown.esc="handleEscape"
    >
        <!-- Letter Modal -->
        <div
            class="relative bg-white rounded-lg shadow-2xl w-[95vw] sm:max-w-lg sm:w-full overflow-hidden flex flex-col"
            style="height: 90vh; max-height: 90vh"
            @click.stop
        >
            <!-- Close Button -->
            <button
                @click="closeModal"
                class="absolute top-2 right-2 sm:top-4 sm:right-4 text-gray-400 hover:text-gray-600 transition-colors z-10 bg-white rounded-full p-1 shadow-sm"
            >
                <svg
                    class="w-5 h-5 sm:w-6 sm:h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    ></path>
                </svg>
            </button>

            <!-- Letter Content (scrollable) -->
            <div
                v-if="letter"
                class="overflow-y-auto p-4 sm:p-6 md:p-8 lg:p-12 flex flex-col"
                style="flex: 1 1 0; min-height: 0"
            >
                <!-- Date (top left) -->
                <div
                    class="text-left text-gray-600 mb-3 sm:mb-4 md:mb-6 text-xs sm:text-sm md:text-base"
                >
                    {{ formatDate(letter.sent_at) }}
                </div>

                <!-- To (top left, larger font) -->
                <div class="mb-4 sm:mb-5 md:mb-6 text-left">
                    <div class="text-gray-700 text-base sm:text-lg md:text-xl">
                        <span class="font-medium">To:</span>
                        <span
                            v-if="
                                letter.is_open_letter || !letter.receiver_name
                            "
                        >
                            Bridge Community
                        </span>
                        <span v-else>
                            {{ letter.receiver_name }}
                        </span>
                    </div>
                </div>

                <!-- Letter Content (main body - compressed width) -->
                <div class="flex-1 mb-4 sm:mb-6 md:mb-8 max-w-md mx-auto">
                    <p
                        class="text-gray-900 text-sm sm:text-base md:text-lg leading-relaxed whitespace-pre-wrap"
                    >
                        {{ letter.content }}
                    </p>
                </div>

                <!-- From (bottom, left-aligned, larger font, pressed color) -->
                <div class="mt-auto pt-4 sm:pt-6 md:pt-8 text-left">
                    <p
                        class="text-pressed font-semibold text-base sm:text-lg md:text-xl"
                    >
                        <span class="font-medium">From:</span>
                        {{ letter.sender_name }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons (outside letter, at bottom of modal) -->
            <div
                class="sticky bottom-0 bg-white border-t border-gray-200 px-4 sm:px-6 md:px-8 py-3 sm:py-4 flex justify-end gap-2 sm:gap-3"
            >
                <button
                    @click="$emit('report', letter)"
                    class="px-3 py-1.5 sm:px-4 sm:py-2 border-2 border-red-300 text-red-600 rounded-lg font-medium hover:bg-red-50 transition-colors flex items-center gap-1 sm:gap-2 text-sm sm:text-base"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="size-4 sm:size-5"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3 2.25a.75.75 0 0 1 .75.75v.54l1.838-.46a9.75 9.75 0 0 1 6.725.738l.108.054A8.25 8.25 0 0 0 18 4.524l3.11-.732a.75.75 0 0 1 .917.81 47.784 47.784 0 0 0 .005 10.337.75.75 0 0 1-.574.812l-3.114.733a9.75 9.75 0 0 1-6.594-.77l-.108-.054a8.25 8.25 0 0 0-5.69-.625l-2.202.55V21a.75.75 0 0 1-1.5 0V3A.75.75 0 0 1 3 2.25Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    Report
                </button>
                <button
                    @click="$emit('reply', letter.id)"
                    class="bg-primary hover:bg-pressed text-black px-4 py-2 sm:px-6 sm:py-3 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-1 sm:gap-2"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="size-4 sm:size-5"
                    >
                        <path
                            d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"
                        />
                        <path
                            d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"
                        />
                    </svg>
                    Reply
                </button>
            </div>
        </div>
    </div>
</template>
