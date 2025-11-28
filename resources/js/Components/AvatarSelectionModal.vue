<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from './Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    availableAvatars: {
        type: Array,
        required: true,
    },
    currentAvatar: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close', 'selected']);

const form = useForm({
    avatar: null,
});

const selectedAvatar = ref(props.currentAvatar);

// Watch for changes to currentAvatar prop
watch(() => props.currentAvatar, (newValue) => {
    selectedAvatar.value = newValue;
});

const selectAvatar = (avatar) => {
    selectedAvatar.value = avatar;
    form.avatar = avatar || null;
    form.put(route('profile.avatar.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Reload the current page to refresh shared props (auth.user) so dropdown updates
            router.reload();
            emit('selected', avatar);
            emit('close');
        },
    });
};

const closeModal = () => {
    emit('close');
};

const getAvatarUrl = (avatar) => {
    return `/images/avatars/${avatar}`;
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="p-6 bg-background">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                    <h3 class="text-3xl lg:text-4xl font-bold text-black">
                        Select Avatar
                    </h3>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
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

            <!-- Scrollable Avatar Grid -->
            <div
                class="max-h-[60vh] overflow-y-auto pr-2"
                style="scrollbar-width: thin; scrollbar-color: #B8B8FF #DCDCFF"
            >
                <!-- Mobile: 2 Column Grid -->
                <div class="grid grid-cols-2 sm:hidden gap-4 pb-4">
                    <button
                        v-for="avatar in availableAvatars"
                        :key="avatar"
                        type="button"
                        @click="selectAvatar(avatar)"
                        :disabled="form.processing"
                        :class="[
                            'relative w-24 h-24 mx-auto rounded-full overflow-hidden border-4 transition-all',
                            selectedAvatar === avatar
                                ? 'border-primary ring-4 ring-primary ring-offset-2 ring-offset-background scale-105'
                                : 'border-gray-300 hover:border-primary/50',
                            form.processing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                        ]"
                    >
                        <img
                            :src="getAvatarUrl(avatar)"
                            :alt="`Avatar ${avatar}`"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-if="selectedAvatar === avatar"
                            class="absolute inset-0 bg-primary/20 flex items-center justify-center"
                        >
                            <svg
                                class="w-8 h-8 text-white drop-shadow-lg"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="3"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>
                    </button>
                </div>

                <!-- Desktop: Grid Layout -->
                <div class="hidden sm:grid sm:grid-cols-4 gap-6 pb-4 px-2">
                    <button
                        v-for="avatar in availableAvatars"
                        :key="avatar"
                        type="button"
                        @click="selectAvatar(avatar)"
                        :disabled="form.processing"
                        :class="[
                            'relative w-24 h-24 rounded-full overflow-hidden border-4 transition-all',
                            selectedAvatar === avatar
                                ? 'border-primary ring-4 ring-primary ring-offset-2 ring-offset-background scale-105'
                                : 'border-gray-300 hover:border-primary/50',
                            form.processing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                        ]"
                    >
                        <img
                            :src="getAvatarUrl(avatar)"
                            :alt="`Avatar ${avatar}`"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-if="selectedAvatar === avatar"
                            class="absolute inset-0 bg-primary/20 flex items-center justify-center"
                        >
                            <svg
                                class="w-10 h-10 text-white drop-shadow-lg"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="3"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Remove Avatar Option -->
            <div
                v-if="selectedAvatar"
                class="mt-6 pt-6 border-t border-gray-200 flex justify-center"
            >
                <button
                    type="button"
                    @click="selectAvatar(null)"
                    :disabled="form.processing"
                            class="text-lg lg:text-xl text-gray-600 hover:text-gray-800 underline disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Remove Avatar
                </button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* Custom scrollbar styling for webkit browsers */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #DCDCFF;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #B8B8FF;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #9B9BFF;
}
</style>

