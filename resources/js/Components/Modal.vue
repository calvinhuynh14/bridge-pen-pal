<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    title: {
        type: String,
        default: null,
    },
    description: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close']);
const dialog = ref();
const showSlot = ref(props.show);
// Generate stable IDs once on component mount
const modalId = `modal-${Math.random().toString(36).substr(2, 9)}`;
const titleId = computed(() => props.title ? `${modalId}-title` : null);
const descriptionId = computed(() => props.description ? `${modalId}-description` : null);
const previousActiveElement = ref(null);

watch(() => props.show, () => {
    if (props.show) {
        // Store the element that triggered the modal
        previousActiveElement.value = document.activeElement;
        document.body.style.overflow = 'hidden';
        showSlot.value = true;
        dialog.value?.showModal();
        // Focus trap: focus the dialog element
        setTimeout(() => {
            dialog.value?.focus();
        }, 100);
    } else {
        document.body.style.overflow = null;
        setTimeout(() => {
            dialog.value?.close();
            showSlot.value = false;
            // Return focus to the element that opened the modal
            if (previousActiveElement.value) {
                previousActiveElement.value.focus();
            }
        }, 200);
    }
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();

        if (props.show && props.closeable) {
            close();
        }
    }
};

// Focus trap: keep focus within modal
const handleFocus = (e) => {
    if (!props.show) return;
    
    const focusableElements = dialog.value?.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    if (!focusableElements || focusableElements.length === 0) return;
    
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    if (e.shiftKey && document.activeElement === firstElement) {
        e.preventDefault();
        lastElement.focus();
    } else if (!e.shiftKey && document.activeElement === lastElement) {
        e.preventDefault();
        firstElement.focus();
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    dialog.value?.addEventListener('keydown', handleFocus);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    dialog.value?.removeEventListener('keydown', handleFocus);
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <dialog 
        class="z-50 m-0 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent" 
        ref="dialog"
        role="dialog"
        :aria-modal="show"
        :aria-labelledby="titleId"
        :aria-describedby="descriptionId"
        tabindex="-1"
    >
        <div class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" scroll-region>
            <transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div 
                    v-show="show" 
                    class="fixed inset-0 transform transition-all" 
                    @click="close"
                    aria-hidden="true"
                >
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75" />
                </div>
            </transition>

            <transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div 
                    v-show="show" 
                    class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto" 
                    :class="maxWidthClass"
                >
                    <!-- Hidden title and description for screen readers if provided -->
                    <h2 v-if="props.title && titleId" :id="titleId" class="sr-only">{{ props.title }}</h2>
                    <p v-if="props.description && descriptionId" :id="descriptionId" class="sr-only">{{ props.description }}</p>
                    <slot v-if="showSlot"/>
                </div>
            </transition>
        </div>
    </dialog>
</template>
