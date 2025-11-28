<script setup>
import { ref, watch } from 'vue';
import Avatar from './Avatar.vue';
import AvatarSelectionModal from './AvatarSelectionModal.vue';

const props = defineProps({
    availableAvatars: {
        type: Array,
        required: true,
    },
    currentAvatar: {
        type: String,
        default: null,
    },
    userName: {
        type: String,
        default: '',
    },
});

const showModal = ref(false);
const selectedAvatar = ref(props.currentAvatar);

// Watch for changes to currentAvatar prop
watch(() => props.currentAvatar, (newValue) => {
    selectedAvatar.value = newValue;
});

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const handleAvatarSelected = (avatar) => {
    selectedAvatar.value = avatar;
};

const getAvatarUrl = (avatar) => {
    return `/images/avatars/${avatar}`;
};
</script>

<template>
    <div class="space-y-4">
        <div>
            <h4 class="text-xl lg:text-2xl font-medium text-white mb-2">
                Profile Avatar
            </h4>
            <p class="text-lg lg:text-xl text-white/80 mb-4">
                Click on your avatar to change it
            </p>
        </div>

        <!-- Clickable Current Avatar Display -->
        <div class="flex items-center gap-4">
            <button
                type="button"
                @click="openModal"
                class="relative group focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary rounded-full transition-transform hover:scale-105"
            >
                <Avatar
                    :src="selectedAvatar ? getAvatarUrl(selectedAvatar) : null"
                    :name="userName"
                    size="xl"
                    border-color="white"
                />
                <!-- Edit Icon Overlay -->
                <div
                    class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                >
                    <svg
                        class="w-8 h-8 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                        />
                    </svg>
                </div>
            </button>
            <div>
                <p class="text-lg lg:text-xl text-white font-medium">Current Avatar</p>
                <p v-if="!selectedAvatar" class="text-base lg:text-lg text-white/60">
                    No avatar selected. Click to choose one.
                </p>
                <p v-else class="text-base lg:text-lg text-white/60">
                    Click to change avatar
                </p>
            </div>
        </div>

        <!-- Avatar Selection Modal -->
        <AvatarSelectionModal
            :show="showModal"
            :available-avatars="availableAvatars"
            :current-avatar="selectedAvatar"
            @close="closeModal"
            @selected="handleAvatarSelected"
        />
    </div>
</template>

