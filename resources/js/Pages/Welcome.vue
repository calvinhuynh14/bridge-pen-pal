<script setup>
import { Head, Link } from "@inertiajs/vue3";
import CustomButton from "@/Components/CustomButton.vue";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Bridge Pen Pal" />

    <!-- Main Container -->
    <div
        class="flex flex-col min-h-screen bg-background items-center justify-center"
    >
        <!-- Hero Section -->
        <section
            class="bg-primary rounded-lg max-w-7xl flex flex-col lg:flex-row items-center justify-between p-8 gap-8 shadow-lg"
        >
            <div class="flex-1">
                <h1 class="text-white text-4xl md:text-6xl lg:text-7xl">
                    Welcome to Bridge!
                </h1>
                <h2 class="text-hover text-lg md:text-2xl lg:text-4xl">
                    Where stories are shared, and friendships begin
                </h2>
            </div>
            <div class="flex-shrink-0 w-1/5">
                <img
                    src="/images/logos/logo-white.svg"
                    alt="Bridge Logo"
                    class="w-full aspect-square object-contain"
                />
            </div>
        </section>

        <section class="flex flex-col justify-center mt-10">
            <div class="max-w-7xl">
                <h2 class="text-hover text-center text-4xl font-bold">
                    Get Started
                </h2>
            </div>
            <!-- Three Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <CustomButton text="Resident" preset="neutral" size="large" />
                <CustomButton text="Volunteer" preset="neutral" size="large" />
                <CustomButton text="Admin" preset="neutral" size="large" />
            </div>
        </section>
    </div>
</template>
