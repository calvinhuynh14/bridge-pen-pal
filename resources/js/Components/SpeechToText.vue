<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import Modal from "@/Components/Modal.vue";
import CustomButton from "@/Components/CustomButton.vue";

const props = defineProps({
    targetId: {
        type: String,
        required: true,
    },
    helpTextId: {
        type: String,
        default: "stt-help-text",
    },
});

const emit = defineEmits(["text-inserted", "error", "start", "stop"]);

// STT State
const isListening = ref(false);
const isProcessing = ref(false);
const statusMessage = ref("");
const errorMessage = ref("");
const showErrorModal = ref(false);
const recognition = ref(null);

// Check if STT is supported
const isSupported = computed(() => {
    if (typeof window === "undefined") return false;

    // Check for Speech Recognition API (Chrome, Edge)
    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;
    return SpeechRecognition !== undefined;
});

// Initialize Speech Recognition
const initRecognition = () => {
    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognitionInstance = new SpeechRecognition();

    recognitionInstance.continuous = false; // Stop after one result
    recognitionInstance.interimResults = true; // Get interim results for better feedback
    recognitionInstance.lang = "en-US";
    recognitionInstance.maxAlternatives = 1; // Only get the best match

    recognitionInstance.onstart = () => {
        isListening.value = true;
        isProcessing.value = false;
        errorMessage.value = "";
        statusMessage.value = "Listening...";
        emit("start");
    };

    recognitionInstance.onresult = (event) => {
        console.log("STT onresult event:", event);
        console.log("STT results:", event.results);
        console.log("STT results length:", event.results.length);

        // Only process final results to avoid duplicate insertions
        let finalTranscript = "";
        let hasFinalResult = false;

        // Check all results for final ones
        for (let i = 0; i < event.results.length; i++) {
            const result = event.results[i];
            if (result.isFinal) {
                finalTranscript += result[0].transcript;
                hasFinalResult = true;
            }
        }

        // Only proceed if we have a final result
        if (!hasFinalResult) {
            console.log("STT: Interim result, waiting for final result...");
            // Update status for interim results (optional visual feedback)
            statusMessage.value = "Listening...";
            return;
        }

        console.log("STT final transcript:", finalTranscript);

        if (!finalTranscript || !finalTranscript.trim()) {
            console.warn("STT: Empty final transcript");
            return;
        }

        // Basic punctuation and capitalization processing
        let processedTranscript = finalTranscript.trim();

        // Capitalize first letter
        if (processedTranscript.length > 0) {
            processedTranscript =
                processedTranscript.charAt(0).toUpperCase() +
                processedTranscript.slice(1);
        }

        // Add a space before inserting (if there's existing text, it will be at cursor position)
        // The parent component handles the actual insertion

        isProcessing.value = true;
        statusMessage.value = "Processing...";

        // Emit the processed text to parent component to handle insertion
        console.log(
            "STT: Emitting text-inserted event with:",
            processedTranscript
        );
        emit("text-inserted", processedTranscript);

        setTimeout(() => {
            isProcessing.value = false;
            statusMessage.value = "Text inserted";

            // Clear status after 2 seconds
            setTimeout(() => {
                if (statusMessage.value === "Text inserted") {
                    statusMessage.value = "";
                }
            }, 2000);
        }, 100);
    };

    recognitionInstance.onerror = (event) => {
        isListening.value = false;
        isProcessing.value = false;

        let errorMsg = "Speech recognition error";

        switch (event.error) {
            case "no-speech":
                errorMsg = "No speech detected. Please try again.";
                break;
            case "audio-capture":
                errorMsg =
                    "Microphone not found. Please check your microphone.";
                break;
            case "not-allowed":
                errorMsg =
                    "Microphone permission denied. Please allow microphone access.";
                break;
            case "network":
                errorMsg = "Network error. Please check your connection.";
                break;
            case "aborted":
                // User stopped manually, don't show error
                statusMessage.value = "";
                return;
            default:
                errorMsg = `Error: ${event.error}`;
        }

        errorMessage.value = errorMsg;
        showErrorModal.value = true;
        statusMessage.value = "";
        emit("error", event.error);
    };

    recognitionInstance.onend = () => {
        isListening.value = false;
        isProcessing.value = false;
        emit("stop");
    };

    recognition.value = recognitionInstance;
};

// Start listening
const startListening = () => {
    if (!isSupported.value) {
        errorMessage.value =
            "Speech-to-text is not supported in your browser. Please use Chrome, Edge, or Safari.";
        showErrorModal.value = true;
        return;
    }

    if (!recognition.value) {
        initRecognition();
    }

    if (recognition.value) {
        try {
            recognition.value.start();
        } catch (error) {
            // Recognition might already be running
            if (error.name !== "InvalidStateError") {
                errorMessage.value = "Failed to start speech recognition.";
                showErrorModal.value = true;
                console.error("STT error:", error);
            }
        }
    }
};

// Stop listening
const stopListening = () => {
    if (recognition.value && isListening.value) {
        recognition.value.stop();
    }
};

// Toggle listening
const toggleListening = () => {
    if (isListening.value) {
        stopListening();
    } else {
        startListening();
    }
};

// Cleanup on unmount
onUnmounted(() => {
    if (recognition.value && isListening.value) {
        recognition.value.stop();
    }
});

// Initialize on mount
onMounted(() => {
    if (isSupported.value) {
        initRecognition();
    }
});

// Expose methods for parent components
defineExpose({
    startListening,
    stopListening,
    toggleListening,
    isListening: computed(() => isListening.value),
});
</script>

<template>
    <div class="speech-to-text-container">
        <!-- Status Announcement (for screen readers) -->
        <div
            v-if="statusMessage || errorMessage"
            role="status"
            aria-live="polite"
            aria-atomic="true"
            class="sr-only"
        >
            {{ errorMessage || statusMessage }}
        </div>

        <!-- Error Modal -->
        <Modal
            :show="showErrorModal"
            max-width="md"
            title="Speech-to-Text Error"
            header-bg="primary"
            @close="showErrorModal = false"
        >
            <!-- Content -->
            <div class="bg-white px-6 py-4">
                <div class="mb-4">
                    <p class="text-sm text-gray-700">
                        {{ errorMessage }}
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3"
            >
                <CustomButton
                    text="Close"
                    preset="neutral"
                    size="small"
                    class="w-full sm:w-auto"
                    @click="showErrorModal = false"
                />
            </div>
        </Modal>

        <!-- Microphone Button - Floating style matching send button -->
        <button
            :aria-label="
                isListening
                    ? 'Stop voice input - click to stop'
                    : 'Start voice input'
            "
            :aria-pressed="isListening ? 'true' : 'false'"
            :aria-describedby="helpTextId"
            :class="[
                'rounded-full p-4 lg:p-6 transition-colors flex items-center justify-center w-fit shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent',
                isListening
                    ? 'bg-red-600 text-white hover:bg-red-700 hover:text-white'
                    : 'bg-accent text-hover hover:bg-pressed hover:text-hover',
            ]"
            @click="toggleListening"
        >
            <svg
                v-if="isListening"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
                class="size-6 lg:size-8"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M4.5 7.5a3 3 0 0 1 3-3h9a3 3 0 0 1 3 3v9a3 3 0 0 1-3 3h-9a3 3 0 0 1-3-3v-9Z"
                    clip-rule="evenodd"
                />
            </svg>
            <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
                class="size-6 lg:size-8"
                aria-hidden="true"
            >
                <path
                    d="M8.25 4.5a3.75 3.75 0 1 1 7.5 0v8.25a3.75 3.75 0 1 1-7.5 0V4.5Z"
                />
                <path
                    d="M6 10.5a.75.75 0 0 1 .75.75v1.5a5.25 5.25 0 1 0 10.5 0v-1.5a.75.75 0 0 1 1.5 0v1.5a6.751 6.751 0 0 1-6 6.709v2.291h3a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1 0-1.5h3v-2.291a6.751 6.751 0 0 1-6-6.709v-1.5A.75.75 0 0 1 6 10.5Z"
                />
            </svg>
            <span class="sr-only">{{
                isListening ? "Stop listening" : "Start voice input"
            }}</span>
        </button>

        <!-- Status Announcement (for screen readers only) -->
        <div
            v-if="statusMessage && !errorMessage"
            role="status"
            aria-live="polite"
            aria-atomic="true"
            class="sr-only"
        >
            {{ statusMessage }}
        </div>

        <!-- Help Text (screen reader only) -->
        <p :id="helpTextId" class="sr-only">
            Click to start voice input. Speak clearly into your microphone.
        </p>
    </div>
</template>

<style scoped>
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}
</style>
