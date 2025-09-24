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
        class="flex flex-col md:flex-row lg:flex-col min-h-screen bg-background items-center justify-center p-4 gap-4"
    >
        <!-- Hero Section -->
        <section
            class="flex flex-col max-w-7xl bg-background lg:bg-primary rounded-lg lg:flex-row lg:m-8 lg:p-8 lg:gap-8 lg:shadow-lg items-center justify-center lg:justify-between"
        >
            <div class="flex-1">
                <h1
                    class="hidden lg:block text-white text-4xl lg:text-6xl xl:text-7xl"
                >
                    Welcome to Bridge!
                </h1>
                <h2
                    class="hidden lg:block text-hover text-lg lg:text-2xl xl:text-4xl"
                >
                    Where stories are shared, and friendships begin
                </h2>
            </div>
            <div class="w-3/4 md:w-3/5 lg:w-1/5">
                <img
                    src="/images/logos/logo-with-name-purple.svg"
                    alt="Bridge Logo"
                    class="w-full h-full object-contain lg:hidden"
                />
                <img
                    src="/images/logos/logo-white.svg"
                    alt="Bridge Logo"
                    class="w-full aspect-square object-contain hidden lg:block"
                />
            </div>
        </section>

        <section
            class="flex flex-col mx-8 md:w-full justify-center lg:items-center"
        >
            <div class="max-w-7xl">
                <h2 class="text-hover text-center text-4xl font-bold">
                    Get Started
                </h2>
            </div>
            <!-- Three Buttons -->
            <div class="flex flex-col lg:flex-row gap-4">
                <CustomButton text="Resident" preset="neutral" size="medium" />
                <CustomButton text="Volunteer" preset="neutral" size="medium" />
                <CustomButton text="Admin" preset="neutral" size="medium" />
            </div>
        </section>
    </div>
</template>
