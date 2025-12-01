<script setup>
import { ref, computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "./Modal.vue";
import CustomButton from "./CustomButton.vue";

const props = defineProps({
    show: {
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

const emit = defineEmits(["close", "completed"]);

// Current page (1 = interests, 2 = languages)
const currentPage = ref(1);

// Form for interests
const interestsForm = useForm({
    interests: props.userInterests || [],
});

// Form for languages
const languagesForm = useForm({
    languages: props.userLanguages || [],
});

// Selected items
const selectedInterests = ref(new Set(props.userInterests || []));
const selectedLanguages = ref(new Set(props.userLanguages || []));

// Watch for prop changes
watch(
    () => props.userInterests,
    (newValue) => {
        selectedInterests.value = new Set(newValue || []);
        interestsForm.interests = newValue || [];
    }
);

watch(
    () => props.userLanguages,
    (newValue) => {
        selectedLanguages.value = new Set(newValue || []);
        languagesForm.languages = newValue || [];
    }
);

// Reset when modal closes
watch(
    () => props.show,
    (isOpen) => {
        if (!isOpen) {
            currentPage.value = 1;
            selectedInterests.value = new Set(props.userInterests || []);
            selectedLanguages.value = new Set(props.userLanguages || []);
            interestsForm.interests = props.userInterests || [];
            languagesForm.languages = props.userLanguages || [];
        }
    }
);

const toggleInterest = (interestId) => {
    if (selectedInterests.value.has(interestId)) {
        selectedInterests.value.delete(interestId);
    } else {
        selectedInterests.value.add(interestId);
    }
    interestsForm.interests = Array.from(selectedInterests.value);
};

const toggleLanguage = (languageId) => {
    if (selectedLanguages.value.has(languageId)) {
        selectedLanguages.value.delete(languageId);
    } else {
        selectedLanguages.value.add(languageId);
    }
    languagesForm.languages = Array.from(selectedLanguages.value);
};

const isInterestSelected = (interestId) => {
    return selectedInterests.value.has(interestId);
};

const isLanguageSelected = (languageId) => {
    return selectedLanguages.value.has(languageId);
};

const nextPage = () => {
    if (currentPage.value === 1) {
        // Save interests first
        interestsForm.put(route("profile.interests.update"), {
            preserveScroll: true,
            onSuccess: () => {
                currentPage.value = 2;
            },
        });
    }
};

const previousPage = () => {
    if (currentPage.value === 2) {
        currentPage.value = 1;
    }
};

const saveAndClose = () => {
    // Save languages
    languagesForm.put(route("profile.languages.update"), {
        preserveScroll: true,
        onSuccess: () => {
            emit("completed");
            emit("close");
        },
    });
};

const canProceed = computed(() => {
    if (currentPage.value === 1) {
        return selectedInterests.value.size > 0;
    }
    return selectedLanguages.value.size > 0;
});

const pageTitle = computed(() => {
    return currentPage.value === 1 ? "Select Your Interests" : "Select Your Languages";
});

const pageDescription = computed(() => {
    if (currentPage.value === 1) {
        return "Choose your interests to help us match you with like-minded pen pals. You can select multiple interests.";
    }
    return "Select the languages you speak. This helps us connect you with others who share your languages.";
});

</script>

<template>
    <Modal
        :show="show"
        max-width="2xl"
        :title="pageTitle"
        :description="pageDescription"
        header-bg="primary"
        :closeable="false"
        @close="() => {}"
    >
        <!-- Content -->
        <div class="bg-white px-6 py-4">
            <!-- Interests Page -->
            <div v-if="currentPage === 1" class="space-y-4">
                <div
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-[400px] overflow-y-auto p-2"
                >
                    <button
                        v-for="interest in availableInterests"
                        :key="interest.id"
                        @click="toggleInterest(interest.id)"
                        :class="[
                            'px-3 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary',
                            isInterestSelected(interest.id)
                                ? 'bg-primary text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                        :aria-pressed="isInterestSelected(interest.id)"
                        :aria-label="`${isInterestSelected(interest.id) ? 'Deselect' : 'Select'} ${interest.name}`"
                    >
                        {{ interest.name }}
                    </button>
                </div>
            </div>

            <!-- Languages Page -->
            <div v-if="currentPage === 2" class="space-y-4">
                <div
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-[400px] overflow-y-auto p-2"
                >
                    <button
                        v-for="language in availableLanguages"
                        :key="language.id"
                        @click="toggleLanguage(language.id)"
                        :class="[
                            'px-3 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary',
                            isLanguageSelected(language.id)
                                ? 'bg-primary text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                        :aria-pressed="isLanguageSelected(language.id)"
                        :aria-label="`${isLanguageSelected(language.id) ? 'Deselect' : 'Select'} ${language.name}`"
                    >
                        {{ language.name }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div
            class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3"
        >
            <!-- Next/Save Button -->
            <CustomButton
                v-if="currentPage === 1"
                text="Next: Languages"
                preset="primary"
                size="small"
                class="w-full sm:w-auto"
                @click="nextPage"
                :disabled="!canProceed || interestsForm.processing"
            />
            <CustomButton
                v-else
                text="Complete Setup"
                preset="primary"
                size="small"
                class="w-full sm:w-auto"
                @click="saveAndClose"
                :disabled="!canProceed || languagesForm.processing"
            />

            <!-- Previous Button (only on languages page) -->
            <CustomButton
                v-if="currentPage === 2"
                text="Back"
                preset="neutral"
                size="small"
                class="mt-3 w-full sm:mt-0 sm:w-auto"
                @click="previousPage"
                :disabled="languagesForm.processing"
            />
        </div>
    </Modal>
</template>

