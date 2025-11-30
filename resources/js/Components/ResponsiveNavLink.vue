<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
    role: String,
});

const classes = computed(() => {
    return props.active
        ? "block w-full ps-3 pe-4 py-2 border-l-4 border-white text-start text-base font-medium text-white bg-white/10 focus:outline-none focus:text-white focus:bg-white/20 focus:border-white transition duration-150 ease-in-out"
        : "block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-white hover:text-white hover:bg-white/10 hover:border-white/50 focus:outline-none focus:text-white focus:bg-white/10 focus:border-white/50 transition duration-150 ease-in-out";
});
</script>

<template>
    <div>
        <button
            v-if="as == 'button'"
            :class="classes"
            class="w-full text-start"
            :role="role"
        >
            <slot />
        </button>

        <a
            v-else-if="as == 'a'"
            :class="classes"
            class="w-full text-start"
            :href="href"
            :role="role"
        >
            <slot />
        </a>

        <Link 
            v-else 
            :href="href" 
            :class="classes"
            v-bind="role ? { role } : {}"
        >
            <slot />
        </Link>
    </div>
</template>
