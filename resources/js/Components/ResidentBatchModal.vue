<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    organizationId: Number,
    success: String,
    results: Object,
    errors: Object,
});

const emit = defineEmits(["close"]);

const fileInput = ref(null);
const isUploading = ref(false);

const form = useForm({
    csv_file: null,
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file type
        if (!file.name.toLowerCase().endsWith(".csv")) {
            alert("Please select a CSV file");
            return;
        }

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert("File size must be less than 5MB");
            return;
        }

        // Update form with selected file
        form.csv_file = file;
    }
};

const downloadTemplate = () => {
    window.location.href = route("admin.residents.batch.template");
};

const uploadFile = () => {
    if (!form.csv_file) {
        alert("Please select a CSV file");
        return;
    }

    isUploading.value = true;

    form.post(route("admin.residents.batch.upload"), {
        forceFormData: true,
        onSuccess: () => {
            isUploading.value = false;
            // Reset form after successful upload
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = "";
            }
        },
        onError: () => {
            isUploading.value = false;
        },
    });
};

const closeModal = () => {
    // Reset form when closing
    form.reset();
    if (fileInput.value) {
        fileInput.value.value = "";
    }
    emit("close");
};
</script>

<template>
    <Modal 
        :show="show" 
        @close="closeModal" 
        max-width="2xl"
        title="Batch Create Residents"
        header-bg="primary"
    >
        <div class="bg-white px-6 py-4">

            <!-- Success Message -->
            <div
                v-if="success"
                role="status"
                aria-live="polite"
                aria-atomic="true"
                class="mb-6 p-4 bg-primary border-2 border-pressed text-black rounded-lg"
            >
                {{ success }}
            </div>

            <!-- Error Messages -->
            <div v-if="errors && Object.keys(errors).length > 0" class="mb-6">
                <div
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                    class="p-4 bg-red-100 border-2 border-red-500 text-red-800 rounded-lg"
                >
                    <h4 class="font-semibold mb-2">
                        Please fix the following errors:
                    </h4>
                    <ul class="list-disc list-inside">
                        <li v-for="(error, field) in errors" :key="field">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Results Display -->
            <div v-if="results" class="mb-6">
                <div
                    class="p-4 bg-primary border-2 border-pressed text-black rounded-lg mb-4"
                >
                    <h3 class="font-semibold mb-2">Batch Processing Results</h3>
                    <p>
                        Successfully created:
                        {{ results.successful?.length || 0 }}
                        residents
                    </p>
                    <p>
                        Failed:
                        {{ results.failed?.length || 0 }} residents
                    </p>
                </div>

                <!-- Successful Creations -->
                <div
                    v-if="results.successful && results.successful.length > 0"
                    class="mb-4"
                >
                    <h4 class="font-semibold mb-2 text-black">
                        Successfully Created Residents:
                    </h4>
                    <div
                        class="bg-white border-2 border-primary p-4 rounded-lg"
                    >
                        <div
                            v-for="resident in results.successful"
                            :key="resident.resident_id"
                            class="mb-2 text-black"
                        >
                            <strong>{{ resident.name }}</strong> - ID:
                            {{ resident.resident_id }}, PIN:
                            {{ resident.pin }}
                        </div>
                    </div>
                </div>

                <!-- Errors -->
                <div
                    v-if="results.errors && results.errors.length > 0"
                    class="mb-4"
                >
                    <h4 class="font-semibold mb-2 text-red-800">Errors:</h4>
                    <div
                        class="bg-red-100 border-2 border-red-500 p-4 rounded-lg"
                    >
                        <div
                            v-for="error in results.errors"
                            :key="error"
                            class="mb-1 text-red-800"
                        >
                            {{ error }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="space-y-6">
                <!-- File Upload -->
                <div>
                    <label
                        for="csv_file"
                        class="block text-sm font-medium text-black mb-2"
                    >
                        Select CSV File
                    </label>
                    <input
                        ref="fileInput"
                        type="file"
                        id="csv_file"
                        accept=".csv"
                        @change="handleFileSelect"
                        class="block w-full text-sm text-black file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-2 file:border-primary file:text-sm file:font-semibold file:bg-primary file:text-black hover:file:bg-pressed transition-colors"
                    />
                    <div
                        v-if="form.errors.csv_file"
                        class="mt-2 text-red-800 text-sm"
                    >
                        {{ form.errors.csv_file }}
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button
                        @click="uploadFile"
                        :disabled="!form.csv_file || isUploading"
                        class="flex-1 bg-primary hover:bg-pressed text-black px-6 py-3 rounded-lg font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="isUploading">Uploading...</span>
                        <span v-else>Upload CSV</span>
                    </button>

                    <button
                        @click="downloadTemplate"
                        class="flex-1 bg-pressed hover:bg-hover text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        Download Template
                    </button>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-8 p-4 bg-white border-2 border-primary rounded-lg">
                <h3 class="text-lg font-semibold mb-3 text-black">
                    Instructions
                </h3>
                <ol
                    class="list-decimal list-inside space-y-2 text-sm text-black"
                >
                    <li>Download the CSV template using the button above</li>
                    <li>
                        Fill in the resident information (first_name, last_name,
                        date_of_birth are required)
                    </li>
                    <li>Room number and floor number are optional</li>
                    <li>Save the file as a CSV</li>
                    <li>Upload the file using the form above</li>
                    <li>
                        Review the results and save the PIN codes for each
                        resident
                    </li>
                </ol>
            </div>
        </div>
    </Modal>
</template>
