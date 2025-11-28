<script setup>
import SelectionModal from "./SelectionModal.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    availableInterests: {
        type: Array,
        default: () => [],
    },
    currentInterests: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close"]);
</script>

<template>
    <SelectionModal
        :show="show"
        title="Select Interests"
        :items="availableInterests"
        :current-selection="currentInterests"
        item-key="id"
        item-label="name"
        update-route="profile.interests.update"
        form-field-name="interests"
        @close="emit('close')"
    />
</template>

