<script setup>
import { Head } from '@inertiajs/vue3';
import CustomButton from '@/Components/CustomButton.vue';
import SimpleHeader from '@/Components/SimpleHeader.vue';

const presets = ['primary', 'secondary', 'accent', 'neutral', 'success', 'warning', 'dark'];
const sizes = ['small', 'medium', 'large'];
const backgrounds = [
    { name: 'Background', class: 'bg-background', textClass: 'text-black' },
    { name: 'Primary', class: 'bg-primary', textClass: 'text-white' },
    { name: 'Hover', class: 'bg-hover', textClass: 'text-white' },
    { name: 'Pressed', class: 'bg-pressed', textClass: 'text-white' },
    { name: 'Light', class: 'bg-light', textClass: 'text-black' },
];

const getButtonText = (preset, size) => {
    return `${preset.charAt(0).toUpperCase() + preset.slice(1)} ${size.charAt(0).toUpperCase() + size.slice(1)}`;
};
</script>

<template>
    <Head title="Button Test Page" />

    <SimpleHeader />

    <main class="min-h-screen bg-background pb-8" role="main">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-4xl font-bold text-primary mb-8 text-center">
                CustomButton Component Test Page
            </h1>

            <p class="text-lg text-black mb-8 text-center">
                Testing all button presets and sizes on different background colors
            </p>

            <!-- Test each background -->
            <div
                v-for="bg in backgrounds"
                :key="bg.name"
                :class="[bg.class, 'rounded-lg p-8 mb-8 shadow-lg']"
            >
                <h2 :class="[bg.textClass, 'text-3xl font-bold mb-6 text-center']">
                    {{ bg.name }} Background
                </h2>

                <!-- Test each preset -->
                <div
                    v-for="preset in presets"
                    :key="preset"
                    class="mb-8"
                >
                    <h3 :class="[bg.textClass, 'text-xl font-semibold mb-4']">
                        {{ preset.charAt(0).toUpperCase() + preset.slice(1) }} Preset
                    </h3>

                    <div class="flex flex-wrap gap-4 items-center">
                        <!-- Test each size -->
                        <div
                            v-for="size in sizes"
                            :key="size"
                            class="mb-4"
                        >
                            <div :class="[bg.textClass, 'text-sm mb-2']">
                                {{ size.charAt(0).toUpperCase() + size.slice(1) }}:
                            </div>
                            <CustomButton
                                :text="getButtonText(preset, size)"
                                :preset="preset"
                                :size="size"
                                :ariaLabel="`${preset} ${size} button on ${bg.name} background`"
                            />
                        </div>

                        <!-- Loading state -->
                        <div class="mb-4">
                            <div :class="[bg.textClass, 'text-sm mb-2']">
                                Loading:
                            </div>
                            <CustomButton
                                :text="`${preset.charAt(0).toUpperCase() + preset.slice(1)} Loading`"
                                :preset="preset"
                                size="medium"
                                :isLoading="true"
                                :ariaLabel="`${preset} button loading on ${bg.name} background`"
                            />
                        </div>

                        <!-- Disabled state -->
                        <div class="mb-4">
                            <div :class="[bg.textClass, 'text-sm mb-2']">
                                Disabled:
                            </div>
                            <CustomButton
                                :text="`${preset.charAt(0).toUpperCase() + preset.slice(1)} Disabled`"
                                :preset="preset"
                                size="medium"
                                disabled
                                :ariaLabel="`${preset} button disabled on ${bg.name} background`"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Color Reference -->
            <div class="bg-white rounded-lg p-8 shadow-lg mt-8">
                <h2 class="text-3xl font-bold text-black mb-6 text-center">
                    Color Reference
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="bg in backgrounds"
                        :key="bg.name"
                        class="flex items-center gap-4 p-4 border-2 border-gray-300 rounded-lg"
                    >
                        <div :class="[bg.class, 'w-16 h-16 rounded-lg border-2 border-gray-400']"></div>
                        <div>
                            <div class="font-semibold text-black">{{ bg.name }}</div>
                            <div class="text-sm text-gray-600">{{ bg.class }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

