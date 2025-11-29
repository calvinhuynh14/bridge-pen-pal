<script setup>
import { computed } from "vue";

const props = defineProps({
    message: {
        type: [String, Object],
        default: null,
    },
    messages: {
        type: Array,
        default: null,
    },
    id: {
        type: String,
        default: null,
    },
    heading: {
        type: String,
        default: "Please fix the following errors:",
    },
});

const errorId = computed(() => props.id || `error-${Math.random().toString(36).substr(2, 9)}`);

const hasErrors = computed(() => {
    if (props.messages && Array.isArray(props.messages) && props.messages.length > 0) {
        return true;
    }
    if (props.message) {
        if (typeof props.message === "object" && Object.keys(props.message).length > 0) {
            return true;
        }
        if (typeof props.message === "string" && props.message.trim() !== "") {
            return true;
        }
    }
    return false;
});

const errorList = computed(() => {
    if (props.messages && Array.isArray(props.messages)) {
        return props.messages;
    }
    if (props.message) {
        if (typeof props.message === "object") {
            return Object.values(props.message).flat();
        }
        if (typeof props.message === "string") {
            return [props.message];
        }
    }
    return [];
});
</script>

<template>
    <div
        v-show="hasErrors"
        :id="errorId"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
        class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg"
    >
        <p v-if="errorList.length === 1" class="text-sm text-red-600 font-medium">
            {{ errorList[0] }}
        </p>
        <div v-else>
            <h4 v-if="heading" class="font-semibold mb-2 text-red-800">
                {{ heading }}
            </h4>
            <ul class="list-disc list-inside space-y-1">
                <li
                    v-for="(error, index) in errorList"
                    :key="index"
                    class="text-sm text-red-600 font-medium"
                >
                    {{ error }}
                </li>
            </ul>
        </div>
    </div>
</template>

