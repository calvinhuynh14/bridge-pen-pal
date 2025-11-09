<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <nav class="bg-primary">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center h-20">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <Link href="/">
                        <img
                            src="/images/logos/logo_name_horizontal_white.svg"
                            alt="Bridge Pen Pal"
                            class="block h-20 w-auto"
                        />
                    </Link>
                </div>
            </div>
        </div>
    </nav>
</template>
