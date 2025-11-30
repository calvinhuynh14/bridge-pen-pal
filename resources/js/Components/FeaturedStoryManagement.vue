<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import CustomButton from "@/Components/CustomButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Select from "@/Components/Select.vue";
import SuccessMessage from "@/Components/SuccessMessage.vue";
import ErrorMessage from "@/Components/ErrorMessage.vue";
import Avatar from "@/Components/Avatar.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    featuredStory: {
        type: Object,
        default: null,
    },
});

const residents = ref([]);
const isLoadingResidents = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const form = useForm({
    resident_id: props.featuredStory?.resident_id || "",
    bio: props.featuredStory?.bio || "",
});

// Update form when featuredStory prop changes
watch(
    () => props.featuredStory,
    (newStory) => {
        if (newStory) {
            form.resident_id = newStory.resident_id || "";
            form.bio = newStory.bio || "";
        } else {
            form.reset();
        }
    },
    { immediate: true }
);

const bioRemaining = computed(() => {
    return 2000 - (form.bio?.length || 0);
});

const bioCountClass = computed(() => {
    if (bioRemaining.value <= 0) return "text-red-600 font-semibold";
    if (bioRemaining.value <= 100) return "text-yellow-600 font-medium";
    return "text-gray-600";
});

const loadResidents = async () => {
    isLoadingResidents.value = true;
    try {
        const response = await axios.get(
            route("admin.featured-story.residents")
        );
        residents.value = response.data.residents.map((r) => ({
            value: r.id,
            label: r.name,
        }));
    } catch (error) {
        console.error("Error loading residents:", error);
    } finally {
        isLoadingResidents.value = false;
    }
};

const openEditModal = () => {
    // Load residents if not already loaded
    if (residents.value.length === 0) {
        loadResidents();
    }
    // Update form with current featured story data
    if (props.featuredStory) {
        form.resident_id = props.featuredStory.resident_id || "";
        form.bio = props.featuredStory.bio || "";
    }
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    // Reset form to current featured story values
    if (props.featuredStory) {
        form.resident_id = props.featuredStory.resident_id || "";
        form.bio = props.featuredStory.bio || "";
    } else {
        form.reset();
    }
    form.clearErrors();
};

const submit = () => {
    form.post(route("admin.featured-story.store"), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            router.reload({ only: ["featuredStory"] });
        },
    });
};

const openDeleteModal = () => {
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
};

const deleteForm = useForm({});

const deleteStory = () => {
    deleteForm.delete(route("admin.featured-story.destroy"), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            form.reset();
            router.reload({ only: ["featuredStory"] });
        },
    });
};

onMounted(() => {
    loadResidents();
});
</script>

<template>
    <section
        aria-label="Featured Story Management"
        class="bg-primary text-white overflow-hidden shadow-xl rounded-lg p-6 mb-6 border-2 border-primary"
    >
        <h3 class="text-lg font-semibold text-white mb-4">Featured Story</h3>

        <p class="text-sm text-white/90 mb-6">
            Select a resident and write a bio to feature them on the platform
            home and discover pages. This helps volunteers learn about residents
            and encourages connections.
        </p>

        <!-- Success Message -->
        <SuccessMessage
            v-if="$page.props.flash?.success"
            :message="$page.props.flash.success"
            class="mb-4"
        />

        <!-- Error Message -->
        <ErrorMessage
            v-if="$page.props.flash?.error"
            :message="$page.props.flash.error"
            class="mb-4"
        />

        <!-- Featured Story Display -->
        <div v-if="props.featuredStory && !showDeleteModal">
            <div
                class="flex flex-col md:flex-row items-start gap-6 p-6 bg-white/10 rounded-lg border border-white/20"
            >
                <div class="flex-shrink-0">
                    <Avatar
                        :src="
                            props.featuredStory.resident_avatar
                                ? `/images/avatars/${props.featuredStory.resident_avatar}`
                                : null
                        "
                        :name="props.featuredStory.resident_name"
                        size="lg"
                        bg-color="primary"
                        text-color="white"
                    />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xl font-semibold text-white mb-2">
                        {{ props.featuredStory.resident_name }}
                    </h4>
                    <p class="text-sm text-white whitespace-pre-line mb-4">
                        {{ props.featuredStory.bio }}
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <CustomButton
                            text="Edit"
                            preset="neutral"
                            size="small"
                            @click="openEditModal"
                            ariaLabel="Edit featured story"
                        />
                        <CustomButton
                            text="Remove"
                            preset="warning"
                            size="small"
                            @click="openDeleteModal"
                            ariaLabel="Remove featured story"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State - No Featured Story -->
        <div v-else-if="!showDeleteModal" class="text-center py-8">
            <p class="text-white/90 mb-4">
                No featured story is currently set. Create one to showcase a
                resident on the platform pages.
            </p>
            <CustomButton
                text="Create Featured Story"
                preset="neutral"
                @click="openEditModal"
                ariaLabel="Create featured story"
            />
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="showDeleteModal"
            @close="closeDeleteModal"
            max-width="md"
            title="Remove Featured Story"
            description="Are you sure you want to remove this featured story?"
            header-bg="primary"
        >
            <div class="bg-white px-6 py-4">
                <!-- Warning Content -->
                <div>
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <svg
                                class="w-8 h-8 text-red-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-lg font-medium text-black">
                                Are you sure?
                            </h4>
                        </div>
                    </div>

                    <div class="text-sm text-gray-600">
                        <p class="mb-2">
                            You are about to remove the featured story for
                            <strong class="text-black">{{
                                props.featuredStory?.resident_name
                            }}</strong
                            >.
                        </p>
                        <p class="mb-2">This action will:</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>
                                Hide the featured story from the platform home
                                and discover pages
                            </li>
                            <li>Not delete the resident or their data</li>
                            <li>
                                Allow you to feature a different resident later
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3"
            >
                <CustomButton
                    :text="
                        deleteForm.processing
                            ? 'Removing...'
                            : 'Remove Featured Story'
                    "
                    preset="error"
                    size="small"
                    class="w-full sm:w-auto"
                    @click="deleteStory"
                    :disabled="deleteForm.processing"
                    ariaLabel="Confirm remove featured story"
                />
                <CustomButton
                    text="Cancel"
                    preset="neutral"
                    size="small"
                    type="button"
                    class="mt-3 w-full sm:mt-0 sm:w-auto"
                    @click="closeDeleteModal"
                    :disabled="deleteForm.processing"
                    ariaLabel="Cancel removal"
                />
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal
            :show="showEditModal"
            @close="closeEditModal"
            max-width="2xl"
            title="Edit Featured Story"
            description="Select a resident and write a bio to feature them on the platform home and discover pages."
            header-bg="primary"
        >
            <div class="bg-white px-6 py-4">
                <form
                    id="featured-story-form"
                    @submit.prevent="submit"
                    aria-label="Featured story form"
                >
                    <div class="space-y-6">
                        <!-- Resident Selection -->
                        <div class="pt-2">
                            <InputLabel
                                for="resident_id"
                                value="Select Resident"
                                required
                                class="text-gray-900"
                            />
                            <Select
                                id="resident_id"
                                v-model="form.resident_id"
                                :options="residents"
                                placeholder="Choose a resident to feature"
                                :required="true"
                                :errorId="
                                    form.errors.resident_id
                                        ? 'resident_id-error'
                                        : null
                                "
                                class="mt-2"
                                :disabled="isLoadingResidents"
                            />
                            <InputError
                                id="resident_id-error"
                                class="mt-2"
                                :message="form.errors.resident_id"
                            />
                            <p
                                v-if="isLoadingResidents"
                                class="mt-2 text-sm text-gray-500"
                            >
                                Loading residents...
                            </p>
                        </div>

                        <!-- Bio Textarea -->
                        <div>
                            <InputLabel
                                for="bio"
                                value="Bio / Story"
                                required
                                class="text-gray-900"
                            />
                            <textarea
                                id="bio"
                                v-model="form.bio"
                                rows="8"
                                :maxlength="2000"
                                :aria-describedby="
                                    form.errors.bio
                                        ? 'bio-error'
                                        : 'bio-hint bio-count'
                                "
                                :aria-invalid="!!form.errors.bio"
                                :aria-required="true"
                                class="mt-2 block w-full border-gray-300 rounded-lg focus:border-primary focus:ring-primary resize-y min-h-[200px]"
                                :class="{
                                    'border-red-500': form.errors.bio,
                                }"
                                placeholder="Write a compelling bio about the resident. This will be displayed on the platform home and discover pages..."
                            ></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <p
                                    id="bio-hint"
                                    class="text-sm text-gray-600"
                                    aria-live="polite"
                                >
                                    Minimum 20 characters required. Write about
                                    the resident's interests, background, or
                                    what makes them special.
                                </p>
                                <span
                                    id="bio-count"
                                    :class="[
                                        'text-sm font-medium',
                                        bioCountClass,
                                    ]"
                                    aria-live="polite"
                                    role="status"
                                >
                                    {{ form.bio?.length || 0 }} / 2000
                                </span>
                            </div>
                            <InputError
                                id="bio-error"
                                class="mt-2"
                                :message="form.errors.bio"
                            />
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div
                class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3"
            >
                <CustomButton
                    :text="
                        props.featuredStory
                            ? 'Update Featured Story'
                            : 'Create Featured Story'
                    "
                    type="submit"
                    preset="primary"
                    size="small"
                    form="featured-story-form"
                    :isLoading="form.processing"
                    class="w-full sm:w-auto"
                    ariaLabel="Save featured story"
                />
                <CustomButton
                    text="Cancel"
                    type="button"
                    preset="neutral"
                    size="small"
                    class="mt-3 w-full sm:mt-0 sm:w-auto"
                    @click="closeEditModal"
                    :disabled="form.processing"
                    ariaLabel="Cancel editing"
                />
            </div>
        </Modal>
    </section>
</template>
