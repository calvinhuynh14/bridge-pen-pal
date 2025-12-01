<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";

const props = defineProps({
    text: {
        type: String,
        required: true,
    },
    contentId: {
        type: String,
        default: "tts-content",
    },
    autoStart: {
        type: Boolean,
        default: false,
    },
    showControls: {
        type: Boolean,
        default: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["start", "stop", "complete", "error"]);

// TTS State
const isPlaying = ref(false);
const currentUtterance = ref(null);
const statusMessage = ref("");
const errorMessage = ref("");

// Voice and Speed Settings
const availableVoices = ref([]);
const selectedVoice = ref(null);
const speechRate = ref(1.0);
const showAdvancedControls = ref(false);

// Check if TTS is supported
const isSupported = computed(() => {
    return typeof window !== "undefined" && "speechSynthesis" in window;
});

// Initialize voices - simplified
const loadVoices = () => {
    if (!isSupported.value) return;

    const voices = window.speechSynthesis.getVoices();

    // Simple filter: English voices, exclude remote/experimental, reasonable length
    availableVoices.value = voices.filter((voice) => {
        if (!voice.lang.startsWith("en")) return false;
        const nameLower = voice.name.toLowerCase();
        return (
            !nameLower.includes("remote") &&
            !nameLower.includes("experimental") &&
            voice.name.length < 50
        );
    });

    // Remove duplicates
    const uniqueVoices = [];
    const seenNames = new Set();
    for (const voice of availableVoices.value) {
        if (!seenNames.has(voice.name)) {
            seenNames.add(voice.name);
            uniqueVoices.push(voice);
        }
    }
    availableVoices.value = uniqueVoices;

    // Set default voice
    if (availableVoices.value.length > 0 && !selectedVoice.value) {
        const preferredVoice = availableVoices.value.find(
            (voice) =>
                voice.name.toLowerCase().includes("zira") ||
                voice.name.toLowerCase().includes("samantha") ||
                voice.default
        );
        selectedVoice.value = preferredVoice || availableVoices.value[0];
    }
};

// Load voices on mount and when they become available
onMounted(() => {
    if (!isSupported.value) {
        errorMessage.value = "Text-to-speech is not supported in your browser.";
        return;
    }

    loadVoices();

    // Some browsers load voices asynchronously
    if (window.speechSynthesis.onvoiceschanged !== undefined) {
        window.speechSynthesis.onvoiceschanged = loadVoices;
    }
});

// Cleanup on unmount
onUnmounted(() => {
    stop();
});

// Watch for auto-start
watch(
    () => props.autoStart,
    (newVal) => {
        if (newVal && !isPlaying.value) {
            play();
        }
    }
);

// Update status message
const updateStatus = (message) => {
    statusMessage.value = message;
    setTimeout(() => {
        if (statusMessage.value === message) {
            statusMessage.value = "";
        }
    }, 3000);
};

// Play TTS
const play = () => {
    if (!isSupported.value) {
        errorMessage.value = "Text-to-speech is not supported in your browser.";
        emit("error", "TTS not supported");
        return;
    }

    // Stop any current speech
    stop();

    if (!props.text || props.text.trim() === "") {
        errorMessage.value = "No text available to read.";
        emit("error", "No text available");
        return;
    }

    // Create new utterance
    const utterance = new SpeechSynthesisUtterance(props.text);

    // Set voice
    if (selectedVoice.value) {
        utterance.voice = selectedVoice.value;
    }

    // Set rate
    utterance.rate = speechRate.value;

    // Set pitch and volume
    utterance.pitch = 1;
    utterance.volume = 1;

    // Event handlers
    utterance.onstart = () => {
        isPlaying.value = true;
        errorMessage.value = "";
        updateStatus("Reading aloud");
        emit("start");
    };

    utterance.onend = () => {
        isPlaying.value = false;
        updateStatus("Finished reading");
        emit("complete");
    };

    utterance.onerror = (event) => {
        isPlaying.value = false;
        errorMessage.value = `Error: ${event.error}`;
        updateStatus("Error reading text");
        emit("error", event.error);
    };

    currentUtterance.value = utterance;
    window.speechSynthesis.speak(utterance);
};

// Stop TTS
const stop = () => {
    if (window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
    }
    isPlaying.value = false;
    currentUtterance.value = null;
    updateStatus("Stopped");
    emit("stop");
};

// Change voice
const changeVoice = (voice) => {
    selectedVoice.value = voice;
    // If currently playing, restart with new voice
    if (isPlaying.value) {
        const wasPlaying = isPlaying.value;
        stop();
        if (wasPlaying) {
            setTimeout(() => play(), 100);
        }
    }
};

// Change speed
const changeSpeed = (rate) => {
    speechRate.value = rate;
    // If currently playing, restart with new speed
    if (isPlaying.value) {
        const wasPlaying = isPlaying.value;
        stop();
        if (wasPlaying) {
            setTimeout(() => play(), 100);
        }
    }
};

// Expose methods for parent components
defineExpose({
    play,
    stop,
    isPlaying: computed(() => isPlaying.value),
});
</script>

<template>
    <div class="text-to-speech-container">
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

        <!-- Error Message (visible) -->
        <div
            v-if="errorMessage && !isSupported"
            role="alert"
            aria-live="assertive"
            class="mb-2 p-2 bg-red-50 border border-red-200 rounded text-red-700 text-sm"
        >
            {{ errorMessage }}
        </div>

        <!-- TTS Controls -->
        <div class="flex items-center gap-2">
            <!-- Play Button -->
            <button
                v-if="!isPlaying"
                :aria-label="'Read aloud'"
                :aria-controls="contentId"
                class="px-2 py-1 text-sm md:px-4 md:py-2 md:text-sm rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 bg-primary text-white hover:bg-hover hover:text-white"
                @click="play"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="w-4 h-4 inline-block"
                    aria-hidden="true"
                >
                    <path
                        fill-rule="evenodd"
                        d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span class="sr-only">Play</span>
            </button>

            <!-- Stop Button -->
            <button
                v-if="isPlaying"
                aria-label="Stop reading"
                class="px-2 py-1 text-sm md:px-4 md:py-2 md:text-sm rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 bg-white text-primary hover:bg-hover hover:text-white border-4 border-primary hover:border-hover"
                @click="stop"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="w-4 h-4 inline-block"
                    aria-hidden="true"
                >
                    <path
                        fill-rule="evenodd"
                        d="M4.5 7.5a3 3 0 0 1 3-3h9a3 3 0 0 1 3 3v9a3 3 0 0 1-3 3h-9a3 3 0 0 1-3-3v-9Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span class="sr-only">Stop</span>
            </button>

            <!-- Status Text (visible) -->
            <span
                v-if="statusMessage && !errorMessage"
                class="text-sm text-gray-600"
                aria-live="polite"
            >
                {{ statusMessage }}
            </span>

            <!-- Advanced Controls Toggle -->
            <button
                v-if="showControls && !compact && availableVoices.length > 0"
                :aria-label="
                    showAdvancedControls
                        ? 'Hide advanced controls'
                        : 'Show advanced controls'
                "
                :aria-expanded="showAdvancedControls"
                class="px-2 py-1 text-sm md:px-4 md:py-2 md:text-sm rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 bg-white text-primary hover:bg-hover hover:text-white border-4 border-primary hover:border-hover"
                @click="showAdvancedControls = !showAdvancedControls"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="w-4 h-4 inline-block"
                    aria-hidden="true"
                >
                    <path
                        d="M18.75 12.75h1.5a.75.75 0 0 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5ZM12 6a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 12 6ZM12 18a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 12 18ZM3.75 6.75h1.5a.75.75 0 1 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5ZM5.25 18.75h-1.5a.75.75 0 0 1 0-1.5h1.5a.75.75 0 0 1 0 1.5ZM3 12a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 3 12ZM9 3.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5ZM12.75 12a2.25 2.25 0 1 1 4.5 0 2.25 2.25 0 0 1-4.5 0ZM9 15.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z"
                    />
                </svg>
                <span class="sr-only">Settings</span>
            </button>
        </div>

        <!-- Advanced Controls -->
        <div
            v-if="showAdvancedControls && showControls && !compact"
            class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200 space-y-3"
            role="group"
            aria-label="Text-to-speech settings"
        >
            <!-- Speed Control -->
            <div>
                <label
                    for="tts-speed"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Reading Speed
                </label>
                <div class="flex items-center gap-2">
                    <input
                        id="tts-speed"
                        type="range"
                        min="0.5"
                        max="2"
                        step="0.1"
                        :value="speechRate"
                        @input="changeSpeed(parseFloat($event.target.value))"
                        class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                        aria-label="Reading speed"
                        :aria-valuenow="speechRate"
                        aria-valuemin="0.5"
                        aria-valuemax="2"
                    />
                    <span class="text-sm text-gray-600 min-w-[3rem] text-right">
                        {{ speechRate.toFixed(1) }}x
                    </span>
                </div>
            </div>

            <!-- Voice Selection -->
            <div v-if="availableVoices.length > 0">
                <label
                    for="tts-voice"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Voice
                </label>
                <select
                    id="tts-voice"
                    :value="selectedVoice ? selectedVoice.name : ''"
                    @change="
                        changeVoice(
                            availableVoices.find(
                                (v) => v.name === $event.target.value
                            )
                        )
                    "
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                    aria-label="Voice selection"
                >
                    <option
                        v-for="voice in availableVoices"
                        :key="voice.name"
                        :value="voice.name"
                    >
                        {{ voice.name }} ({{ voice.lang }})
                    </option>
                </select>
            </div>
        </div>
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
