<script setup>
import SelectionModal from "./SelectionModal.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    availableLanguages: {
        type: Array,
        default: () => [],
    },
    currentLanguages: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close"]);
</script>

<template>
    <SelectionModal
        :show="show"
        title="Select Languages"
        :items="availableLanguages"
        :current-selection="currentLanguages"
        item-key="id"
        item-label="name"
        update-route="profile.languages.update"
        form-field-name="languages"
        @close="emit('close')"
    />
</template>

