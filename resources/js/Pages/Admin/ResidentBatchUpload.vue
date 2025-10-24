<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    organizationId: Number,
    success: String,
    results: Object,
    errors: Object,
});

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
        },
        onError: () => {
            isUploading.value = false;
        },
    });
};
</script>

<template>
    <Head title="Resident Batch Upload" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h1
                                        class="text-2xl font-bold text-gray-900 mb-2"
                                    >
                                        Resident Batch Upload
                                    </h1>
                                    <p class="text-gray-600">
                                        Upload a CSV file to create multiple
                                        resident accounts at once.
                                    </p>
                                </div>
                                <Link
                                    :href="route('admin.residents')"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    ← Back to Residents
                                </Link>
                            </div>
                        </div>

                        <!-- Success Message -->
                        <div
                            v-if="success"
                            class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded"
                        >
                            {{ success }}
                        </div>

                        <!-- Error Messages -->
                        <div
                            v-if="errors && Object.keys(errors).length > 0"
                            class="mb-6"
                        >
                            <div
                                v-for="(error, key) in errors"
                                :key="key"
                                class="p-4 bg-red-100 border border-red-400 text-red-700 rounded mb-2"
                            >
                                {{ error }}
                            </div>
                        </div>

                        <!-- Results Display -->
                        <div v-if="results" class="mb-6">
                            <div
                                class="p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded mb-4"
                            >
                                <h3 class="font-bold">
                                    Batch Processing Results
                                </h3>
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
                                v-if="
                                    results.successful &&
                                    results.successful.length > 0
                                "
                                class="mb-4"
                            >
                                <h4 class="font-bold text-green-700 mb-2">
                                    Successfully Created:
                                </h4>
                                <div class="bg-green-50 p-4 rounded">
                                    <div
                                        v-for="resident in results.successful"
                                        :key="resident.resident_id"
                                        class="mb-2"
                                    >
                                        <strong>{{ resident.name }}</strong>
                                        (ID: {{ resident.resident_id }}, PIN:
                                        {{ resident.pin }})
                                    </div>
                                </div>
                            </div>

                            <!-- Errors -->
                            <div
                                v-if="
                                    results.errors && results.errors.length > 0
                                "
                                class="mb-4"
                            >
                                <h4 class="font-bold text-red-700 mb-2">
                                    Errors:
                                </h4>
                                <div class="bg-red-50 p-4 rounded">
                                    <div
                                        v-for="error in results.errors"
                                        :key="error"
                                        class="mb-1 text-red-700"
                                    >
                                        {{ error }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h2 class="text-lg font-semibold mb-4">
                                Upload CSV File
                            </h2>

                            <div class="mb-4">
                                <label
                                    for="csv_file"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Select CSV File
                                </label>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    id="csv_file"
                                    accept=".csv"
                                    @change="handleFileSelect"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                />
                                <div
                                    v-if="form.errors.csv_file"
                                    class="mt-2 text-red-600 text-sm"
                                >
                                    {{ form.errors.csv_file }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <h3
                                    class="text-sm font-medium text-gray-700 mb-2"
                                >
                                    CSV Format Requirements:
                                </h3>
                                <ul
                                    class="text-sm text-gray-600 list-disc list-inside"
                                >
                                    <li>File must be in CSV format</li>
                                    <li>Maximum file size: 5MB</li>
                                    <li>Maximum 50 residents per batch</li>
                                    <li>
                                        Required columns: first_name, last_name,
                                        date_of_birth
                                    </li>
                                    <li>
                                        Optional columns: room_number,
                                        floor_number
                                    </li>
                                </ul>
                            </div>

                            <div class="flex gap-4">
                                <button
                                    @click="uploadFile"
                                    :disabled="isUploading || form.processing"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    {{
                                        isUploading || form.processing
                                            ? "Uploading..."
                                            : "Upload File"
                                    }}
                                </button>

                                <button
                                    @click="downloadTemplate"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Download Template
                                </button>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="mt-6 bg-yellow-50 p-4 rounded-lg">
                            <h3 class="font-bold text-yellow-800 mb-2">
                                Instructions:
                            </h3>
                            <ol
                                class="text-sm text-yellow-700 list-decimal list-inside space-y-1"
                            >
                                <li>
                                    Download the CSV template using the button
                                    above
                                </li>
                                <li>
                                    Fill in the resident information (name, date
                                    of birth, room/floor numbers)
                                </li>
                                <li>Save the file as a CSV</li>
                                <li>Upload the file using the form above</li>
                                <li>
                                    Review the results and save the PIN codes
                                    for each resident
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
