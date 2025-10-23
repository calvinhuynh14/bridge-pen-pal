<script setup>
import { ref, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";

defineProps({
    title: String,
});

const page = usePage();
const showingNavigationDropdown = ref(false);

// Check if user is admin
const isAdmin = computed(() => {
    return page.props.auth?.user?.user_type === "admin";
});

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen">
            <nav class="bg-primary">
                <!-- Primary Navigation Menu -->
                <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-20">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <img
                                        src="/images/logos/logo_name_horizontal_white.svg"
                                        alt="Bridge Pen Pal"
                                        class="block h-20 w-auto"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links - Moved to sidebar -->
                            <div class="hidden">
                                <!-- Admin Navigation -->
                                <template v-if="isAdmin">
                                    <NavLink
                                        :href="route('admin.dashboard')"
                                        :active="
                                            route().current('admin.dashboard')
                                        "
                                    >
                                        Overview
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.residents')"
                                        :active="
                                            route().current('admin.residents')
                                        "
                                    >
                                        Residents
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.volunteers')"
                                        :active="
                                            route().current('admin.volunteers')
                                        "
                                    >
                                        Volunteers
                                    </NavLink>
                                    <NavLink
                                        :href="route('admin.reports')"
                                        :active="
                                            route().current('admin.reports')
                                        "
                                    >
                                        Reports
                                    </NavLink>
                                </template>

                                <!-- Volunteer/Resident Navigation -->
                                <template v-else>
                                    <NavLink
                                        :href="route('platform.home')"
                                        :active="
                                            route().current('platform.home')
                                        "
                                    >
                                        Home
                                    </NavLink>
                                    <NavLink
                                        :href="route('profile.settings')"
                                        :active="
                                            route().current('profile.settings')
                                        "
                                    >
                                        Profile
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            v-if="
                                                $page.props.jetstream
                                                    .managesProfilePhotos
                                            "
                                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition"
                                        >
                                            <img
                                                class="size-8 rounded-full object-cover"
                                                :src="
                                                    $page.props.auth.user
                                                        .profile_photo_url
                                                "
                                                :alt="
                                                    $page.props.auth.user.name
                                                "
                                            />
                                        </button>

                                        <span
                                            v-else
                                            class="inline-flex rounded-md"
                                        >
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white !bg-primary hover:!bg-hover focus:outline-none focus:!bg-hover active:!bg-hover transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ms-2 -me-0.5 size-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.5"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div
                                            class="block px-4 py-2 text-xs text-white/70"
                                        >
                                            Manage Account
                                        </div>

                                        <DropdownLink
                                            :href="route('profile.show')"
                                        >
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink
                                            v-if="
                                                $page.props.jetstream
                                                    .hasApiFeatures
                                            "
                                            :href="route('api-tokens.index')"
                                        >
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-white/20" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-white hover:bg-hover focus:outline-none focus:bg-hover focus:text-white transition duration-150 ease-in-out"
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                            >
                                <svg
                                    class="size-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1 text-white">
                        <!-- Admin Navigation -->
                        <template v-if="isAdmin">
                            <ResponsiveNavLink
                                :href="route('admin.dashboard')"
                                :active="route().current('admin.dashboard')"
                            >
                                Dashboard
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('admin.residents')"
                                :active="route().current('admin.residents')"
                            >
                                Residents
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('admin.volunteers')"
                                :active="route().current('admin.volunteers')"
                            >
                                Volunteers
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('admin.reports')"
                                :active="route().current('admin.reports')"
                            >
                                Reports
                            </ResponsiveNavLink>
                        </template>

                        <!-- Volunteer/Resident Navigation -->
                        <template v-else>
                            <ResponsiveNavLink
                                :href="route('platform.home')"
                                :active="route().current('platform.home')"
                            >
                                Home
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('profile.settings')"
                                :active="route().current('profile.settings')"
                            >
                                Profile
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-white/20 text-white">
                        <div class="flex items-center px-4">
                            <div
                                v-if="
                                    $page.props.jetstream.managesProfilePhotos
                                "
                                class="shrink-0 me-3"
                            >
                                <img
                                    class="size-10 rounded-full object-cover"
                                    :src="
                                        $page.props.auth.user.profile_photo_url
                                    "
                                    :alt="$page.props.auth.user.name"
                                />
                            </div>

                            <div>
                                <div class="font-medium text-base text-white">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-white/80">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink
                                :href="route('profile.show')"
                                :active="route().current('profile.show')"
                            >
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                v-if="$page.props.jetstream.hasApiFeatures"
                                :href="route('api-tokens.index')"
                                :active="route().current('api-tokens.index')"
                            >
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                v-if="$slots.header"
                class="bg-white dark:bg-gray-800 shadow"
            >
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex">
                <!-- Sidebar Navigation -->
                <div
                    class="hidden bg-primary lg:block w-64 text-white min-h-screen"
                >
                    <div class="p-4">
                        <nav class="space-y-2">
                            <Link
                                :href="route('admin.dashboard')"
                                class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors"
                                :class="{
                                    'bg-pressed':
                                        $page.url.startsWith(
                                            '/admin/dashboard'
                                        ),
                                }"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="size-6 mr-3"
                                >
                                    <path
                                        d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"
                                    />
                                    <path
                                        d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"
                                    />
                                </svg>

                                Overview
                            </Link>

                            <Link
                                :href="route('admin.residents')"
                                class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors"
                                :class="{
                                    'bg-pressed':
                                        $page.url.startsWith(
                                            '/admin/residents'
                                        ),
                                }"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="size-6 mr-3"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z"
                                        clip-rule="evenodd"
                                    />
                                    <path
                                        d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z"
                                    />
                                </svg>

                                Residents
                            </Link>

                            <Link
                                :href="route('admin.volunteers')"
                                class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors"
                                :class="{
                                    'bg-pressed':
                                        $page.url.startsWith(
                                            '/admin/volunteers'
                                        ),
                                }"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="size-6 mr-3"
                                >
                                    <path
                                        d="M5.25 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM2.25 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM18.75 7.5a.75.75 0 0 0-1.5 0v2.25H15a.75.75 0 0 0 0 1.5h2.25v2.25a.75.75 0 0 0 1.5 0v-2.25H21a.75.75 0 0 0 0-1.5h-2.25V7.5Z"
                                    />
                                </svg>

                                Volunteers
                            </Link>

                            <Link
                                :href="route('admin.reports')"
                                class="flex items-center px-4 py-3 text-base font-medium rounded-md hover:bg-hover transition-colors"
                                :class="{
                                    'bg-pressed':
                                        $page.url.startsWith('/admin/reports'),
                                }"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="size-6 mr-3"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M3 2.25a.75.75 0 0 1 .75.75v.54l1.838-.46a9.75 9.75 0 0 1 6.725.738l.108.054A8.25 8.25 0 0 0 18 4.524l3.11-.732a.75.75 0 0 1 .917.81 47.784 47.784 0 0 0 .005 10.337.75.75 0 0 1-.574.812l-3.114.733a9.75 9.75 0 0 1-6.594-.77l-.108-.054a8.25 8.25 0 0 0-5.69-.625l-2.202.55V21a.75.75 0 0 1-1.5 0V3A.75.75 0 0 1 3 2.25Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>

                                Reports
                            </Link>
                        </nav>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex-1 bg-background min-h-screen pb-16 lg:pb-0">
                    <slot />
                </div>
            </main>

            <!-- Mobile Bottom Navigation -->
            <div
                class="lg:hidden fixed bottom-0 left-0 right-0 bg-primary text-white border-t border-gray-600"
            >
                <div class="flex justify-around items-center py-2">
                    <!-- Overview -->
                    <Link
                        :href="route('admin.dashboard')"
                        class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors"
                        :class="{
                            'bg-pressed':
                                $page.url.startsWith('/admin/dashboard'),
                        }"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="size-6 mb-1"
                        >
                            <path
                                d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"
                            />
                            <path
                                d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"
                            />
                        </svg>
                        <span class="text-xs font-medium">Overview</span>
                    </Link>

                    <!-- Residents -->
                    <Link
                        :href="route('admin.residents')"
                        class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors"
                        :class="{
                            'bg-pressed':
                                $page.url.startsWith('/admin/residents'),
                        }"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="size-6 mb-1"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z"
                                clip-rule="evenodd"
                            />
                            <path
                                d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z"
                            />
                        </svg>
                        <span class="text-xs font-medium">Residents</span>
                    </Link>

                    <!-- Volunteers -->
                    <Link
                        :href="route('admin.volunteers')"
                        class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors"
                        :class="{
                            'bg-pressed':
                                $page.url.startsWith('/admin/volunteers'),
                        }"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="size-6 mb-1"
                        >
                            <path
                                d="M5.25 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM2.25 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM18.75 7.5a.75.75 0 0 0-1.5 0v2.25H15a.75.75 0 0 0 0 1.5h2.25v2.25a.75.75 0 0 0 1.5 0v-2.25H21a.75.75 0 0 0 0-1.5h-2.25V7.5Z"
                            />
                        </svg>
                        <span class="text-xs font-medium">Volunteers</span>
                    </Link>

                    <!-- Reports -->
                    <Link
                        :href="route('admin.reports')"
                        class="flex flex-col items-center py-2 px-3 rounded-lg transition-colors"
                        :class="{
                            'bg-pressed':
                                $page.url.startsWith('/admin/reports'),
                        }"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="size-6 mb-1"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M3 2.25a.75.75 0 0 1 .75.75v.54l1.838-.46a9.75 9.75 0 0 1 6.725.738l.108.054A8.25 8.25 0 0 0 18 4.524l3.11-.732a.75.75 0 0 1 .917.81 47.784 47.784 0 0 0 .005 10.337.75.75 0 0 1-.574.812l-3.114.733a9.75 9.75 0 0 1-6.594-.77l-.108-.054a8.25 8.25 0 0 0-5.69-.625l-2.202.55V21a.75.75 0 0 1-1.5 0V3A.75.75 0 0 1 3 2.25Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="text-xs font-medium">Reports</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
