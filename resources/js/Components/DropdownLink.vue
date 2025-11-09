<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    href: String,
    as: String,
});
</script>

<template>
    <div class="mx-2">
        <button
            v-if="as == 'button'"
            type="submit"
            class="block w-full px-4 py-2 text-start text-sm leading-5 text-white hover:bg-hover focus:outline-none focus:bg-hover transition duration-150 ease-in-out rounded-md"
        >
            <slot />
        </button>

        <a
            v-else-if="as == 'a'"
            :href="href"
            class="block w-full px-4 py-2 text-sm leading-5 text-white hover:bg-hover focus:outline-none focus:bg-hover transition duration-150 ease-in-out rounded-md"
        >
            <slot />
        </a>

        <Link
            v-else
            :href="href"
            class="block w-full px-4 py-2 text-sm leading-5 text-white hover:bg-hover focus:outline-none focus:bg-hover transition duration-150 ease-in-out rounded-md"
        >
            <slot />
        </Link>
    </div>
</template>
